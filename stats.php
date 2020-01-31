<?php require_once 'config.php'; ?>
<!doctype html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="css/common.css?v=<?php echo VERSION; ?>" />
    <style>
    .col {
      padding-bottom: 1em;
    }
    .col:nth-child(odd) {
      background-color: #eeeeee;
    }
    </style>
  </head>
  <body>

    <?php
    // Read JSON data from users who has submitted some sentences.
    // There are *much* more people who accessed the test but didn't submit.
    $cmd = sprintf('find %s -name "*.log" | sed "s,.log$,.json,g" | xargs cat', LOGS_DIR);
    $out = shell_exec($cmd);
    $res = explode(PHP_EOL, trim($out));

    $fields = array('gender', 'age', 'nationality', 'englishLevel', 'familiarity', 'dominantHand', 'swipeHand', 'swipeFinger', 'maxTouchPoints', 'platform', 'vendor');
    $bucket = array();
    foreach ($res as $line) {
        $json = json_decode($line, TRUE);
        foreach ($json as $key => $val) {
            if (in_array($key, $fields) && !empty($val)) {
                $bucket[$key][$val]++;
            }
        }
    }
    ?>

    <div class="container">

      <p>
        <b><?php _e('This is a raw dump of the server logs!'); ?></b>
        <?php echo sprintf(_('So far, %d users submitted at least one sentence.'), count($res)); ?>
      </p>

      <div class="row">
        <?php foreach ($bucket as $name => $group): ?>
            <div class="col">
              <h3><?php echo $name; ?></h3>

              <?php ksort($group); ?>

              <?php foreach ($group as $key => $val): ?>
                <b><?php echo $key; ?>:</b> <?php echo $val; ?><br/>
              <?php endforeach; ?>

            </div>
        <?php endforeach; ?>
      </div><!-- .row -->
    </div>

  </body>
</html>
