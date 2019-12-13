<?php require_once 'config.php'; ?>
<?php
if (empty($_POST)) exit;

if (!empty($_POST['events'])) {

    // Enure than the user is always prompted unique words in the RANDOM condition.
    $word = filter_var($_POST['word'], FILTER_SANITIZE_STRING);
    $_SESSION['done_words'][] = $word;

    // Ignore trial sentences, since they're typically outliers.
    // Notice that `done_count` is used on the session-level,
    // whereas `prev_count` takes into account ALL the previous sessions.
    if ($_SESSION['prev_count'] >= NUM_TRIAL_SENTENCES) {
        $events = json_decode($_POST['events']);
        $entries = '';
        foreach($events as $event) {
            $entries .= implode(' ', $event).PHP_EOL;
        }
        file_put_contents(USER_EVENTS_FILE, $entries, FILE_APPEND);
    }

    if (isset($_POST['isDone'])) {
        // Increase sentence counter only when *all* words have been submitted.
        // The `isDone` flag accounts for this. See `main.js`.
        $finished = filter_var($_POST['isDone'], FILTER_VALIDATE_BOOLEAN);
        if ($finished) {
            $_SESSION['done_count']++; // Num sentences entered within the *current* session
            $_SESSION['prev_count']++; // Num sentences entered across ALL sessions

            // Also increase the counter of the RANDOM condition. See `keyb.php`.
            if ($_SESSION['condition'] === 'RANDOM') {
                $_SESSION['rand_count']++;
            } elseif ($_SESSION['condition'] === 'MEMORABLE') {
                // Notice: we receive a *sentence hash*, not a plain-text sentence.
                $sentence = filter_var($_POST['sentence'], FILTER_SANITIZE_STRING);
                file_put_contents(USER_SENTENCES_FILE, $sentence.PHP_EOL, FILE_APPEND);
            } else {
                // Condition not implemented.
            }
        }
    }

    _e('OK');
} else {
    _e('You should not post here.');
}
