<?php
session_start();

// We need a sentence counter, both at the session level and for all sessions.
// We also need to keep track of the individual words entered so far.
if (!isset($_SESSION['done_count'])) $_SESSION['done_count'] = 0; // Num sentences entered within the *current* session
if (!isset($_SESSION['prev_count'])) $_SESSION['prev_count'] = 0; // Num sentences entered across ALL sessions
if (!isset($_SESSION['done_words'])) $_SESSION['done_words'] = array();
if (!isset($_SESSION['rand_count'])) $_SESSION['rand_count'] = 0; // Num sentences entered in the RANDOM condition

// Use `time()` to request a fresh copy of our JS and CSS files (very useful when debugging).
// Otherwise set to a fixed string, e.g. '1.0.0'.
define('VERSION', time());
// We can use whatever ID to unequivocally identify the user,
// so let's take advantage of this built-in ID.
define('USER_ID', session_id());
// Path to store all user logs.
define('LOGS_DIR', './logs');
// Path to read datasets.
define('DATA_DIR', './data');
// File to write the swipe events.
define('USER_EVENTS_FILE', LOGS_DIR.'/'.USER_ID.'.log');
// File to write the users' metadata.
define('USER_METADATA_FILE', LOGS_DIR.'/'.USER_ID.'.json');
// File to write the sentences entered by the user from the MEMORABLE condition.
// NB: Each line will contain a hashed sentence, not plain text.
define('USER_SENTENCES_FILE', LOGS_DIR.'/'.USER_ID.'.txt');
// File to write the user feedback (reported issues).
// Notice that any user can report any issue, even before taking part in the test.
define('USER_FEEDBACK_FILE', LOGS_DIR.'/feedback.txt');
// Path to the MEMORABLE sentences file.
// One sentence per line, lowercased, no punctuation symbols, no numbers.
define('DATA_SENTENCES_FILE', DATA_DIR.'/mem200.txt');
// Number of sentences each user should do in a session.
define('MAX_NUM_SENTENCES', 15);
// Number of sentences composed of random words, pooled from Google's Trillion Corpus.
// We want the user to enter as much random words as possible, but we need to collect familiar sentences.
// Thus, the remaining sentences will be pooled from the Enron mobile dataset (MEMORABLE condition).
define('NUM_RANDOM_SENTENCES', 10);
// Add at least one trial sentence, so that users can get familizarized with the look and feel of the keyboard.
// For returning users, we will log ALL sentences.
define('NUM_TRIAL_SENTENCES', 1);
// Number of estimated minutes for taking the test.
// It should be proportional to the number of sentences and their length.
define('MAX_EST_MINUTES', 5);
// When there are no more sentences to display, the user will be shown the "done" view.
define('NUM_TODO_SENTENCES', MAX_NUM_SENTENCES + NUM_TRIAL_SENTENCES - $_SESSION['done_count']);

// Ensure that dirs exist.
if (!file_exists(DATA_DIR)) die(sprintf(_('Not found: %s'), DATA_DIR));
if (!file_exists(LOGS_DIR)) mkdir(LOGS_DIR);

/**
 * Shorthand for echo'ing a localized string.
 * @param {string} $msg Message to print.
 */
function _e($msg) {
    echo _($msg);
}

/**
 * Compute time distribution, aggregated (averaged) by user;
 * i.e. each data point is the mean time each user took to swipe all words.
 * @param {string} $glob Glob pattern to expand.
 * @return {array}
 */
function time_distribution($glob) {
    // Notice that users can enter the same sentence in different sessions,
    // so only the latest entry will be considered.
    $cmd = sprintf("for f in %s; do \
      grep -w touchmove \$f | awk 'BEGIN {
        word[$1] = 0x42;
      } $9 == 0 {
        if (word[$1] != $8) hdic[$1][$8][0] = $2;
        hdic[$1][$8][1] = $2;
        word[$1] = $8;
      } END {
        for (h in hdic) {
          for (w in hdic[h]) {
            t0 = hdic[h][w][0];
            t1 = hdic[h][w][1];
            t = t1 - t0;
            if (t > 0 && t < 9999) print t;
          }
        }
      }' | awk '{ sum += $1 } END { print (sum/NR)/1000 }'; done | sort -n", $glob);

    $out = shell_exec($cmd);
    $values = explode(PHP_EOL, trim($out));
    return array_map('floatval', $values);
}

/**
 * Compute word error distribution, aggregated (averaged) by user;
 * i.e. each data point is the mean word error per sentence.
 * @param {string} $glob Glob pattern to expand.
 * @return {array}
 */
function error_distribution($glob) {
    // We count errors on a per-word basis, i.e. if the user swiped several
    // times the same word in a row, then only one error is considered.
    $cmd = sprintf("for f in %s; do \
      grep -w touchend \$f | awk '{
        if ($9 == 1) errs[$1][$8] = 0x42;
        else tots[$1][$8] = 0x42;
      } END {
        for (h in tots) {
          errsum = length(errs[h]);
          totsum = length(tots[h]);
          print errsum / totsum;
        }
      }' | awk '{ sum += $1 } END { print 100*sum/NR }'; done | sort -n", $glob);

    $out = shell_exec($cmd);
    $values = explode(PHP_EOL, trim($out));
    return array_map('floatval', $values);
}

/**
 * Compute position of a given value within a list of values.
 * @param {array} $values Distribution of population values.
 * @param {int|array} $value Distribution of user's swipe times. Only one value is expected.
 * @return {float}
 */
function find_position($values, $value) {
    if (is_array($value)) $value = $value[0];

    $index = array_search($value, $values);
    if ($index === FALSE) return -1;
    return $index;
}

function percentile($values, $value) {
    $index = find_position($values, $value);
    $tally = ($index + 1) / count($values);
    $percentile = round(100 * $tally);
    return $percentile;
}

/**
 * Compute histogram for a series of values.
 * @param {array} $values Distribution of values.
 * @param {int} $num_bins Number of histogram bins. Default: `1`.
 * @param {float} $min_val Minimum value in the distribution. Default: `min(values)`.
 * @param {float} $max_val Minimum value in the distribution. Default: `max(values)`.
 * @return {array}
 */
function histogram($values, $num_bins=1, $min_val=NULL, $max_val=NULL) {
    if (is_null($min_val)) $min_val = min($values);
    if (is_null($max_val)) $max_val = max($values);

    $step_val = ($max_val - $min_val) / $num_bins;
    $widths = range($min_val, $max_val, $step_val);
    $bins = array();
    foreach ($widths as $key => $val) {
        if (!isset($widths[$key + 1])) break;
        $bins[] = array($val, $widths[$key + 1]);
    }
    // Allocate histogram bins (fill with zeros).
    $histogram = array();
    foreach ($bins as $bin) {
        list($lo, $hi) = $bin;
        $key = fmtbin($lo, $hi);
        $histogram[$key] = 0;
    }
    // Compute frequency values.
    foreach ($values as $val) {
        $closest_bin = $bins[0];
        foreach ($bins as $bin) {
            list($lo, $hi) = $bin;
            $val = round($val);
            if ($val >= $lo && $val <= $hi) {
                $closest_bin = $bin;
                break;
            }
        }
        list($lo, $hi) = $closest_bin;
        $key = fmtbin($lo, $hi);
        $histogram[$key]++;
    }
    return $histogram;
}

/**
 * Format histogram bin label.
 * @param {float} $lo Lower bound.
 * @param {float} $hi Upper bound.
 * @return {string}
 */
function fmtbin($lo, $hi) {
    return pr1($lo).'-'.pr1($hi);
}

/**
 * Pretty-round number to one decimal place.
 * @param {float} $value Input number.
 * @return {float}
 */
function pr1($value) {
    return round($value, 1);
}

/**
 * Find the histogram bin containing a given value.
 * @param {array} $histogram Distribution of values.
 * @param {float} $value Number of to find in histogram bins.
 * @return {int}
 */
function find_value_in_histogram($histogram, $value) {
    if (is_array($value)) $value = $value[0];

    $bin_index = 0;
    foreach ($histogram as $bin_label => $count) {
        list($lo, $hi) = explode('-', $bin_label);
        // NB: Apply the same numerical preprocessing as histogram keys.
        $value = pr1($value);
        if ($value >= $lo && $value <= $hi) {
            break;
        }
        $bin_index++;
    }
    return $bin_index;
}
