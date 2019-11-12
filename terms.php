<?php require_once 'config.php'; ?>
<?php if ($_SERVER['SCRIPT_FILENAME'] === __FILE__): ?>
<!doctype html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="css/common.css?v=<?php echo VERSION; ?>" />
  </head>
  <body>
    <div class="container">
      <img src="img/swipe-test-logo.png" alt="Swipe test logo" class="logo" />
<?php endif; ?>


      <p>
        <?php _e('This research is carried out in Aalto University.'); ?>
        <?php _e('The purpose of the study is to conduct scientific research on people\'s typing behavior.'); ?>
      </p>

      <h4><?php _e('Data collection'); ?></h4>
      <p>
        <?php _e('We collect the following data:'); ?>
        <ol class="list-indented">
          <li><?php _e('Keyboard events and their associated information, such as key codes and touch position.'); ?></li>
          <li><?php _e('Information about the mobile device and location.'); ?></li>
          <li><?php _e('Demographic data as provided by the participant.'); ?></li>
        </ol>
      </p>

      <h4><?php _e('Anonymity, secure storage, confidentiality'); ?></h4>
      <p>
        <?php _e('We collect data for research purposes, which can be made publicly available as part of our scientific work. Therefore, we follow European privacy regulation (GDPR) in strictly anonymizing the published data.'); ?>
        <?php _e('When taking the typing test, we store IP addresses which we use in order to infer for example the country and city of a user. This personally identifiable information is confidential. It will be stored for up to five years and will not be transferred outside EU/EEA area.'); ?>
      </p>

      <h4><?php _e('Voluntary participation'); ?></h4>
      <p>
        <?php _e('Participation in the study is voluntary.'); ?>
        <?php _e('You have the right to discontinue participation at any time without obligation to disclose any specific reasons.'); ?>
      </p>

      <h4><?php _e('Rights of the study participant'); ?></h4>
      <p>
        <?php _e('It may be necessary to deviate from the rights of the data subject, as defined in GDPR and national legislation, if the study is being carried out for the performance of the public interest and the exercising of the participant’s rights would likely prevent reaching the aim of the research study.'); ?>
        <?php _e('The following rights can be deviated from:'); ?>
        <ol class="list-indented">
          <li><?php _e('The right to access data'); ?></li>
          <li><?php _e('The right to rectify information'); ?></li>
          <li><?php _e('The right to restrict processing'); ?></li>
          <li><?php _e('The right to be forgotten'); ?></li>
        </ol>
        <?php _e('If, however, it is possible to achieve the aims of the study and the achievement of the purpose is not greatly hindered, Aalto University will actualize your rights as defined in the GDPR.'); ?>
        <?php _e('The extent of your rights is related to the legal basis of processing of your personal data, national legislation and exercising your rights requires proof of identity.'); ?>
      </p>

      <p>
        <i><?php _e('We thank you for your contribution to research efforts.'); ?></i>
      </p>

      <h4><?php _e('Funding information and contact'); ?></h4>
      <p>
        <?php _e('This study is funded by the European Research Council under the Horizon 2020 program (ERC Starting Grant contract ID 637991) and the Finnish Center for Artificial Intelligence (FCAI).'); ?>
      </p>
      <p>
        <b>Prof. Antti Oulasvirta</b>
        <br/>
        <b>Dr. Luis A. Leiva</b>
        <br/>
        <b>Dr. Sunjun Kim</b>
        <br/>
        firstname.lastname@aalto.fi
      </p>


<?php if ($_SERVER['SCRIPT_FILENAME'] === __FILE__): ?>
      <p>
        <a href="index.php">&laquo; <?php _e('Back'); ?></a>
      </p>
    </div><!-- .container -->
  </body>
</html>
<?php endif; ?>
