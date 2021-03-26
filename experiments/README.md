# Shape-writing scripts

Here you can find several programs we used to process the swipe log files.

You can get the log files at https://github.com/luileito/swipedataset

## Install

You need Python >= 3 and NodeJS >= 10 interpreters.

## Usage

### Demographics and user metadata

Create a consolidated file where each line describes each user:
```sh
~$ python3 demographics.py /path/to/swipelogs/*.json > metadata.tsv
```

### Plot swiped words

The `plotlog.js` file render a swipe trajectory as a PNG file.

```sh
~$ node plotlog.js -h
Usage:
  node plotlog.js [OPTION]

Options:
  -l, --logFile=ARG Log file
  -w, --word=ARG    Word to plot
  -f, --failed      Show failed word, if present
  -v, --verbose     Display debug info
  -h, --help        Display this help
```

Example: assuming that the word "hello" is in `somefile.log` file:
```sh
~$ node plotlog.js -l /path/to/somefile.log -w hello
```

## Process performance metrics

The `stats-create.py` program create a consolidated file where each line summarizes both sentence and word level performance.

Example:
```sh
~$ python3 stats-create.py /path/to/swipelogs/*.log > swipelogs.ndjson
```

### Analyze performance metrics

The `stats-analyze.py` program is fairly complete.

```sh
~$ python3 stats-analyze.py -h
usage: stats-analyze.py [-h] --metric METRIC --stats_file STATS_FILE [--users_file USERS_FILE] [--group_name GROUP_NAME] [--group_value GROUP_VALUE] [--header] [--dataset {enron,rand,rand2k,rand3k,rand5k,rand0}] [--word_type {good_words,fail_words}]

Analyze ndjon-based swipe dataset.

optional arguments:
  -h, --help            show this help message and exit
  --metric METRIC       target metric to report
  --stats_file STATS_FILE
                        path to computed stats file
  --users_file USERS_FILE
                        path to use demographics file
  --group_name GROUP_NAME
                        e.g. familiarity, dominanthand, etc.
  --group_value GROUP_VALUE
                        e.g. often, left, etc.
  --header              print header
  --dataset {enron,rand,rand2k,rand3k,rand5k,rand0}
                        dataset
  --word_type {good_words,fail_words}
                        word type
```

Examples:
```sh
~$ python3 stats-analyze.py \
      --stats_file swipelogs.ndjson \
      --users_file metadata.tsv \
      --word_type good_words \
      --metric time >> good_words-time.dat

~$ python3 stats-analyze.py \
      --stats_file swipelogs.ndjson \
      --users_file metadata.tsv \
      --group_name familiarity \
      --group_value never \
      --group_name vendor \
      --group_value apple \
      --metric time > sentences-time-familiarity_never-vendor_apple.dat
```
