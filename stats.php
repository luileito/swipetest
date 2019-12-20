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
      $cmd = sprintf('find %s -name "*.log" | while read f; do echo $(basename $f .log); done', LOGS_DIR);
      $out = shell_exec($cmd);
      $lst = explode(PHP_EOL, trim($out));
      ?>
      <h3>Participants</h3>
      <ul class="list-indented">
        <?php foreach ($lst as $l): ?>
          <li><a href="done.php?uid=<?php echo $l; ?>"><?php echo $l; ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </body>
</html>
