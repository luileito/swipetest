<?php require_once 'config.php'; ?>
<?php
// Refresh session data.
$_SESSION['done_count'] = 0;
$_SESSION['done_words'] = array();

// If request is not asynchronous, redirect accordingly.
if (!isset($_GET['xhr'])) {
    header('Location: index.php');
}
