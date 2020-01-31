<?php require_once 'config.php'; ?>
<!doctype html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="css/common.css?v=<?php echo VERSION; ?>" />
    <script type="text/javascript" src="js/vendor/jquery-2.0.2.min.js"></script>
    <script type="text/javascript" src="js/vendor/chart.min.js"></script>
  </head>
  <body>
    <div class="container">
      <?php
      $cmd = sprintf('find %s -name "*.log" | while read f; do echo $(date -r $f "+%%Y-%%m-%%d") $(basename $f .log); done | sort -rn', LOGS_DIR);
      $out = shell_exec($cmd);
      $res = explode(PHP_EOL, trim($out));
      ?>

      <h3><?php _e('Participants'); ?></h3>
      <p><?php echo sprintf(_('So far %d users have taken the test.'), count($res)); ?></p>

      <ul class="list-indented">
        <?php foreach ($res as $line): ?>
          <?php list($date, $uid) = explode(' ', $line); ?>
          <li>
            <?php echo $date; ?>
            <a href="done.php?uid=<?php echo $uid; ?>"><?php echo $uid; ?></a>
            <?php // $t = time_distribution(LOGS_DIR.'/'.$uid.'.log')[0]; echo 60 / $t; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </body>
</html>
