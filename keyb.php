<?php require_once 'config.php'; ?>
<?php
/*
// User sentences are stored as sha1 hashes.
$hash_sentences = file(USER_SENTENCES_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
// All sentences are assumed to be lowercased, no punctuation, no numbers.
$data_sentences = file(DATA_SENTENCES_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Pick a sentence at random, ensuring it hasn't been already typed.
// If the user has completed the full dataset (unlikely but theoretically possible),
// then pick any sentence at random.
do {
    $rand_idx = rand(0, count($data_sentences) - 1);
    $sentence = $data_sentences[$rand_idx];
    $txt_hash = sha1($sentence);
} while (in_array($txt_hash, $hash_sentences) && count($hash_sentences) < count($data_sentences));

// We could do some preprocessing here,
// but it's better to display the dataset as is.
$tokens = explode(' ', $sentence);
*/

// Present the user with 4-word sentences, where there is always:
// - one highly frequent word
// - one common word
// - one uncommon word
// - one out-of-vocabulary word
$tokens = array();
// Maybe define these files in `config.php` but then anybody accessing ANY of the URLs will allocate too much data unnecesarily.
if (empty($_SESSION['bin1'])) $_SESSION['bin1'] = file(DATA_DIR.'/dic-words-2k.txt', FILE_IGNORE_NEW_LINES); // Highly frequent words
if (empty($_SESSION['bin2'])) $_SESSION['bin2'] = file(DATA_DIR.'/dic-words-3k.txt', FILE_IGNORE_NEW_LINES); // Somewhat common words
if (empty($_SESSION['bin3'])) $_SESSION['bin3'] = file(DATA_DIR.'/dic-words-5k.txt', FILE_IGNORE_NEW_LINES); // Infrequent words
if (empty($_SESSION['bin4'])) $_SESSION['bin4'] = file(DATA_DIR.'/oov-words.txt', FILE_IGNORE_NEW_LINES);    // Out of vocabulary words

foreach (array('bin1', 'bin2', 'bin3', 'bin4') as $bin) {
    do {
        $rand_idx = rand(0, count($_SESSION[$bin]) - 1);
        $word = $_SESSION[$bin][$rand_idx];
    } while (in_array($word, $_SESSION['done_words']));
    $tokens[] = $word;
}
// This is to keep retro-compatibility with the previous system implementation.
$txt_hash = sha1(implode(',', $tokens));

// Randomize word order to account for potential confounding factors,
// e.g. if the last token is always the OOV, the user might write it without effort
// since s/he already entered 3 words and thus has some "inertia".
shuffle($tokens);

//if (isset($_GET['debug'])) var_dump($_SESSION['done_words']);
?>
<!doctype html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css?v=<?php echo VERSION; ?>" />
    <link rel="stylesheet" type="text/css" href="css/common.css?v=<?php echo VERSION; ?>" />
    <link rel="stylesheet" type="text/css" href="css/keyb.css?v=<?php echo VERSION; ?>" />
    <script type="text/javascript" src="js/vendor/jquery-2.0.2.min.js"></script>
    <script type="text/javascript" src="js/vendor/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/keyboard-impl.js?v=<?php echo VERSION; ?>"></script>
    <script type="text/javascript" src="js/main.js?v=<?php echo VERSION; ?>"></script>
  </head>
  <body>
    <div class="global">
      <div class="info">
        <img src="img/swipe-test-logo.png" alt="Swipe test logo" class="logo" />

        <p class="instructions">
          <?php echo sprintf(_('%s more sentences to go!'), NUM_TODO_SENTENCES); ?>
          <a href="#feedback">Report an issue</a>

          <br />

          <?php _e('Enter these words by swiping on the virtual keyboard below.'); ?>
          <b><?php _e('Try to <i>memorize</i> each word before you type.'); ?></b>
        </p>

        <h4 class="sentence" data-hash="<?php echo $txt_hash; ?>">
          <?php foreach ($tokens as $tok): ?>
            <span class="todo"><?php echo $tok; ?></span>
          <?php endforeach; ?>
        </h4>
      </div>
      <div class="container">
        <div class="message"></div>
        <canvas class="keyboard"></canvas>
      </div>
    </div>

    <script>
    $(function() {

        $('a[href=#feedback]').on('click', function(ev) {
            ev.preventDefault();
            $('#feedback').on('shown.bs.modal', function(e) {
                $(this).find('textarea').focus();
            }).modal('show');
        });

    });
    </script>

    <div class="modal fade" id="feedback" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form method="post" action="report.php">
          <div class="modal-header">
            <h5 class="modal-title"><?php _e('Feedback'); ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p><?php _e('Please describe the issue you have found.'); ?></p>
            <textarea name="comment" rows="4" style="width:100%" required></textarea>
            <input type="hidden" name="referer" value="<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" />
          </div>
          <div class="modal-footer">
            <input type="submit" class="btn btn-primary" value="<?php _e('Submit'); ?>" />
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e('Close'); ?></button>
          </div>
          </form>
        </div>
      </div>
    </div>

  </body>
</html>
