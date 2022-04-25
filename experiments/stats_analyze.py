#!/usr/bin/env python3
# coding: utf-8

'''
Analyze swipe dataset from ndjson file with computed stats,
taking into account user demographics.

---
Author: Luis A. Leiva <luis.leiva@aalto.fi>
Date created: 22/4/2020
Date last modified: 14/5/2020
Python Version: 3.5
'''

import sys
import os
import json
import csv
import math
import argparse
from collections import defaultdict

import numpy as np

# Define CLI options.
parser = argparse.ArgumentParser(description='Analyze ndjon-based swipe dataset.')
parser.add_argument('--metric', required=True, help='target metric to report')
parser.add_argument('--stats_file', required=True, help='path to computed stats file')
parser.add_argument('--users_file', help='path to use demographics file')
parser.add_argument('--group_name', type=str.lower, help='e.g. familiarity, dominanthand, etc.')
parser.add_argument('--group_value', type=str.lower, help='e.g. often, left, etc.')
parser.add_argument('--header', action='store_true', help='print header')
parser.add_argument('--dataset', type=lambda s:s.lower(), choices=['enron', 'rand', 'rand2k', 'rand3k', 'rand5k', 'rand0'], help='dataset')
parser.add_argument('--word_type', default='good_words', choices=['good_words', 'fail_words'], help='word type')

args = parser.parse_args()
print(args, file=sys.stderr)

# Swipe-based analysis is only available for a particular set of metrics.
is_word_analysis = args.metric in ['length', 'dtw', 'time']


def normalize(dic):
    newdic = {}
    for k, v in dic.items():
        try:
            v = int(v)
        except:
            v = v.lower()
        newdic[k.lower()] = v
    return newdic


def print_stats(values):
    # Remove `None`s.
    values = [v for v in values if v]

    N = len(values)
    if N > 0:
        M, Mdn, SD = np.mean(values), np.median(values), np.std(values)
    else:
        M, Mdn, SD = None, None, None

    # Assign default condition labels.
    group = args.group_name if args.group_name else 'overall'
    value = args.group_value if args.group_value else 'overall'

    if args.header:
        print('dataset', 'metric', 'group', 'value', 'n', 'mean', 'median', 'sd', sep='\t')

    print(args.dataset, args.metric, group, value, N, M, Mdn, SD, sep='\t')


def check_condition(user):
    # Some conditions require manual treatment:
    # - age:          youth (<=20), young (<=30), adult (<=40), senior (>40)
    # - language:     english, non-english
    # - nationality:  us, non-us
    # - screenwidth:  small, large
    # - vendor:       google, apple
    if args.group_name == 'age':
        age = user[args.group_name]
        if args.group_value == 'youth' and age > 20:
            return False
        elif args.group_value == 'young' and (age < 20 or age > 30):
            return False
        elif args.group_value == 'adult' and (age < 30 or age > 40):
            return False
        elif args.group_value == 'senior' and age < 40:
            return False
        return True

    elif args.group_name == 'language':
        lang = user[args.group_name]
        if args.group_value == 'english' and lang != 'en':
            return False
        elif args.group_value == 'non-english' and lang == 'en':
            return False
        return True

    elif args.group_name == 'nationality':
        country = user[args.group_name]
        if args.group_value == 'us' and country != 'us':
            return False
        elif args.group_value == 'non-us' and country == 'us':
            return False
        return True

    elif args.group_name == 'screenwidth':
        width = user[args.group_name]
        if args.group_value == 'small' and width > 400:
            return False
        elif args.group_value == 'large' and width < 400:
            return False
        return True

    elif args.group_name == 'vendor':
        if not user[args.group_name].startswith(args.group_value):
            return False
        return True

    # By default just check that name and value match.
    return user[args.group_name] == args.group_value



if __name__ == '__main__':

    users = {}
    if args.users_file:
        with open(args.users_file) as f:
            reader = csv.DictReader(f, delimiter='\t')
            for row in reader:
                users[row['uid']] = normalize(row)

    # NB: With a dynamic dict we can only access top-level props.
    # Therefore we created `check_condition()` to allow for some flexibility.
    res = defaultdict(list)

    with open(args.stats_file) as f:
        for line in f:
            obj = json.loads(line)
            uid = obj['username']
            user = users[uid]

            # If we specify any condition, ensure that user matches.
            if args.group_name and args.group_value:
                if not check_condition(user):
                    continue

            if not is_word_analysis:
                # --- General analysis ---
                if args.metric not in obj:
                    continue

                val = obj[args.metric]
                if type(val) is dict and 'mean' in val:
                    val = val['mean']

                if not val or math.isnan(val):
                    continue

                if args.dataset:
                    # In sentence mode, we only have two conditions: enron or random.
                    if not args.word_type in obj or not obj[args.word_type]:
                        continue

                    # Just pick the first word and check the dataset where it comes from.
                    sentence_dataset = obj[args.word_type][0]['dataset']
                    if not sentence_dataset or not sentence_dataset.startswith(args.dataset):
                        continue

                res[args.metric].append(val)

                # All done.
                continue

            if is_word_analysis:
                # --- Word-based analysis ---
                if args.word_type not in obj:
                    continue

                for word in obj[args.word_type]:
                    if args.dataset and word['dataset'] != args.dataset:
                        continue

                    if args.metric not in word:
                        continue

                    val = word[args.metric]
                    if not val or math.isnan(val):
                        continue

                    res[args.metric].append(val)

                # All done.
                continue

    print_stats(res[args.metric])
