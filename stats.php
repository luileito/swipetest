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
        $user_id = filter_var($_GET['uid'], FILTER_SANITIZE_STRING);
        $user_file = LOGS_DIR.'/'.$user_id.'.log';
        if (empty($user_id)) {
            $cmd = sprintf('find %s -name "*.log" | while read f; do echo $(basename $f .log); done', LOGS_DIR);
            $out = shell_exec($cmd);
            $lst = explode(PHP_EOL, trim($out));
            ?>
            <h3>Participants</h3>
            <ul class="list-indented">
              <?php foreach ($lst as $l): ?>
                <li><a href="?uid=<?php echo $l; ?>"><?php echo $l; ?></a></li>
              <?php endforeach; ?>
            </ul>
            <?php
            exit;
        }

        $all_times = time_distribution(LOGS_DIR.'/*.log');
        $user_time = time_distribution($user_file);
        $time_percentile = percentile($all_times, $user_time);

        $all_errors = error_distribution(LOGS_DIR.'/*.log');
        $user_error = error_distribution($user_file);
        $error_percentile = percentile($all_errors, $user_error);

        $time_histogram = histogram($all_times, 5);
        $time_bin_index = find_value_in_histogram($time_histogram, $user_time);

        $error_histogram = histogram($all_errors, 5);
        $error_bin_index = find_value_in_histogram($error_histogram, $user_error);
      ?>

      <h4>
      <?php echo sprintf(_('You swipe faster than %d%% of all people'), 100 - $time_percentile); ?>
      </h4>
      <p>
      <?php echo sprintf(_('Your average swipe time is %.2f seconds per word.'), $user_time[0]); ?>
      <?php
      $best_time_performers = (count($all_times) - 1) * $time_percentile/100;
      echo sprintf(_('Overall, %d users did better than you.'), $best_time_performers);
      ?>
      </p>
      <canvas class="mb-5" id="time-chart"></canvas>

      <h4>
      <?php echo sprintf(_('You have less errors than %d%% of all people'), 100 - $error_percentile); ?>
      </h4>
      <p>
      <?php echo sprintf(_('Your average word error is %d%% per sentence.'), $user_error[0]); ?>
      <?php
      $best_error_performers = (count($all_errors) - 1) * $error_percentile/100;
      echo sprintf(_('Overall, %d users did better than you.'), $best_error_performers);
      ?>
      </p>
      <canvas class="mb-3" id="error-chart"></canvas>

      <script>
      $(function() {

          var timeHistogram = <?php echo json_encode($time_histogram); ?>;
          var timeBinIndex = <?php echo $time_bin_index; ?>;
          var errorHistogram = <?php echo json_encode($error_histogram); ?>;
          var errorBinIndex = <?php echo $error_bin_index; ?>;

          plot('time-chart', timeHistogram, timeBinIndex, '#3f9', 'Average swipe time in ms, lower is better');
          plot('error-chart', errorHistogram, errorBinIndex, '#f93', 'Average word errors in %, lower is better');

          function plot(elemId, histogram, binIndex, binColor, title) {
              var labels = Object.keys(histogram);
              var values = labels.map(function(bin) { return histogram[bin]; });
              // Highlight user bin and display remaining bars in gray color.
              var colors = Array.apply(null, Array(values.length)).map(function(v) { return '#ccc'; });
              colors[binIndex] = binColor;

              var ctx = document.getElementById(elemId).getContext('2d');
			        var plt = new Chart(ctx, {
				          type: 'bar',
				          data: {
				              labels: labels, //.map(function(bin) { return Math.ceil(bin.split('-').pop()); }),
				              datasets: [{
              				    label: 'users',
              				    data: values,
              				    backgroundColor: colors,
				              }]
				          },
				          options: {
					            responsive: true,
					            legend: {
            					    display: false,
					            },
					            title: {
    					            display: true,
					                text: title,
					            },
					            tooltips: {
                          //enabled: false,
                      },
                      scales: {
                          yAxes: [{
                              gridLines: {
                                  display: false,
                              },
                          }],
                          xAxes: [{
                              ticks: {
                                  //maxRotation: 90,
                                  //minRotation: 90,
                              },
                          }],
                      },
				          }
			        });
          }

      });
      </script>
    </div>
  </body>
</html>
