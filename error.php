<?php require_once 'config.php'; ?>
<!doctype html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="css/common.css?v=<?php echo VERSION; ?>" />
    <script type="text/javascript" src="js/vendor/jquery-2.0.2.min.js"></script>
  </head>
  <body class="alert alert-warning">
    <div class="container">
    <?php
    $error = filter_var($_GET['e'], FILTER_SANITIZE_STRING);
    switch ($error) {

    case 'no-touch':
    // Only mobile devices can swipe.
    ?>

            <h2><?php _e('No mobile device detected'); ?></h2>
            <p><?php _e('This study requires a touch-capable device, such as a tablet or mobile phone.'); ?></p>

    <?php
    break;
    case 'ios-chrome':
    // Chrome on iOS has built-in swipe gestures that can't be disabled,
    // so unfortunately this browser can't be tested.
    ?>

            <h2><?php _e('Unsupported browser'); ?></h2>
            <p><?php _e('Please click the button below to copy the study URL and open it with <b>Safari</b> browser:'); ?></p>
            <button class="btn btn-primary mr-2" onclick="copyToClipboard(document.referrer, notifyCopyEvent)"><?php _e('Copy URL'); ?></button>
            <i id="copyNote"><?php _e('Link copied!'); ?></i>

    <?php
    break;
    case 'ios':
    // Safari disables swipe gestures is there's no previous/next URL in `history`,
    // so iOS users can do the study if the URL is entered manually.
    ?>

            <h2><?php _e('iOS note'); ?></h2>
            <p><?php _e('To circumvent a technical issue, please (1) click the button below to copy the study URL, then (2) <b>open a new tab</b> and (3) paste the copied URL.'); ?></p>
            <button class="btn btn-primary mr-2" onclick="copyToClipboard(document.referrer, notifyCopyEvent)"><?php _e('Copy URL'); ?></button>
            <i id="copyNote"><?php _e('Link copied!'); ?></i>

    <?php
    break;
    default:
    // This page appears if the user mangles the URL.
    ?>

            <h2><?php _e('Unknown error'); ?></h2>
            <p><tt>¯\_(ツ)_/¯</tt></p>

    <?php
    break;
    }
    ?>
    </div>
    <script>
    $(function(){
        // We need global functions to enable inline js in onclick attributes.
        window.copyToClipboard = function(str, callback) {
            var el = document.createElement('textarea');
            el.value = str;
            el.setAttribute('readonly', '');
            el.style.position = 'absolute'
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.select();
            el.setSelectionRange(0, 99999);
            document.execCommand('copy');
            if (typeof callback === 'function') callback(el.value);
            document.body.removeChild(el);
        }

        var $copyBtn = $('#copyNote').hide();
        window.notifyCopyEvent = function() {
            $copyBtn.show().delay(3000).fadeOut();
        }
    });
    </script>
  </body>
</html>
