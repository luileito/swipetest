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
        $all_times = time_distribution(LOGS_DIR.'/*.log');
        $user_time = time_distribution(USER_EVENTS_FILE);
        $time_percentile = percentile($all_times, $user_time);

        $all_errors = error_distribution(LOGS_DIR.'/*.log');
        $user_error = error_distribution(USER_EVENTS_FILE);
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

          var timeHistogram = <?php echo json_encode($time_histogram); ?>;
          var timeBinIndex = <?php echo $time_bin_index; ?>;
          var errorHistogram = <?php echo json_encode($error_histogram); ?>;
          var errorBinIndex = <?php echo $error_bin_index; ?>;

          plot('time-chart', timeHistogram, timeBinIndex, '#3f9', 'Average swipe time in seconds, lower is better');
          plot('error-chart', errorHistogram, errorBinIndex, '#f93', 'Average word error rate, lower is better');

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
