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
 * @param {int} $min_obs Minimum number of sentences per user to show results. Otherwise the user will be ignored.
 * @return {array}
 */
function time_distribution($glob, $min_obs = 1) {
    // Notice that users can enter the same sentence in different sessions,
    // so only the latest (non-failed) entry will be considered.
    // NB: Each row in a log file has the following format:
    // ['sentence', 'timestamp', ..., 'word', 'isFailedWord']
    $cmd = sprintf("for f in %s; do \
      grep -w touchmove \$f | awk 'BEGIN {
        words[$1] = 0x42;
      } $(NF) == 0 {
        w = $(NF - 1);
        if (words[$1] != w) hdict[$1][w][0] = $2;
        hdict[$1][w][1] = $2;
        words[$1] = w;
      } END {
        for (h in hdict) {
          for (w in hdict[h]) {
            t0 = hdict[h][w][0];
            t1 = hdict[h][w][1];
            t = t1 - t0;
            if (t > 0 && t < 9999) print t;
          }
        }
      }' | awk '{
        obs++;
        sum += $1;
      } END { if (obs > %s) print (sum/NR)/1000; }'; done | sort -n", $glob, $min_obs);

    $out = shell_exec($cmd);
    $values = explode(PHP_EOL, trim($out));
    return array_map('floatval', $values);
}

/**
 * Compute word error distribution, aggregated (averaged) by user;
 * i.e. each data point is the mean word error per sentence.
 * @param {string} $glob Glob pattern to expand.
 * @param {int} $min_obs Minimum number of sentences per user to show results. Otherwise the user will be ignored.
 * @return {array}
 */
function error_distribution($glob, $min_obs = 1) {
    // We count errors on a per-word basis, i.e. if the user swiped several
    // times the same word in a row, then only one error is considered.
    // NB: Each row in a log file has the following format:
    // ['sentence', 'timestamp', ..., 'word', 'isFailedWord']
    $cmd = sprintf("for f in %s; do \
      grep -w touchend \$f | awk '{
        w = $(NF - 1);
        if ($(NF) == 1) errs[$1][w] = 0x42;
        else tots[$1][w] = 0x42;
      } END {
        for (h in tots) {
          errsum = length(errs[h]);
          totsum = length(tots[h]);
          e = errsum / totsum;
          if (e < 1) print e;
        }
      }' | awk '{
        obs++;
        sum += $1;
      } END { if (obs > %s) print 100*sum/NR }'; done | sort -n", $glob, $min_obs);

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
            $val = pr1($val);
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

/**
 * Find prefix in string.
 * @param {string} $source Input string.
 * @param {string} $prefix Prefix to search for.
 * @return {bool}
 */
function str_starts_with($source, $prefix) {
    return strncmp($source, $prefix, strlen($prefix)) == 0;
}

/**
 * Set translation domain path, relative to current app's dir.
 * @param {string} $domain Translation domain; e.g. "messages".
 * @return {bool}
 */
function gettext_load_domain($domain) {
    // Load translations from the `locale` dir.
    $lc_dir = __DIR__ . '/locale';

    $res = bindtextdomain($domain, $lc_dir);
    if (!$res) {
        trigger_error(sprintf('Could not load "%s" domain codeset from %s', $domain, $lc_dir));
        return FALSE;
    }

    // It is important to specify the encoding. Must match that of the PO file.
    $res = bind_textdomain_codeset($domain, 'UTF-8');
    if (!$res) {
        trigger_error('Could not bind "%s" domain codeset. Is it installed?');
        return FALSE;
    }

    return TRUE;
}

if (!function_exists('locale_accept_from_http')) {
    function locale_accept_from_http($header) {
        return 'en_US';
    }
}

/**
 * Read the preferred user's language locale.
 * @return {string}
 */
function gettext_get_language() {
    $lc_iso = NULL;

    // Read user's language from different sources.
    if (isset($_GET['hl'])) {
        // Change language via URL param.
        $lc_iso = filter_var($_GET['hl'], FILTER_SANITIZE_STRING);
    } elseif (isset($_COOKIE['hl'])) {
        // Change language via cookie.
        $lc_iso = $_COOKIE['hl'];
    } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        // Change language via HTTP lang header.
        $lc_iso = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    } else {
        // Fallback language.
        $lc_iso = 'en_US';
    }

    // Now look at the actually available locales in the OS.
    foreach ($os_locales as $lcid) {
        if (str_starts_with($lcid, $lc_iso)) {
            $lc_actual = $lcid;
            break;
        }
    }

    return $lc_actual;
}

/**
 * Apply user interface translations based on a given locale.
 * @param {string} $lc_iso Language locale.
 * @return {bool}
 */
function gettext_apply_translations($lc_iso) {
    if (!$lc_iso) {
        trigger_error('Cannot translate from an empty locale.');
        return FALSE;
    }

    // NB: Apparently Windows servers need *both* `setlocale()` and `putenv()`,
    // but our application requires a Unix-based OS so we don't need to call `putenv()`.
    $res = setlocale(LC_MESSAGES, $lc_iso);
    if (!$res) {
        trigger_error(sprintf('Could not set the "%s" locale. Is it installed?', $lc_iso));
        return FALSE;
    }

    // Indicate the name of the MO file, without extension.
    $domain = 'messages';
    gettext_load_domain($domain);

    // Set default domain for the gettext function.
    $res = textdomain($domain);
    if (!$res) {
        trigger_error(sprintf('Could not set "%s" as default domain. Does it exist?', $domain));
        return FALSE;
    }

    return TRUE;
}

$lc_iso = gettext_get_language();
gettext_apply_translations($lc_iso);
