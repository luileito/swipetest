<?php require_once 'config.php'; ?>
<?php
if (!file_exists(USER_METADATA_FILE)) {
    include 'form.php';
} else {
    if (NUM_TODO_SENTENCES > 0) {
        include 'keyb.php';
    } else {
        include 'done.php';
    }
}
