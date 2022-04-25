#!/usr/bin/env python3
# coding: utf-8

'''
Reads in a swipe log file and outputs a ndjson file with computed stats.

The following measures are computed:
 - Swipe time, in ms
 - Swipe length, in px
 - WPM: entry speed
 - WER: error rate (multiply it by 100 to get the percentage)
 - Inter-swipe time interval: time elapsed between swipes, in ms
 - Path deviation: DTW between sokgraph and swiped trajectory, in px

All measures are computed both for good and failed words,
excepting WER, which is an overall performance measure.

You can also use the exposed functions in your own programs.

---
Author: Luis A. Leiva <luis.leiva@aalto.fi>
Date created: 1/3/2020
Date last modified: 14/5/2020
Python Version: 3.5
'''

import csv
import sys
import os
import json
import math
from collections import OrderedDict
import unicodedata

import numpy as np
from fastdtw import fastdtw

import logparser
from keyboard_impl import Keyboard


enron_dataset = './data/mem200.txt'
rand2_dataset = './data/dic-words-2k.txt'
rand3_dataset = './data/dic-words-3k.txt'
rand5_dataset = './data/dic-words-5k.txt'
rand0_dataset = './data/oov-words.txt'

with open(enron_dataset) as f:
    enron_sentences = f.read().splitlines()
    enron_sentences = list(map(lambda x: x.replace(' ', '_'), enron_sentences))

with open(rand2_dataset) as f:
    rand2_words = f.read().splitlines()

with open(rand3_dataset) as f:
    rand3_words = f.read().splitlines()

with open(rand5_dataset) as f:
    rand5_words = f.read().splitlines()

with open(rand0_dataset) as f:
    rand0_words = f.read().splitlines()

# Clear unused globals, to get a nice `help()` message.
del f


def wordbin(word, sentence):
    '''Get dataset which the given word belongs to.'''
    if sentence in enron_sentences:
        return 'enron'
    elif word in rand2_words:
        return 'rand2k'
    elif word in rand3_words:
        return 'rand3k'
    elif word in rand5_words:
        return 'rand5k'
    elif word in rand0_words:
        return 'rand0'
    return None


def euclidean_distance(a, b):
    '''Compute Euclidean distance between two points.'''
    # This function is used also in DTW computation, so check points format.
    # Points are either given as dicts of x and y values or as 2d arrays.
    if type(a) is dict:
        a_x, a_y = a['x'], a['y']
        b_x, b_y = b['x'], b['y']
    else:
        a_x, a_y = a[0], a[1]
        b_x, b_y = b[0], b[1]

    dx = a_x - b_x
    dy = a_y - b_y
    return math.sqrt(dx**2 + dy**2)


def swipe_time(rows):
    '''Compute swipe time. Assume a SINGLE swiped word sequence.'''
    return rows[-1]['timestamp'] - rows[0]['timestamp']


def swipe_length(rows):
    '''Compute cumulative swipe distance. Assume a SINGLE swiped word sequence.'''
    distlist = [euclidean_distance(rows[i], rows[i+1]) for i in range(len(rows) - 1)]
    return sum(distlist)


def swipe_interval_times(chunks):
    '''Compute time elapsed between consecutive words. A chunk is a series of rows pertaining the same word.'''
    times = []
    # Exit early if chunks are empty. This happens e.g. when there are no failed words.
    if not chunks:
        return times
    # The first swipe becomes the reference for the second swipe, and so on.
    # Thus, the swipe interval time of a sentence with N words is computed using N-1 observations.
    for i in range(len(chunks) - 1):
        curr_chunk = chunks[i]
        next_chunk = chunks[i+1]
        time_diff = next_chunk[0]['timestamp'] - curr_chunk[-1]['timestamp']
        times.append(time_diff)
    return times


def swipe_path_deviation(rows):
    '''Compute point-wise difference between the user swipe and the "perfect" swipe (sokgraph).'''
    user_path = swipe_coords(rows)
    true_path = swipe_sokgraph(rows)
    if not user_path or not true_path:
        return None
    return dtw(user_path, true_path)


def dtw(seq1, seq2):
    '''Compute dynamic time warping between two coordinate sequences.'''
    s1 = np.array(seq1)
    s2 = np.array(seq2)

    score, path = fastdtw(s1, s2, dist=euclidean_distance)
    return score


def swipe_coords(rows):
    '''Get flat sequence of swipe x,y coordinates.'''
    return [[r['x'], r['y']] for r in rows]


def swipe_sokgraph(rows):
    '''Compute the sokgraph of given word, based on the QWERTY layout used in our study.'''
    keys = word_keys(rows)
    return word_centroids(keys)


def word_keys(rows):
    '''Get key positions for a given word.'''
    word = rows[0]['word']
    canvas_width = rows[0]['canvasWidth']
    canvas_height = rows[0]['canvasHeight']

    # In the web app, key size was proportional to the available screen size,
    # so we need to read how many rows and cols were there.
    kb = Keyboard()
    kb_size = kb.measure()
    kb.settings['keyWidth'] = canvas_width / kb_size['numCols']
    kb.settings['keyHeight'] = canvas_height / kb_size['numRows']

    layout = kb.getLayout()
    try:
        # Some logs have Unicode words, which is weird because users shouldn't be able to modify the DOM.
        # Most likely those cases were due to someone messing up with our web app.
        keys = [list(filter(lambda k : k['char'] == ch.lower(), layout)).pop() for ch in list(word)]
    except:
        return None
    return keys


def word_centroids(keys):
    '''Get centroids of given list of keys. Each key is a dict.'''
    if not keys:
        return None
    return [key_center(k) for k in keys]


def key_center(key):
    '''Compute key center in x and y axes. Key offsets (if any) are already considered.'''
    x_center = key['x'] + key['width'] / 2
    y_center = key['y'] + key['height'] / 2
    return [x_center, y_center]


def success_words(chunks):
    '''Get words that were entered correctly.'''
    filtered_rows = [chunk[0] for chunk in chunks if not chunk[0]['isFailedWord']]
    return [row['word'] for row in filtered_rows]


def failed_words(chunks):
    '''Get words that were not swiped correctly.'''
    filtered_rows = [chunk[0] for chunk in chunks if chunk[0]['isFailedWord']]
    return [row['word'] for row in filtered_rows]


def chunked(rows, failed=None):
    '''Split rows by each of the performed swipe trajectories.'''
    chunks = []
    myrows = []

    prev_word = None

    for row in rows:
        if failed is not None and row['isFailedWord'] != failed:
            continue

        # We shouldn't rely on touchstart and touchend events since some of them were not logged.
        # However, for failed words we need to look for touchend events,
        # as failed words had to be resubmitted as many times as needed until getting them right.
        if prev_word is None or row['word'] != prev_word or row['event'] == 'touchend':
            if myrows:
                chunks.append(myrows)
                myrows = []
            prev_word = row['word']
        elif row['event'] == 'touchmove':
            myrows.append(row)

    # Flush remaining data.
    if myrows:
        chunks.append(myrows)

    return chunks


def stats(values, lo=None, hi=None):
    '''Compute basic stats for a given list of values.
       Optionally pass in a lower/upper threshold to ignore values outside that range.
    '''
    # Remove `None`s to begin with.
    values = [v for v in values if v]

    N = len(values)
    if N > 0:
        values = remove_outliers(values, lo=lo, hi=hi)
        stats = {
            'sample_size': len(values),
            'mean': np.mean(values),
            'median': np.median(values),
            'stdev': np.std(values),
            'values': values,
        }
    else:
        # Flag empty sequences as NAs, to ease later analysis.
        stats = {
            'sample_size': N,
            'mean': None,
            'median': None,
            'stdev': None,
            'values': values,
        }

    return stats


def remove_outliers(values, s=2, lo=None, hi=None):
    '''Remove values that are outside (1) the Mean +- s*SD range or (2) a given lower/upper threshold.'''
    if lo:
        values = [v for v in values if v > lo]
    if hi:
        values = [v for v in values if v < hi]
    data = np.array(values)
    filtered = data[abs(data - np.mean(data)) < s * np.std(data)]
    return filtered.tolist()


def uid(logfile):
    '''Compute user ID based on the given log filename.'''
    base = os.path.basename(logfile)
    base, _ = os.path.splitext(base)
    return base


def unique(sequence, preserve_order=True):
    '''Remove duplicates preserving the original sequence order.'''
    if preserve_order:
        seen = set()
        return [x for x in sequence if not (x in seen or seen.add(x))]
    else:
        return list(set(sequence))



if __name__ == '__main__':

    logfiles = sys.argv[1:]
    print('Reading {} logfiles ...'.format(len(logfiles)), file=sys.stderr)

    for logfile in logfiles:

        dataset = logparser.load(logfile)
        # NB: dataset is a dictionary with the following structure: `(sentence, [{row1}, ...])`.
        for sentence, rows in dataset.items():
            # Analyze each sentence separately.
            data = {
                'sentence': sentence,
                'dataset': wordbin(None, sentence),
                'username': uid(logfile)
            }

            # Extract all swipe sequences, since there are several in each log file.
            good_chunks = chunked(rows, failed=False)
            fail_chunks = chunked(rows, failed=True)

            good_words = success_words(good_chunks)
            fail_words = failed_words(fail_chunks)

            # Failed words were entered more than once, e.g. when the swipe was wrong.
            # So we need to keep two separate, unique word lists.
            uniq_good_words = unique(good_words)
            uniq_fail_words = unique(fail_words)

            if not uniq_good_words and not uniq_fail_words:
                continue

            # Compute WER.
            # Notice that failed words are only counted once,
            # since they could have been entered more than once.
            num_failed = len(uniq_fail_words)
            num_tokens = len(sentence.split('_'))
            data['wer'] = num_failed / num_tokens

            # Assume that there cannot be more than 10 seconds between two swipes,
            # otherwise it should be considered an outlier observation.
            data['good_interval_time'] = stats(swipe_interval_times(good_chunks), hi=10000)
            data['fail_interval_time'] = stats(swipe_interval_times(fail_chunks), hi=10000)

            # There are different ways of computing WPM;
            # see e.g. https://www.yorku.ca/mack/RN-TextEntrySpeed.html
            good_t = sum(map(swipe_time, good_chunks))
            fail_t = sum(map(swipe_time, fail_chunks))

            if good_t > 0:
                good_t += sum(data['good_interval_time']['values'])
                # Classic WPM computation.
                good_cps = len(' '.join(good_words)) / (good_t / 1000)
                data['good_wpm_classic'] = good_cps * 60 / 5
                # Swipe-based computation.
                data['good_wpm_swipe'] = len(good_words) / (good_t / 1000 / 60)
            else:
                data['good_wpm_classic'] = None
                data['good_wpm_swipe'] = None

            if fail_t > 0:
                fail_t += sum(data['fail_interval_time']['values'])
                # Classic WPM computation.
                fail_cps = len(' '.join(uniq_fail_words)) / (fail_t / 1000)
                data['fail_wpm_classic'] = fail_cps * 60 / 5
                # Swipe-based computation.
                data['fail_wpm_swipe'] = len(uniq_fail_words) / (fail_t / 1000 / 60)
            else:
                data['fail_wpm_classic'] = None
                data['fail_wpm_swipe'] = None

            if uniq_good_words:
                # Analyze successfully entered words separately.
                data['good_words'], dists, times, dtws = [], [], [], []

                for chunk in good_chunks:
                    word = chunk[0]['word']
                    context = chunk[0]['sentenceHash']

                    swipe_len = swipe_length(chunk)
                    swipe_t = swipe_time(chunk)
                    dtw_dist = swipe_path_deviation(chunk)

                    dists.append(swipe_len)
                    times.append(swipe_t)
                    dtws.append(dtw_dist)

                    data['good_words'].append({
                        'word': word,
                        'dataset': wordbin(word, context),
                        'length': swipe_len,
                        'time': swipe_t,
                        'dtw': dtw_dist
                    })

                data['good_length'] = stats(dists)
                data['good_time'] = stats(times)
                data['good_dtw'] = stats(dtws)

            if uniq_fail_words:
                # Analyze also wrongly entered words separately.
                data['fail_words'], dists, times, dtws = [], [], [], []

                for chunk in fail_chunks:
                    word = chunk[0]['word']
                    context = chunk[0]['sentenceHash']

                    swipe_len = swipe_length(chunk)
                    swipe_t = swipe_time(chunk)
                    dtw_dist = swipe_path_deviation(chunk)

                    dists.append(swipe_len)
                    times.append(swipe_t)
                    dtws.append(dtw_dist)

                    data['fail_words'].append({
                        'word': word,
                        'dataset': wordbin(word, context),
                        'length': swipe_len,
                        'time': swipe_t,
                        'dtw': dtw_dist
                    })

                data['fail_length'] = stats(dists)
                data['fail_time'] = stats(times)
                data['fail_dtw'] = stats(dtws)

            entry = OrderedDict(sorted(data.items()))

#            # DEBUG: pretty-print only one sentence.
#            print(json.dumps(entry, indent=2))
#            exit()

            # Use ndjson as output format: one stringified json object per line.
            print(json.dumps(entry))
