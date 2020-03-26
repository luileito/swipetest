#!/usr/bin/env python3

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
Date last modified: 25/3/2020
Python Version: 3.5
'''

import csv
import sys
import os
import json
import math
from collections import OrderedDict

import numpy as np
from fastdtw import fastdtw

import logparser
from keyboard_impl import Keyboard


enron_dataset = '../data/mem200.txt'
rand2_dataset = '../data/dic-words-2k.txt'
rand3_dataset = '../data/dic-words-3k.txt'
rand5_dataset = '../data/dic-words-5k.txt'
rand0_dataset = '../data/oov-words.txt'

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
    # Exit early if chunks are empty. This happens if there are no failed words.
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
    return dtw(user_path, true_path)


def dtw(seq1, seq2):
    '''Compute dynamic time warping between two coordinate sequences.'''
    s1 = np.array(seq1)
    s2 = np.array(seq2)

    score, path = fastdtw(s1, s2, dist=euclidean_distance)
    return score


def swipe_coords(rows):
    '''Get flat sequence of swipe x,y coordinates.'''
    return [[r['x'], r['y']] for r in event_filter(rows, 'touchmove', False)]


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
    return [list(filter(lambda k : k['char'] == ch, layout)).pop() for ch in list(word)]


def word_centroids(keys):
    '''Get centroids of given list of keys. Each key is a dict.'''
    return [key_center(k) for k in keys]


def key_center(key):
    '''Compute key center in x and y axes. Key offsets (if any) are already considered.'''
    x_center = key['x'] + key['width'] / 2
    y_center = key['y'] + key['height'] / 2
    return [x_center, y_center]


def word_filter(rows, word, failed=None):
    '''Filter rows by word. When failed=None, BOTH success and failed words are considered.'''
    if failed is None:
        return list(filter(lambda r : r['word'] == word, rows))
    return list(filter(lambda r : r['word'] == word and r['isFailedWord'] == failed, rows))


def event_filter(rows, event, failed=None):
    '''Filter rows by event name. When failed=None, BOTH success and failed events are considered.'''
    if failed is None:
        return list(filter(lambda r : r['event'] == event, rows))
    return list(filter(lambda r : r['event'] == event and r['isFailedWord'] == failed, rows))


def success_words(rows):
    '''Get words that were entered correctly.'''
    filtered_rows = event_filter(rows, 'touchstart', False)
    return [row['word'] for row in filtered_rows]


def failed_words(rows):
    '''Get words that were not swiped correctly.'''
    filtered_rows = event_filter(rows, 'touchstart', True)
    return [row['word'] for row in filtered_rows]


def chunked(rows, failed=None):
    '''Split rows by each of the performed swipe trajectories.'''
    chunks = []
    groups = []
    for row in rows:
        if failed is not None and row['isFailedWord'] != failed:
            continue
        elif row['event'] == 'touchstart':
            groups = []
            groups.append(row)
        elif row['event'] == 'touchend':
            # Exclude outliers.
            time_diff = groups[-1]['timestamp'] - groups[0]['timestamp']
            if time_diff > 0 and time_diff < 9999:
                chunks.append(groups)
            groups = []
        elif row['event'] == 'touchmove':
            groups.append(row)
    return chunks


def liststats(values):
    '''Compute basic stats for a given list of values.'''
    N = len(values)
    if N > 0:
        stats = {
            'sample_size': N,
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


def uid(logfile):
    '''Compute user ID based on the given log filename.'''
    base = os.path.basename(logfile)
    base, _ = os.path.splitext(base)
    return base


def unique(sequence):
    '''Remove duplicates preserving the original sequence order.'''
    seen = set()
    return [x for x in sequence if not (x in seen or seen.add(x))]



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
                'username': uid(logfile)
            }

            # Some words were entered more than once, e.g. when the swipe was wrong.
            # So we need to keep two separate, unique word lists.
            uniq_good_words = unique(success_words(rows))
            uniq_fail_words = unique(failed_words(rows))

            # Compute classic stats: WPM and WER.
            num_failed = len(uniq_fail_words)
            num_tokens = len(uniq_good_words + uniq_fail_words)

            data['wer'] = num_failed / num_tokens

            # Extract all swipe sequences, since there are several in each log file.
            good_chunks = chunked(rows, False) # false means "non-failed words"
            fail_chunks = chunked(rows, True)  # vice versa

            data['good_interval_time'] = liststats(swipe_interval_times(good_chunks))
            data['fail_interval_time'] = liststats(swipe_interval_times(fail_chunks))

            # Estimate WPM using swipe times first.
            good_t = sum(map(swipe_time, good_chunks))
            fail_t = sum(map(swipe_time, fail_chunks))

            data['good_wpm_swipe'] = len(uniq_good_words) / (good_t / 1000 / 60) if good_t > 0 else None
            data['fail_wpm_swipe'] = len(uniq_fail_words) / (fail_t / 1000 / 60) if fail_t > 0 else None

            # Also compue WPM as usual: https://www.yorku.ca/mack/RN-TextEntrySpeed.html
            good_cps = len(' '.join(uniq_good_words)) / (good_t / 1000) if good_t > 0 else 0.0
            fail_cps = len(' '.join(uniq_fail_words)) / (fail_t / 1000) if fail_t > 0 else 0.0

            data['good_wpm_classic'] = good_cps * 60 / 5 if good_cps else None
            data['fail_wpm_classic'] = fail_cps * 60 / 5 if fail_cps else None

            if uniq_good_words:
                # Analyze successfully entered words separately.
                data['good_words'], dists, times = [], [], []

                for word in uniq_good_words:
                    for chunk in chunked(word_filter(rows, word, False)):

                        seq = event_filter(chunk, 'touchmove')

                        context = seq[0]['sentenceHash']
                        swipe_len = swipe_length(seq)
                        swipe_t = swipe_time(seq)

                        dists.append(swipe_len)
                        times.append(swipe_t)

                        data['good_words'].append({
                            'word': word,
                            'dataset': wordbin(word, context),
                            'length': swipe_len,
                            'time': swipe_t,
                            'dtw': swipe_path_deviation(seq)
                        })

                data['good_length'] = liststats(dists)
                data['good_time'] = liststats(times)

            if uniq_fail_words:
                # Analyze also wrongly entered words separately.
                data['fail_words'], dists, times = [], [], []

                for word in uniq_fail_words:
                    for chunk in chunked(word_filter(rows, word, True)):

                        seq = event_filter(chunk, 'touchmove')

                        context = seq[0]['sentenceHash']
                        swipe_len = swipe_length(seq)
                        swipe_t = swipe_time(seq)

                        dists.append(swipe_len)
                        times.append(swipe_t)

                        data['fail_words'].append({
                            'word': word,
                            'dataset': wordbin(word, context),
                            'length': swipe_len,
                            'time': swipe_t,
                            'dtw': swipe_path_deviation(seq)
                        })

                data['fail_length'] = liststats(dists)
                data['fail_time'] = liststats(times)

            # DEBUG: pretty-print only one sentence.
            print(json.dumps(OrderedDict(sorted(data.items())), indent=2))
            exit()

            # Use ndjson as output format: one stringified json object per line.
            print(json.dumps(data))
