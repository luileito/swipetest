<?php require_once 'config.php'; ?>
<?php
// Get visitor's IP to detect their current country.
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $USER_IP = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $USER_IP = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $USER_IP = $_SERVER['REMOTE_ADDR'];
}

if (!empty($_POST)) {
    // This is the user metadata we collect for later analysis.
    $entry = array(
        'gender' => filter_var($_POST['gender'], FILTER_SANITIZE_STRING),
        'age' => (int) filter_var($_POST['age'], FILTER_SANITIZE_NUMBER_INT),
        'nationality' => filter_var($_POST['nationality'], FILTER_SANITIZE_STRING),
        'familiarity' => filter_var($_POST['familiarity'], FILTER_SANITIZE_STRING),
        'englishLevel' => filter_var($_POST['englishLevel'], FILTER_SANITIZE_STRING),
        'dominantHand' => filter_var($_POST['dominantHand'], FILTER_SANITIZE_STRING),
        'swipeHand' => filter_var($_POST['swipeHand'], FILTER_SANITIZE_STRING),
        'swipeFinger' => filter_var($_POST['swipeFinger'], FILTER_SANITIZE_STRING),
        'screenWidth' => (int) filter_var($_POST['screenWidth'], FILTER_SANITIZE_NUMBER_INT),
        'screenHeight' => (int) filter_var($_POST['screenHeight'], FILTER_SANITIZE_NUMBER_INT),
        'devicePixelRatio' => (float) filter_var($_POST['devicePixelRatio'], FILTER_SANITIZE_NUMBER_FLOAT),
        'maxTouchPoints' => (int) filter_var($_POST['maxTouchPoints'], FILTER_SANITIZE_NUMBER_INT),
        'platform' => filter_var($_POST['platform'], FILTER_SANITIZE_STRING),
        'vendor' => filter_var($_POST['vendor'], FILTER_SANITIZE_STRING),
        'referal' => filter_var($_POST['referal'], FILTER_SANITIZE_URL),
        'language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'],
        'userAgent' => $_SERVER['HTTP_USER_AGENT'],
        'timestamp' => time(),
    );
    // Ensure we have write permissions on the server.
    $bytes = file_put_contents(USER_METADATA_FILE, json_encode($entry).PHP_EOL);
    if ($bytes > 0) {
        // Redirect after HTTP POST, otherwise browsers may complain about resubmitting the form.
        header('Location: index.php');
        exit;
    } else {
        die(_('We have technical problems, sorry. Please try again later.'));
    }
}
?>
<!doctype html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="css/common.css?v=<?php echo VERSION; ?>" />
    <script type="text/javascript" src="js/vendor/jquery-2.0.2.min.js"></script>
    <script type="text/javascript" src="js/vendor/bootstrap.min.js"></script>
  </head>
  <body>
    <div class="container">

      <p class="text-center">
        <img src="img/swipe-test-logo.png" alt="Swipe test logo" class="logo" />
      </p>

      <div class="instructions">
        <p>
        <?php _e('This brief test allows you to test how good you can swipe on your mobile phone and compare with others.'); ?>
        <?php _e('Data is collected and used for scientific purposes only and strictly anonymized.'); ?>
        </p>

        <div class="text-center mb-3">
          <img src="img/swipe-anim.gif" alt="Swipe animation" />
        </div>

        <h4><?php _e('Instructions'); ?></h4>
        <ol class="list-indented list-spaced">
          <li>
            <?php echo sprintf(_('The test will take about %d minutes of your time.'), MAX_EST_MINUTES); ?>
            <?php echo sprintf(_('You will be presented with %d short sentences, one by one.'), MAX_NUM_SENTENCES + NUM_TRIAL_SENTENCES); ?>
            <i>
            <?php echo ngettext(
              'The first sentence is a warm-up sentence to familiarize yourself with the test.',
              sprintf('The first %d sentences are warm-up sentences to familiarize yourself with the test.', NUM_TRIAL_SENTENCES),
              NUM_TRIAL_SENTENCES
            ); ?>
            </i>
            <?php _e('You have to swipe each word on a custom virtual keyboard as fast and as accurately as possible.'); ?>
          </li>
          <li>
            <?php _e('Please be aware that some sentences may contain gibberish text or even offensive words.'); ?>
          </li>
          <li>
            <?php _e('You will see the following color codes as visual feeback:'); ?>
            <br>
            <img src="img/word-colors.png" alt="Color codes" style="max-width:100%;" />
            <ul class="pl-3 list-nospaced">
              <li><?php echo sprintf(_('Successfully entered words are shown in %s color'), '<b style="color:blue">'._('blue').'</b>'); ?></li>
              <li><?php echo sprintf(_('Pending words are shown in %s color'), '<b style="color:gray">'._('gray').'</b>'); ?></li>
              <li><?php echo sprintf(_('The current word you must swipe is shown in %s color'), '<b style="color:black">'._('black').'</b>'); ?></li>
              <li><?php echo sprintf(_('If you swipe wrongly, the current word will turn %s (please retry!)'), '<b style="color:red">'._('red').'</b>'); ?></li>
            </ul>
          </li>
          <li>
            <?php _e('You will get your performance statistics after completing successfully all the sentences.'); ?>
            <?php _e('To get a better estimate of your performance, you can redo the test later; every time there will be different sentences.'); ?>
          </li>
          <li>
            <?php echo sprintf(_('Some mobile browsers have technical limitations, such as Firefox Focus and Chrome on iOS. If you find any bug or issue, please <a href="%s">inform us</a>.'), '#feedback'); ?>
          </li>
        </ol>
      </div><!-- .instructions -->

      <hr/>

      <h4 class="text-muted"><?php _e('Please fill in this form in order to proceed'); ?></h4>
      <p><?php _e('We need to know a little bit about you, for statistical purposes.'); ?></p>

      <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <div class="form-group row">
          <label for="gender" class="col-4 col-form-label"><?php _e('Gender'); ?></label>
          <div class="col-8">
            <select class="form-control" id="gender" name="gender" required>
              <option value=""></option>
              <option value="Male"><?php _e('Male'); ?></option>
              <option value="Female"><?php _e('Female'); ?></option>
              <option value="Other"><?php _e('Other'); ?></option>
              <option value="NA"><?php _e('Prefer not to say'); ?></option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label for="age" class="col-4 col-form-label"><?php _e('Age'); ?></label>
          <div class="col-8">
            <input id="age" name="age" type="number" class="form-control" min="18" max="99" required />
          </div>
        </div>
        <div class="form-group row">
          <label for="nationality" class="col-4 col-form-label"><?php _e('Nationality'); ?></label>
          <div class="col-8">
            <?php
            $languages = array(
                "AF" => _('Afghanistan'),
                "AX" => _('Åland Islands'),
                "AL" => _('Albania'),
                "DZ" => _('Algeria'),
                "AS" => _('American Samoa'),
                "AD" => _('Andorra'),
                "AO" => _('Angola'),
                "AI" => _('Anguilla'),
                "AQ" => _('Antarctica'),
                "AG" => _('Antigua and Barbuda'),
                "AR" => _('Argentina'),
                "AM" => _('Armenia'),
                "AW" => _('Aruba'),
                "AU" => _('Australia'),
                "AT" => _('Austria'),
                "AZ" => _('Azerbaijan'),
                "BS" => _('Bahamas'),
                "BH" => _('Bahrain'),
                "BD" => _('Bangladesh'),
                "BB" => _('Barbados'),
                "BY" => _('Belarus'),
                "BE" => _('Belgium'),
                "BZ" => _('Belize'),
                "BJ" => _('Benin'),
                "BM" => _('Bermuda'),
                "BT" => _('Bhutan'),
                "BO" => _('Bolivia, Plurinational State of'),
                "BQ" => _('Bonaire, Sint Eustatius and Saba'),
                "BA" => _('Bosnia and Herzegovina'),
                "BW" => _('Botswana'),
                "BV" => _('Bouvet Island'),
                "BR" => _('Brazil'),
                "IO" => _('British Indian Ocean Territory'),
                "BN" => _('Brunei Darussalam'),
                "BG" => _('Bulgaria'),
                "BF" => _('Burkina Faso'),
                "BI" => _('Burundi'),
                "KH" => _('Cambodia'),
                "CM" => _('Cameroon'),
                "CA" => _('Canada'),
                "CV" => _('Cape Verde'),
                "KY" => _('Cayman Islands'),
                "CF" => _('Central African Republic'),
                "TD" => _('Chad'),
                "CL" => _('Chile'),
                "CN" => _('China'),
                "CX" => _('Christmas Island'),
                "CC" => _('Cocos (Keeling) Islands'),
                "CO" => _('Colombia'),
                "KM" => _('Comoros'),
                "CG" => _('Congo'),
                "CD" => _('Congo, the Democratic Republic of the'),
                "CK" => _('Cook Islands'),
                "CR" => _('Costa Rica'),
                "CI" => _('Côte d\'Ivoire'),
                "HR" => _('Croatia'),
                "CU" => _('Cuba'),
                "CW" => _('Curaçao'),
                "CY" => _('Cyprus'),
                "CZ" => _('Czech Republic'),
                "DK" => _('Denmark'),
                "DJ" => _('Djibouti'),
                "DM" => _('Dominica'),
                "DO" => _('Dominican Republic'),
                "EC" => _('Ecuador'),
                "EG" => _('Egypt'),
                "SV" => _('El Salvador'),
                "GQ" => _('Equatorial Guinea'),
                "ER" => _('Eritrea'),
                "EE" => _('Estonia'),
                "ET" => _('Ethiopia'),
                "FK" => _('Falkland Islands (Malvinas)'),
                "FO" => _('Faroe Islands'),
                "FJ" => _('Fiji'),
                "FI" => _('Finland'),
                "FR" => _('France'),
                "GF" => _('French Guiana'),
                "PF" => _('French Polynesia'),
                "TF" => _('French Southern Territories'),
                "GA" => _('Gabon'),
                "GM" => _('Gambia'),
                "GE" => _('Georgia'),
                "DE" => _('Germany'),
                "GH" => _('Ghana'),
                "GI" => _('Gibraltar'),
                "GR" => _('Greece'),
                "GL" => _('Greenland'),
                "GD" => _('Grenada'),
                "GP" => _('Guadeloupe'),
                "GU" => _('Guam'),
                "GT" => _('Guatemala'),
                "GG" => _('Guernsey'),
                "GN" => _('Guinea'),
                "GW" => _('Guinea-Bissau'),
                "GY" => _('Guyana'),
                "HT" => _('Haiti'),
                "HM" => _('Heard Island and McDonald Islands'),
                "VA" => _('Holy See (Vatican City State)'),
                "HN" => _('Honduras'),
                "HK" => _('Hong Kong'),
                "HU" => _('Hungary'),
                "IS" => _('Iceland'),
                "IN" => _('India'),
                "ID" => _('Indonesia'),
                "IR" => _('Iran, Islamic Republic of'),
                "IQ" => _('Iraq'),
                "IE" => _('Ireland'),
                "IM" => _('Isle of Man'),
                "IL" => _('Israel'),
                "IT" => _('Italy'),
                "JM" => _('Jamaica'),
                "JP" => _('Japan'),
                "JE" => _('Jersey'),
                "JO" => _('Jordan'),
                "KZ" => _('Kazakhstan'),
                "KE" => _('Kenya'),
                "KI" => _('Kiribati'),
                "KP" => _('Korea, Democratic People\'s Republic of'),
                "KR" => _('Korea, Republic of'),
                "KW" => _('Kuwait'),
                "KG" => _('Kyrgyzstan'),
                "LA" => _('Lao People\'s Democratic Republic'),
                "LV" => _('Latvia'),
                "LB" => _('Lebanon'),
                "LS" => _('Lesotho'),
                "LR" => _('Liberia'),
                "LY" => _('Libya'),
                "LI" => _('Liechtenstein'),
                "LT" => _('Lithuania'),
                "LU" => _('Luxembourg'),
                "MO" => _('Macao'),
                "MK" => _('Macedonia, the former Yugoslav Republic of'),
                "MG" => _('Madagascar'),
                "MW" => _('Malawi'),
                "MY" => _('Malaysia'),
                "MV" => _('Maldives'),
                "ML" => _('Mali'),
                "MT" => _('Malta'),
                "MH" => _('Marshall Islands'),
                "MQ" => _('Martinique'),
                "MR" => _('Mauritania'),
                "MU" => _('Mauritius'),
                "YT" => _('Mayotte'),
                "MX" => _('Mexico'),
                "FM" => _('Micronesia, Federated States of'),
                "MD" => _('Moldova, Republic of'),
                "MC" => _('Monaco'),
                "MN" => _('Mongolia'),
                "ME" => _('Montenegro'),
                "MS" => _('Montserrat'),
                "MA" => _('Morocco'),
                "MZ" => _('Mozambique'),
                "MM" => _('Myanmar'),
                "NA" => _('Namibia'),
                "NR" => _('Nauru'),
                "NP" => _('Nepal'),
                "NL" => _('Netherlands'),
                "NC" => _('New Caledonia'),
                "NZ" => _('New Zealand'),
                "NI" => _('Nicaragua'),
                "NE" => _('Niger'),
                "NG" => _('Nigeria'),
                "NU" => _('Niue'),
                "NF" => _('Norfolk Island'),
                "MP" => _('Northern Mariana Islands'),
                "NO" => _('Norway'),
                "OM" => _('Oman'),
                "PK" => _('Pakistan'),
                "PW" => _('Palau'),
                "PS" => _('Palestinian Territory, Occupied'),
                "PA" => _('Panama'),
                "PG" => _('Papua New Guinea'),
                "PY" => _('Paraguay'),
                "PE" => _('Peru'),
                "PH" => _('Philippines'),
                "PN" => _('Pitcairn'),
                "PL" => _('Poland'),
                "PT" => _('Portugal'),
                "PR" => _('Puerto Rico'),
                "QA" => _('Qatar'),
                "RE" => _('Réunion'),
                "RO" => _('Romania'),
                "RU" => _('Russian Federation'),
                "RW" => _('Rwanda'),
                "BL" => _('Saint Barthélemy'),
                "SH" => _('Saint Helena, Ascension and Tristan da Cunha'),
                "KN" => _('Saint Kitts and Nevis'),
                "LC" => _('Saint Lucia'),
                "MF" => _('Saint Martin (French part)'),
                "PM" => _('Saint Pierre and Miquelon'),
                "VC" => _('Saint Vincent and the Grenadines'),
                "WS" => _('Samoa'),
                "SM" => _('San Marino'),
                "ST" => _('Sao Tome and Principe'),
                "SA" => _('Saudi Arabia'),
                "SN" => _('Senegal'),
                "RS" => _('Serbia'),
                "SC" => _('Seychelles'),
                "SL" => _('Sierra Leone'),
                "SG" => _('Singapore'),
                "SX" => _('Sint Maarten (Dutch part)'),
                "SK" => _('Slovakia'),
                "SI" => _('Slovenia'),
                "SB" => _('Solomon Islands'),
                "SO" => _('Somalia'),
                "ZA" => _('South Africa'),
                "GS" => _('South Georgia and the South Sandwich Islands'),
                "SS" => _('South Sudan'),
                "ES" => _('Spain'),
                "LK" => _('Sri Lanka'),
                "SD" => _('Sudan'),
                "SR" => _('Suriname'),
                "SJ" => _('Svalbard and Jan Mayen'),
                "SZ" => _('Swaziland'),
                "SE" => _('Sweden'),
                "CH" => _('Switzerland'),
                "SY" => _('Syrian Arab Republic'),
                "TW" => _('Taiwan, Province of China'),
                "TJ" => _('Tajikistan'),
                "TZ" => _('Tanzania, United Republic of'),
                "TH" => _('Thailand'),
                "TL" => _('Timor-Leste'),
                "TG" => _('Togo'),
                "TK" => _('Tokelau'),
                "TO" => _('Tonga'),
                "TT" => _('Trinidad and Tobago'),
                "TN" => _('Tunisia'),
                "TR" => _('Turkey'),
                "TM" => _('Turkmenistan'),
                "TC" => _('Turks and Caicos Islands'),
                "TV" => _('Tuvalu'),
                "UG" => _('Uganda'),
                "UA" => _('Ukraine'),
                "AE" => _('United Arab Emirates'),
                "GB" => _('United Kingdom'),
                "US" => _('United States'),
                "UM" => _('United States Minor Outlying Islands'),
                "UY" => _('Uruguay'),
                "UZ" => _('Uzbekistan'),
                "VU" => _('Vanuatu'),
                "VE" => _('Venezuela, Bolivarian Republic of'),
                "VN" => _('Viet Nam'),
                "VG" => _('Virgin Islands, British'),
                "VI" => _('Virgin Islands, U.S.'),
                "WF" => _('Wallis and Futuna'),
                "EH" => _('Western Sahara'),
                "YE" => _('Yemen'),
                "ZM" => _('Zambia'),
                "ZW" => _('Zimbabwe'),
            );
            $user_countries = array();
            // Display user country, based on their IP.
            if (!empty($USER_IP)) {
                // XXX: Ensure that fwrappers are allowed in PHP, otherwise use cURL here.
                $res = file_get_contents('http://www.geoplugin.net/json.gp?ip='.$USER_IP);
                if ($res) {
                    // Country codes are already uppercased.
                    $key = json_decode($res)->geoplugin_countryCode;
                    if (isset($languages[$key])) {
                        $user_countries[$key] = $languages[$key];
                    }
                }
            }
            // Display preferred languages.
            $results = array();
            $locales = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($locales as $lang) {
                list($target, $score) = explode(';', $lang);
                list($lang, $country) = explode('-', $target);
                $results[$country] = $score;
            }
            foreach (array_keys($results) as $code) {
                $key = strtoupper($code);
                if (isset($languages[$key])) {
                    $user_countries[$key] = $languages[$key];
                }
            }
            ?>
            <select class="form-control" id="nationality" name="nationality" required>
              <option value=""></option>
              <optgroup label="<?php _e('Autodetected'); ?>">
              <?php foreach ($user_countries as $code => $name): ?>
                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
              <?php endforeach; ?>
              </optgroup>
              <optgroup label="<?php _e('All countries'); ?>">
              <?php foreach ($languages as $code => $name): ?>
                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
              <?php endforeach; ?>
              </optgroup>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="familiarity" class=""><?php _e('How often do you swipe on your keyboard?'); ?></label>
          <div class="">
            <select class="form-control" id="familiarity" name="familiarity" required>
              <option value=""></option>
              <option value="Everyday"><?php _e('Everyday'); ?></option>
              <option value="Often"><?php _e('Often'); ?></option>
              <option value="Sometimes"><?php _e('Sometimes'); ?></option>
              <option value="Rarely"><?php _e('Rarely'); ?></option>
              <option value="Never"><?php _e('Never'); ?></option>
            </select>
            <div class="moreinfo mt-2">
              <p class="alert alert-warning">
                <?php echo sprintf(_('Oh! If you have never swiped before, please <a href="%s">watch this video</a> first.'),
                  'https://www.youtube.com/watch?v=7cTOl6MzWd4'); ?>
              </p>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="dominantHand" class=""><?php _e('What is your dominant hand?'); ?></label>
          <div class="">
            <select class="form-control" id="dominantHand" name="dominantHand" required>
              <option value=""></option>
              <option value="Right"><?php _e('Right'); ?></option>
              <option value="Left"><?php _e('Left'); ?></option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="swipeHand" class=""><?php _e('Which hand do you use to swipe on your keyboard?'); ?></label>
          <div class="">
            <select class="form-control" id="swipeHand" name="swipeHand" required>
              <option value=""></option>
              <option value="Right"><?php _e('Right'); ?></option>
              <option value="Left"><?php _e('Left'); ?></option>
              <option value="Both"><?php _e('Both'); ?></option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="swipeFinger" class=""><?php _e('Which finger do you use to swipe on your keyboard?'); ?></label>
          <div class="">
            <select class="form-control" id="swipeFinger" name="swipeFinger" required>
              <option value=""></option>
              <option value="Index"><?php _e('Index'); ?></option>
              <option value="Thumb"><?php _e('Thumb'); ?></option>
              <option value="Other"><?php _e('Other'); ?></option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="englishLevel" class=""><?php _e('What is your knowledge of English language?'); ?></label>
          <div class="">
            <select class="form-control" id="englishLevel" name="englishLevel" required>
              <option value=""></option>
              <option value="Beginner"><?php _e('Beginner'); ?></option>
              <option value="Intermediate"><?php _e('Intermediate'); ?></option>
              <option value="Advanced"><?php _e('Advanced'); ?></option>
              <option value="Native"><?php _e('Native'); ?></option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="agree" required />
            <label class="form-check-label" for="agree">
              <?php echo sprintf(_('I have read and accept the <a href="%s">terms and conditions</a> of the study'), 'terms.php'); ?>
            </label>
          </div>
        </div>

        <div class="form-group text-center">
          <button type="submit" class="btn btn-primary"><?php _e('Start test'); ?></button>
        </div>

        <script>
        $(function() {

            $('a').each(function() {
                $(this).attr('target', '_blank');
            });

            $('a[href=#feedback]').on('click', function(ev) {
                ev.preventDefault();
                $('#feedback').on('shown.bs.modal', function(e) {
                    $(this).find('textarea').focus();
                }).modal('show');
            });

            $('label[for="agree"] a').on('click', function(ev) {
                ev.preventDefault();
                $('#terms').modal('show');
            });

            $('select#familiarity').next().hide();
            $('select#familiarity').on('change', function(ev) {
                if (this.value === 'Never') {
                    $(this).next().show();
                } else {
                    $(this).next().hide();
                }
            });

            // Get device info.
            var props = {
                screenWidth: screen.width,
                screenHeight: screen.height,
                devicePixelRatio: devicePixelRatio,
                maxTouchPoints: navigator.maxTouchPoints,
                platform: navigator.platform,
                vendor: navigator.vendor,
                referal: document.referrer,
            };
            for (var p in props) {
                $('<input>').attr({ type: 'hidden', name: p, value: props[p] }).appendTo('form');
            }

        });
        </script>
      </form>
    </div><!-- .container -->

    <div class="modal fade" id="feedback" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form method="post" action="report.php">
          <div class="modal-header">
            <h5 class="modal-title"><?php _e('Feedback'); ?></h5>
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p><?php _e('Please describe the issue you have found.'); ?></p>
            <textarea name="comment" rows="4" style="width:100%" required></textarea>
          </div>
          <div class="modal-footer">
            <input type="submit" class="btn btn-primary" value="<?php _e('Submit'); ?>" />
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e('Close'); ?></button>
          </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="terms" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><?php _e('Terms and conditions'); ?></h5>
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <?php include 'terms.php'; ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e('Close'); ?></button>
          </div>
        </div>
      </div>
    </div>

  </body>
</html>
