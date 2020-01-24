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
      <img src="img/swipe-test-logo.png" alt="Swipe test logo" class="logo" />

      <p class="text-center">
        <img src="img/tada.png" alt="Tada" />
      </p>

      <?php
      // We can reproduce other users' results by passing in the `uid` query param.
      // If the param is not set, we use the session data.
      $user_id = filter_var($_GET['uid'], FILTER_SANITIZE_STRING);
      $user_log_file = LOGS_DIR.'/'.$user_id.'.log';
      if (empty($user_id)) {
          $user_log_file = USER_EVENTS_FILE;
      }

      $all_times = time_distribution(LOGS_DIR.'/*.log');
      $user_time = time_distribution($user_log_file);
      $time_percentile = percentile($all_times, $user_time);

      // Antti dixit: "Report WPM instead".
      // FIXME: Remove outliers by IQR or M+2D criteria.
      // Currently ignore users who swiped for 0.2 seconds on average,
      // which corresponds to a typing speed of 300 WPM.
      $user_wpm = 60 / $user_time[0];
      $all_wpms = array_map(function($t) { return ($t > 0.2) ? 60/$t : 0; }, $all_times);
      $wpm_percentile = percentile($all_wpms, $user_wpm);

      $all_errors = error_distribution(LOGS_DIR.'/*.log');
      $user_error = error_distribution($user_log_file);
      $error_percentile = percentile($all_errors, $user_error);


      //$time_histogram = histogram($all_times, 10);
      //$time_bin_index = find_value_in_histogram($time_histogram, $user_time);

      $wpm_histogram = histogram($all_wpms, 10);
      $wpm_bin_index = find_value_in_histogram($wpm_histogram, $user_wpm);

      $error_histogram = histogram($all_errors, 10);
      $error_bin_index = find_value_in_histogram($error_histogram, $user_error);
      ?>

      <h4>
      <?php echo sprintf(_('You swipe faster than %d%% of all people'), 100 - $time_percentile); ?>
      </h4>
      <p>
        <b><?php echo sprintf(_('Your typing speed is %d words per minute.'), $user_wpm); ?></b>
        <?php echo sprintf(_('Your average swipe time is %.2f seconds per word.'), $user_time[0]); ?>
        <?php
        $best_time_performers = (count($all_times) - 1) * $time_percentile/100;
        echo sprintf(_('Overall, %d users did better than you.'), $best_time_performers);
        ?>
      </p>
      <canvas class="mb-5" id="speed-chart"></canvas>

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

      <div class="text-center mb-3">
        <a class="btn btn-primary" href="#redo" id="redo"><?php _e('Do it again?'); ?></a>
      </div>

      <script>
      $(function() {

          // This is a workaround because of iPhone users.
          // Instead of URL redirecting, clear session data in the background.
          // This way they don't have to copy-paste the URL link again.
          $('#redo').on('click', { xhr: true }, function(ev) {
              $.get('redo.php', function(res) {
                  location.reload();
              });
          });

          var speedHistogram = <?php echo json_encode($wpm_histogram); ?>;
          var speedBinIndex = <?php echo $wpm_bin_index; ?>;
          var errorHistogram = <?php echo json_encode($error_histogram); ?>;
          var errorBinIndex = <?php echo $error_bin_index; ?>;

          plot('speed-chart', speedHistogram, speedBinIndex, '#3f9', 'Words per minute, higher is better');
          plot('error-chart', errorHistogram, errorBinIndex, '#f93', 'Word error rate, lower is better');

          function plot(elemId, histogram, binIndex, binColor, title) {
              var labels = Object.keys(histogram);
              var values = labels.map(function(bin) { return histogram[bin]; });
              // Display percentange of users instead of the sample size.
              var sumVal = values.reduce(function(acc, v) { return acc + v; }, 0);
              values = values.map(function(v) { return Math.round(100 * v/sumVal); });
              // Highlight user bin and display remaining bars in gray color.
              var colors = Array.apply(null, Array(values.length)).map(function(v) { return '#ccc'; });
              colors[binIndex] = binColor;

              var ctx = document.getElementById(elemId).getContext('2d');
			        var plt = new Chart(ctx, {
				          type: 'bar',
				          data: {
				              labels: labels.map(function(bin) { return bin.split('-').pop(); }),
				              datasets: [{
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
                              scaleLabel: {
                                  display: true,
                                  labelString: '% of participants'
                              }
                          }],
                          xAxes: [{
                              ticks: {
                                  maxRotation: 90,
                                  minRotation: 90,
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
