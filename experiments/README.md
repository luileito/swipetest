# Shape-writing scripts

Here you can find several programs we used to process the swipe log files.

You can get the log files at https://osf.io/sj67f/

## Install

You need Python >= 3 and NodeJS >= 10 interpreters.

## Usage

### Process demographics (user metadata)

The `demographics.py` file creates a consolidated file where each line describes each user:
```sh
~$ python3 demographics.py /path/to/swipelogs/*.json > metadata.tsv
```

### Plot swiped words

The `plotlog.js` file renders a swipe trajectory as a PNG file.

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

Example: assuming that the word "hello" is in `somefile.log` file,
this will create the file `somefile-hello-0.png`:
```sh
~$ node plotlog.js -l /path/to/somefile.log -w hello
```

The output filename pattern in always "username-word-flag.png", where "username" is the user ID (swipe log filename), "word" is the swiped word, and "flag" is either 0 (default) or 1 if the argument `-f` (or `--failed`) is provided.

### Process performance metrics

The `stats-create.py` program creates a consolidated file where each line is a dictionary summarizing both sentence and word level performance.

Example:
```sh
~$ python3 stats-create.py /path/to/swipelogs/*.log > swipelogs.ndjson
```

Read more about the ndjson format at http://ndjson.org/

### Analyze performance metrics

The `stats-analyze.py` program creates fine-grained performance reports.

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

Example: analyze all swiped words:
```sh
~$ python3 stats-analyze.py \
      --stats_file swipelogs.ndjson \
      --users_file metadata.tsv \
      --word_type good_words \
      --metric time > good_words-time-overall.dat
```

Example: analyze swipe time by users who are not familiarized with shape-writing:
```sh
~$ python3 stats-analyze.py \
      --stats_file swipelogs.ndjson \
      --users_file metadata.tsv \
      --group_name familiarity \
      --group_value never \
      --metric time > sentences-time-familiarity_never.dat
```

These are all the possible `group_name` and `group_value` combinations:

| group_name   | group_value                           |
|---           |---                                    |
| familiarity  | everyday,often,sometimes,rarely,never |
| gender       | male,female,other                     |
| age          | youth,young,adult,senior              |
| language     | english,non-english                   |
| nationality  | us,non-us                             |
| englishlevel | native,advanced,intermediate,beginner |
| dominanthand | left,right                            |
| swipehand    | left,right,both                       |
| swipefinger  | index,thumb,other                     |
| screenwidth  | small,large                           |
| vendor       | google,apple                          |

**Note:** Age categories are youth (<= 18), young (<=30), adult (<=40), senior (>40).

### Export datasets

The `db-export.py` file creates normalized datasets.
This is what we used to generate the processed data files (TSV extension) in https://osf.io/sj67f/

```sh
$ python3 db-export.py -h
usage: db-export.py [-h] [--stats_file STATS_FILE] [--users_file USERS_FILE] [--format {ndjson,tsv}] [--prefix PREFIX]

Export swipe datasets.

optional arguments:
  -h, --help            show this help message and exit
  --stats_file STATS_FILE
                        path to computed stats file
  --users_file USERS_FILE
                        path to user demographics file
  --format {ndjson,tsv}
                        database storage format
  --prefix PREFIX       database name prefix
```

Example:
```sh
~$ python3 db-export.py --stats_file swipelogs.ndjson --users_file metadata.tsv --prefix raw_
```
This will create 3 files: `raw_demographics.tsv`, `raw_words.tsv`, `raw_sentences.tsv`.
