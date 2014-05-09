<?php
    $MASTER_PAGE_TITLE = "Your Event Title";
    $HOST_CONTACT_EMAIL = "contact@example.com";

    $INVITATION_HTML = "<p>You are inivited to our special event!</p>";
    $INVITATION_HTML .= "<p>It will happen at an appropriate space-time coordinates.</p>";

    $INVALID_URL_EMAIL_SUBJECT = urlencode("Wedding RSVP - Bad URL");
    $WRONG_PERSON_EMAIL_SUBJECT = urlencode("Wedding RSVP - Wrong Person");
    $ADDITIONAL_GUEST_EMAIL_SUBJECT = urlencode("Wedding RSVP - Additional Guest");

    $INCLUDE_MEAL_DESCRIPTIONS = false;

    $RESPONSE_NO_HTML = "<p>Sorry you can't make it!  If you change your mind, please email us before August.</p>";
    $RESPONSE_NO_HTML .= "<p>If you find yourself available the day of, feel free to stop by for drinks at the reception anyway!</p>";

    $THANK_YOU_HTML = "<p>&hearts; Thank you for your RSVP!</p>";

    $ADDITIONAL_DETAILS_HTML = "<p>This event may be held on the moon, weather permitting.</p>";
    
    // confirmation emails
    $ALWAYS_SEND_CONFIRMATION_EMAIL = true;
    // confirmation email headers
    $CONFIRMATION_EMAIL_FROM     = $HOST_CONTACT_EMAIL;
    $CONFIRMATION_EMAIL_REPLY_TO = $HOST_CONTACT_EMAIL;
    $CONFIRMATION_EMAIL_CC       = $HOST_CONTACT_EMAIL;
    $CONFIRMATION_EMAIL_SUBJECT  = "Thank You for Your RSVP!";
    // body
    $CONFIRMATION_EMAIL_HTML = "<p>Thank you for your RSVP!</p>";
    $CONFIRMATION_EMAIL_HTML .= "<p>If anything changes, feel free to go back to your link and update the information.</p>";
    $CONFIRMATION_EMAIL_HTML .= "<p>If you have any questions please reply to this email.</p>";
    $CONFIRMATION_EMAIL_HTML .= "<p>Additional Details:</p>";
    $CONFIRMATION_EMAIL_HTML .= $ADDITIONAL_DETAILS_HTML;
    
    // url for rsvp-ing, key should go at the end
    $BASE_RSVP_URL = 'http://' . gethostbyname(gethostname()) . "/rsvp.php?k=";

    // technical details
    $MYSQL_USERNAME = ""; // update after RSVP SETUP
    $MYSQL_PASSWORD = ""; // update after RSVP SETUP
    // update to whatever you like, especially if you may have multiple events
    $MYSQL_DB_NAME = "rsvp";

    $JS_DIR = "/js";
    $CSS_DIR = "/css";
    $QR_DIR = "/qrcode";

    //$JQUERY_LOCATION = "//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js";
    $JQUERY_LOCATION = "/js/jquery.min.js";
    $JQUERY_UI_THEME = "flick";

    //$JQUERY_UI_JS_LOCATION = "//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js";
    //$JQUERY_UI_CSS_LOCATION = "//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/${JQUERY_UI_THEME}/jquery-ui.css";
    $JQUERY_UI_JS_LOCATION = "/js/jquery-ui.min.js";
    $JQUERY_UI_CSS_LOCATION = "/css/jquery-ui.min.css";

?>
