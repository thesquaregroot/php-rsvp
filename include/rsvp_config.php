<?php
    $MASTER_PAGE_TITLE = "Your Event Title";
    $HOST_CONTACT_EMAIL = "contact@example.com";

    $INVITATION_HTML = "<p>You are inivited to our special event!</p>";
    $INVITATION_HTML .= "<p>It will happen at an appropriate space-time coordinates.</p>";

    $INVALID_URL_EMAIL_SUBJECT = urlencode("Wedding RSVP - Bad URL");
    $WRONG_PERSON_EMAIL_SUBJECT = urlencode("Wedding RSVP - Wrong Person");
    $ADDITIONAL_GUEST_EMAIL_SUBJECT = urlencode("Wedding RSVP - Additional Guest");

    $FINAL_RSVP_DATE = "9999-12-31";
    $LATE_RSVP_HTML = "<p>Sorry, no additional RSVPs are being accepted at this time.  Additional information can still be found below.</p>";

    $INCLUDE_MEAL_DESCRIPTIONS = false;

    $RESPONSE_NO_HTML = "<p>Sorry you can't make it!  If you change your mind, please email us before August.</p>";
    $RESPONSE_NO_HTML .= "<p>If you find yourself available the day of, feel free to stop by for drinks at the reception anyway!</p>";

    $THANK_YOU_HTML = "<p>&hearts; Thank you for your RSVP!</p>";

    $ADDITIONAL_DETAILS_HTML = "<p>This event may be held on the moon, weather permitting.</p>";
    
    // url for rsvp-ing, key should go at the end
    $BASE_RSVP_URL = 'http://' . gethostbyname(gethostname()) . '/rsvp.php?k=';
    // if using .htaccess to avoid 'rsvp.php?k=' piece
    //$BASE_RSVP_URL = 'http://' . gethostbyname(gethostname()) . '/';

    // switch to false to force users to live out their sessions
    // when set to true (default), a user can access multiple URLs back-to-back
    $ALLOW_CHANGE_OF_PARTY = true;
    
    // confirmation emails
    $ALWAYS_SEND_CONFIRMATION_EMAIL = true;
    // confirmation email headers
    $CONFIRMATION_EMAIL_FROM     = $HOST_CONTACT_EMAIL;
    $CONFIRMATION_EMAIL_REPLY_TO = $HOST_CONTACT_EMAIL;
    $CONFIRMATION_EMAIL_CC       = $HOST_CONTACT_EMAIL;
    $CONFIRMATION_EMAIL_SUBJECT  = "Thank You for Your RSVP!";

    require_once(__DIR__."/rsvp_functions.php");
    function get_confirmation_email_content($conn, $party_id)
    {
        global $BASE_RSVP_URL;
        $content = "<p>Hi " . get_party_names_csv($conn, $party_id) . "!</p>";
        $content .= "<p>Thank you for your RSVP!  We have:</p>";
        $content .= "<p><ul>";
        // get guests
        $stmt = $conn->prepare("SELECT guests.name, CASE WHEN guests.response = 1 THEN CONCAT('attending - ', meals.name) ELSE 'not attending' END FROM guests LEFT OUTER JOIN meals ON guests.meal_id = meals.id WHERE guests.party_id = ? AND guests.name <> ''");
        $stmt->bind_param('i', $party_id);
        $guests = $stmt->execute();
        $stmt->bind_result($guest_name, $guest_status);
        while ($stmt->fetch()) {
            $content .= "<li><b>$guest_name</b> - <i>$guest_status</i></li>";
        }
        $content .= "</ul></p>";
        $stmt->close();
        // add rsvp comment
        $stmt = $conn->prepare("SELECT rsvp_comment FROM parties WHERE id = ?");
        $stmt->bind_param('i', $party_id);
        $party = $stmt->execute();
        $stmt->bind_result($rsvp_comment);
        if ($stmt->fetch()) {
            $content .= "<p>Comment: $rsvp_comment</p>";
        }
        $stmt->close();
        // final text
        $content .= "<p>If anything changes feel free to go back to <a href=\"" . htmlspecialchars($BASE_RSVP_URL . get_url_key($conn, $party_id)) . "\">your link</a> and update the information.</p>";
        $content .= "<p>If you have any questions please reply to this email.</p>";
        $content .= "<p>Thank you! :)</p>";
        return $content;
    }

    // technical details
    $MYSQL_USERNAME = ""; // update after RSVP SETUP
    $MYSQL_PASSWORD = ""; // update after RSVP SETUP
    // update to whatever you like, especially if you may have multiple events
    $MYSQL_DB_NAME = "rsvp";

    $JS_DIR = "/js";
    $CSS_DIR = "/css";
    $QR_DIR = "/qrcode";
    
    $QR_LEVEL = 'L'; // valid values: L, M, Q, H
    $QR_VERSION = 1; // QR code version
    $QR_SIZE = 2; // size of dot in pixels

    $JQUERY_VERSION = "1.11.0";
    $JQUERY_LOCATION = "//ajax.googleapis.com/ajax/libs/jquery/${JQUERY_VERSION}/jquery.min.js";
    //$JQUERY_LOCATION = "/js/jquery.min.js";

    $JQUERY_UI_THEME = "flick";
    $JQUERY_UI_VERSION = "1.10.4";
    $JQUERY_UI_JS_LOCATION = "//ajax.googleapis.com/ajax/libs/jqueryui/${JQUERY_UI_VERSION}/jquery-ui.min.js";
    //$JQUERY_UI_JS_LOCATION = "/js/jquery-ui.min.js";
    $JQUERY_UI_CSS_LOCATION = "//ajax.googleapis.com/ajax/libs/jqueryui/${JQUERY_UI_VERSION}/themes/${JQUERY_UI_THEME}/jquery-ui.css";
    //$JQUERY_UI_CSS_LOCATION = "/css/jquery-ui.min.css";

?>
