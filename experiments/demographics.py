#!/usr/bin/env python3

'''
Analyze user demographics from study json files.

---
Author: Luis A. Leiva <luis.leiva@aalto.fi>
Date created: 22/4/2020
Date last modified: 22/4/2020
Python Version: 3.5
'''

import sys
import os
import json
from collections import defaultdict


def parse_lang(langstr):
    '''Get preferred language from user agent lang, e.g. "en-US,en;q=0.9" becomes "en".'''
    res = []
    for lang in langstr.split(','):
        lang = lang.split(';')[0]
        lang = lang.split('-')[0]
        res.append(lang)
    res = list(set(res))
    return res.pop() if res else None


def uid(logfile):
    '''Compute user ID based on the given log filename.'''
    base = os.path.basename(logfile)
    base, _ = os.path.splitext(base)
    return base


if __name__ == '__main__':

    resultdic = defaultdict(lambda: defaultdict(int))
    jsonfiles = sys.argv[1:]

    print('uid', 'gender', 'age', 'familiarity',
      'dominantHand', 'swipeHand', 'swipeFinger',
      'nationality', 'language', 'englishLevel',
      'vendor', sep='\t')

    # We used the 'NA' token to denote missing data in the study,
    # so use the same convention here.
    NA = 'NA'

    for jsonfile in jsonfiles:
        username = uid(jsonfile)

        with open(jsonfile) as f:
            obj = json.load(f)
            print(username, obj['gender'] or NA, obj['age'] or NA, obj['familiarity'] or NA,
              obj['dominantHand'] or NA, obj['swipeHand'] or NA, obj['swipeFinger'] or NA,
              obj['nationality'] or NA, parse_lang(obj['language']), obj['englishLevel'] or NA,
              obj['vendor'] or NA, sep='\t')
