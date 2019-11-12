<?php require_once 'config.php'; ?>
<?php
if (empty($_POST)) exit;

if (!empty($_POST['events'])) {
    // NEW: We now use N-word lists instead of regular sentences.
    // So enure than the user is prompted unique words always.
    $word = filter_var($_POST['word'], FILTER_SANITIZE_STRING);
    $_SESSION['done_words'][] = $word;

    if (isset($_POST['isDone'])) {
        // Increase sentence counter only when all words have been submitted.
        $finished = filter_var($_POST['isDone'], FILTER_VALIDATE_BOOLEAN);
        if ($finished) {
            /*
            // Notice: we receive a *sentence hash*, not a plain-text sentence.
            $sentence = filter_var($_POST['sentence'], FILTER_SANITIZE_STRING);
            file_put_contents(USER_SENTENCES_FILE, $sentence.PHP_EOL, FILE_APPEND);
            */
            $_SESSION['done_count']++;
            $_SESSION['prev_count']++;
        }
    }

    // Ignore trial sentences, since they're typically outliers.
    // Notice that `done_count` is used on the session-level,
    // whereas `prev_count` takes into account ALL the previous sessions.
    if ($_SESSION['prev_count'] > NUM_TRIAL_SENTENCES) {
        $events = json_decode($_POST['events']);
        $entries = '';
        foreach($events as $event) {
            $entries .= implode(' ', $event).PHP_EOL;
        }
        file_put_contents(USER_EVENTS_FILE, $entries, FILE_APPEND);
    }

    _e('OK');
} else {
    _e('You should not post here.');
}
