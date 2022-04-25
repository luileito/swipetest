#!/usr/bin/env python3
# coding: utf-8

'''
Export computed stats (NDJSON file) and user demographics (TSV file) to a consolidated database format.
Note: The stats file is split into sentence-level and word-level databases.

---
Author: Luis A. Leiva <luis.leiva@aalto.fi>
Date created: 2/6/2020
Date last modified: 2/6/2020
Python Version: 3.5
'''

import sys
import os
import json
import csv
import argparse
import numpy as np

# Define CLI options.
parser = argparse.ArgumentParser(description='Export swipe datasets.')
parser.add_argument('--stats_file', help='path to computed stats file')
parser.add_argument('--users_file', help='path to user demographics file')
parser.add_argument('--format', default='tsv', choices=['ndjson', 'tsv'], help='database storage format')
parser.add_argument('--prefix', default='', help='database name prefix')

args = parser.parse_args()
print(args, '\n', file=sys.stderr)


def truncate_file(filepath):
    with open(filepath, 'w') as f:
         f.truncate(0)


def add_item(entry, table, header=None, sep='\t'):
    entry = normalize_keys(entry)
    entry = normalize_values(entry)

    if args.format == 'tsv':
        if header is None:
            header = []
            values = []
            # Ensure we write columns in the same order.
            for key, val in sorted(entry.items()):
                header.append(key)
                values.append(val)
        else:
            # Ensure entry has all header fields.
            values = [entry[k] if k in entry else np.nan for k in header]
            header = normalize_header(header)

        if not os.path.isfile(table):
            with open(table, 'w') as f:
                writer = csv.writer(f, delimiter=sep)
                writer.writerow(header)
                writer.writerow(values)
        else:
            with open(table, 'a') as f:
                writer = csv.writer(f, delimiter=sep)
                writer.writerow(values)

    elif args.format == 'ndjson':
        # In this case we don't care about sorting.
        with open(table, 'a') as f:
            f.write(json.dumps(row) + '\n')


# We had 2 sentence types (enron or random) so store that info in DB as well.
# We just need to indicate where does each word comes from.
enron_dataset = './data/mem200.txt'
with open(enron_dataset) as f:
    enron_sentences = f.read().splitlines()
    enron_sentences = list(map(lambda x: x.replace(' ', '_'), enron_sentences))


def normalize_values(dic):
    newdic = {}
    for k, v in dic.items():
        try:
            v = v.lower()
        except:
            pass
        newdic[k] = v
    return newdic


def normalize_keys(dic):
    newdic = {}
    for key in dic.keys():
        if any(c.isupper() for c in key):
            k = snakecase(key)
        else:
            k = key
        newdic[k] = dic[key]
    return newdic


def normalize_header(cols):
    header = []
    for key in cols:
        if any(c.isupper() for c in key):
            k = snakecase(key)
        else:
            k = key
        header.append(k)
    return header


def snakecase(string):
    return ''.join(['_' + c.lower() if c.isupper() else c for c in string]).lstrip('_')



if __name__ == '__main__':

    db_demographics = args.prefix + 'demographics.' + args.format
    db_words = args.prefix + 'words.' + args.format
    db_sentences = args.prefix + 'sentences.' + args.format

    if args.users_file:
        truncate_file(db_demographics)

        with open(args.users_file) as f:
            # Each user record has the same number of columns,
            # so no extra check are needed.
            reader = csv.DictReader(f, delimiter='\t')
            for row in reader:
                add_item(row, db_demographics)

    if args.stats_file:
        truncate_file(db_words)
        truncate_file(db_sentences)

        with open(args.stats_file) as f:
            # Each stat record may have different number of columns,
            # therefore we have to scan all records first
            # to know the exact number of columns to store.
            header = []
            for line in f:
                d = json.loads(line)
                cols = list(d.keys())
                header = list(set(header + cols))
            # We'll store words in a separate table,
            # therefore revise the columns we process below.
            header.remove('good_words')
            header.remove('fail_words')
            header.append('dataset')

        with open(args.stats_file) as f:
            # Now we can process all the data.
            for line in f:
                d = json.loads(line)

                # Store words in a separate table.
                if 'good_words' in d:
                    for num, dic in enumerate(d['good_words']):
                        dic.update({
                            'username': d['username'],
                            'sentence': d['sentence'],
                            'is_failed': 0
                        })
                        add_item(dic, db_words)
                    del d['good_words']

                if 'fail_words' in d:
                    for num, dic in enumerate(d['fail_words']):
                        dic.update({
                            'username': d['username'],
                            'sentence': d['sentence'],
                            'is_failed': 1
                        })
                        add_item(dic, db_words)
                    del d['fail_words']

                # Flat nested values for analysis.
                # TODO: Create additional keys: *_mean, *_median, *_sd.
                # By now just pick the median value, since it's more robust.
                for key, val in d.items():
                    if type(val) is dict and 'sample_size' in val:
                        d[key] = val['median']

                # Indicate the dataset from each sentence comes from.
                d['dataset'] = 'enron' if d['sentence'] in enron_sentences else 'rand'

                add_item(d, db_sentences, header)
