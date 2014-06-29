<?php
    session_start();
    // immediately exit if session party not established
    if (!isset($_SESSION['party_id'])) {
        die('255');
    }
    $party_id = $_SESSION['party_id'];
    
    // get db connection
    require_once(__DIR__."/../../include/mysql.php");
    require_once(__DIR__."/../../include/rsvp_config.php");

   if (method_exists($rsvp_conn, 'begin_transaction')) {
        $rsvp_conn->begin_transaction();
    } else {
        $rsvp_conn->autocommit(FALSE);
    }

    // get guests
    $stmt = $rsvp_conn->prepare("SELECT id FROM guests WHERE party_id = ?");
    $stmt->bind_param('i', $party_id);
    $stmt->bind_result($guest_id);
    $stmt->execute();
    $guest_ids = array();
    while ($stmt->fetch()) {
        if ($rsvp_conn->error) {
            $rsvp_conn->rollback();
            die('1');
        }
        $guest_ids[] = $guest_id;
    }
    // have guest ids, update each one
    foreach ($guest_ids as $guest_id) {
        if (isset($_POST['guest'.$guest_id])) {
            // coming!
            $meal_id = null;
            if (isset($_POST['guest'.$guest_id.'_meal'])) {
                $meal_id = $_POST['guest'.$guest_id.'_meal'];
            }
            // get name is plus-one
            if (isset($_POST['name_guest'.$guest_id])) {
                // is a plus-one
                $name = htmlspecialchars($_POST['name_guest'.$guest_id]); // be careful with user input....
                $stmt = $rsvp_conn->prepare("UPDATE guests SET response = 1, name = ?, meal_id = ? WHERE id = ?");
                $stmt->bind_param('sii', $name, $meal_id, $guest_id);
                $stmt->execute();
            } else {
                // is a regular guest
                $stmt = $rsvp_conn->prepare("UPDATE guests SET response = 1, meal_id = ? WHERE id = ?");
                $stmt->bind_param('ii', $meal_id, $guest_id);
                $stmt->execute();
            }
        } else {
            // not coming...
            if (isset($_POST['name_guest'.$guest_id])) {
                // plus one
                $name = htmlspecialchars($_POST['name_guest'.$guest_id]); // be careful with user input....
                $stmt = $rsvp_conn->prepare("UPDATE guests SET response = 0, name = ?, meal_id = NULL WHERE id = ?");
                $stmt->bind_param('si', $name, $guest_id);
                $stmt->execute();
            } else {
                // regular guest
                $stmt = $rsvp_conn->prepare("UPDATE guests SET response = 0, meal_id = NULL WHERE id = ?");
                $stmt->bind_param('i', $guest_id);
                $stmt->execute();
            }
        }
        // failure -- exit
        if ($rsvp_conn->error) {
            $rsvp_conn->rollback();
            die('2');
        }
    }
    // update party with rsvp comment (if any)
    if (isset($_POST['rsvp_comment'])) {
        $stmt = $rsvp_conn->prepare("UPDATE parties SET rsvp_comment = ? WHERE id = ?");
        $stmt->bind_param('si', $_POST['rsvp_comment'], $party_id);
        $stmt->execute();
        if ($rsvp_conn->error) {
            $rsvp_conn->rollback();
            die('3');
        }
        $stmt->close();
    }
    // add email address, if set
    if (isset($_POST['email_addr'])) {
        // delete any existing emails
        $stmt = $rsvp_conn->prepare("DELETE FROM party_emails WHERE party_id = ?");
        $stmt->bind_param('i', $party_id);
        $stmt->execute();
        $stmt->close();
        // add new email
        $stmt = $rsvp_conn->prepare("INSERT INTO party_emails (party_id, email) VALUES (?, ?)");
        $addrs = explode(',', $_POST['email_addr']);
        foreach ($addrs as $addr) {
            $address = trim($addr);
            $stmt->bind_param('is', $party_id, $address);
            $stmt->execute();
            if ($rsvp_conn->error) {
                $rsvp_conn->rollback();
                die('4');
            }
        }
        // all set, everything successful
        $sent_email=0;
        if ($ALWAYS_SEND_CONFIRMATION_EMAIL || !$_SESSION['responded']) {
            // get parameters
            $to = $_POST['email_addr'];
            $subject = $CONFIRMATION_EMAIL_SUBJECT;
            // message
            $message = "<html><body>";
            $message .= get_confirmation_email_content($rsvp_conn, $party_id);
            $message .= "</body></html>";
            // headers
            $headers = "From: $CONFIRMATION_EMAIL_FROM\r\n";
            $headers .= "Reply-To: $CONFIRMATION_EMAIL_REPLY_TO\r\n";
            $headers .= "Cc: $CONFIRMATION_EMAIL_CC\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            // send email
            $sent_email = mail($to, $subject, $message, $headers)?1:-1;
        }
    }
    $rsvp_conn->commit();
    $_SESSION['responded'] = true;
    die("0:$sent_email"); // 0:0  - success, no email
                          // 0:1  - success, email success
                          // 0:-1 - success, email failed
?>
