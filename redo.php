<?php require_once 'config.php'; ?>
<?php
// Refresh session data.
// Don't reset `prev_count` since we want to log ALL sentences from returning users.
$_SESSION['done_count'] = 0;
$_SESSION['done_words'] = array();
$_SESSION['rand_count'] = 0;

// If request is not asynchronous, redirect accordingly.
if (!isset($_GET['xhr'])) {
    header('Location: index.php');
}
