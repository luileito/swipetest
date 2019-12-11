<?php require_once 'config.php'; ?>
<?php
$comment = filter_var($_POST['comment'], FILTER_SANITIZE_STRING);
$referer = filter_var(urldecode($_POST['referer']), FILTER_SANITIZE_URL);
if (empty($referer)) $referer = '/index.php';

if (empty($comment)) {
    header('Location: '.$referer);
    exit;
}

$mark = date('r');
$contents  = 'Date: '.$mark.PHP_EOL;
$contents .= $comment.PHP_EOL.PHP_EOL;
file_put_contents(USER_FEEDBACK_FILE, $contents, FILE_APPEND);
?>
<!doctype html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
  </head>
  <body>
    <div class="container">
      <h1><?php echo _e('Thanks!'); ?></h1>
      <p><?php echo _e('Your feedback will be processed shortly.'); ?></p>
      <p><a href="<?php echo $referer; ?>"><?php echo _e('Go back'); ?></a></p>
  </body>
</html>
