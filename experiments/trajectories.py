#!/usr/bin/env python3
# coding: utf-8

'''
Export swiped word trajectories (NDJSON file) to a consolidated database format.

---
Author: Luis A. Leiva <luis.leiva@uni.lu>
Date created: 25/4/2022
Date last modified: 25/4/2022
Python Version: 3.5
'''

import sys
import json
import logparser
from stats_create import uid, chunked


if __name__ == '__main__':
    # This program takes as input any number of swipe logs
    # and writes in stdout one JSON object per swiped word.
    logfiles = sys.argv[1:]

    for logfile in logfiles:
        # The user ID is available in the log filename, so use the `uid()` function for that.
        user = uid(logfile)
        data = logparser.load(logfile)

        # NB: `data` is a dictionary with the following structure: `(sentence, [{row1}, ...])`.
        # And each row is a dictionary with these properties: `sentenceHash`, `canvasWidth`, etc.
        for sentence, rows in data.items():
            # Extract all swipe sequences, since there are several in each log file.
            good_chunks = chunked(rows, failed=False)
            # Each chunk contains data for a single word, so iterate over it.
            for chunk in good_chunks:
                output = json.dumps({
                    'username': user,
                    # The actual word is stored in each chunk's row as the "word" property,
                    # so we can read it from the very first row (index 0).
                    'word': chunk[0]['word'],
                    # Read swipe coordinates.
                    'swipe': [[d['x'], d['y'], d['timestamp']] for d in chunk]
                })
                print(output)
