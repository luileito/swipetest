#!/usr/bin/env python

import csv
from collections import defaultdict


def load(filename):
    '''Extract useful data (in the right type) for later analysis.'''
    rows = defaultdict(list)
    headers = ['sentenceHash', 'timestamp', 'canvasWidth', 'canvasHeight', 'event', 'x', 'y', 'rx', 'ry', 'angle', 'word', 'isFailedWord']
    with open(filename) as csvfile:
        reader = csv.reader(csvfile, delimiter=' ')
        for row in reader:
            # Some rows have more than 12 columns,
            # but it's OK since we need to read up to the 12th column.
            # What we can't parse are rows with _less_ than 12 columns.
            if len(row) >= len(headers):
                entry = {
                    'sentenceHash': row[0],
                    'timestamp': int(row[1]),
                    'canvasWidth': int(row[2]),
                    'canvasHeight': int(row[3]),
                    'event': row[4],
                    'x': int(row[5]),
                    'y': int(row[6]),
                    'word': row[10],
                    'isFailedWord': row[-1] == '1',
                }
            else:
                raise Exception('Unsupported row format: {} columns'.format(len(row)))

            # Index data by sentence, to ease later postprocessing.
            rows[row[0]].append(entry)

    return dict(rows)


if __name__ == '__main__':
  import sys
  logfile = sys.argv[1]

  data = load(logfile)
  sentences = data.keys()
  print('Log file has {} sentences'.format(len(sentences)))

  # Display an excerpt of the data.
  print(data[sentences[0]][0])
  print(data[sentences[0]][-1])
  print('...')
  print(data[sentences[-1]][0])
  print(data[sentences[-1]][-1])
