<?php
    require_once(__DIR__."/mysql.php");
    require_once(__DIR__."/rsvp_config.php");

    if (!function_exists('password_hash')) {
        function password_verify($password, $hash) {
            return (md5($password) == $hash);
        }
    }

    // functions:
    //
    //  Login/Session:
    //      print_login_screen(error)
    //      authenticate_admin_user(conn, username, password)
    //
    //  Adding Entities:
    //      add_party(conn, nickname, guests[], plus_one_count)
    //      add_meal(conn, name, description)
    //      set_url_key(conn, party_id) -> error
    //      mass_add_keys(conn, keys[]) -> errors[]
    //      randomize_keys(conn) -> error
    //
    //  Editing/Deleting Entities:
    //      update_party(conn, party_id, new_nickname)
    //      update_guest(conn, guest_id, new_name, new_meal_id, attending)
    //      update_meal(conn, meal_id, new_name, new_description)
    //      delete_party(conn, party_id)
    //      delete_guest(conn, guest_id)
    //      delete_meal(conn, meal_id)
    //      delete_url_key(conn, url_key_id)
    //
    //  Emails:
    //      get_email_string(conn, response_type = null) -> email_string
    //
    //  QR Codes:
    //      qrcode(data, filename)

    function print_login_screen($error) {
        require_once(__DIR__."/html_functions.php");
        // print login screen
        ?>
        <div>
        <?php
            if (isset($error)) {
                print_error($error);
            }
        ?>
        <form method=post>
            <table>
                <tr><td>Username:</td><td><input name="username" type="text"/></td></tr>
                <tr><td>Password:</td><td><input name="password" type="password"/></td></tr>
                <tr><td></td><td><input type="submit"/></td></tr>
            </table>
        </form>
        </div>
        <?php
        die();
    }

    function authenticate_admin_user($conn, $username, $password) {
        if ($stmt = $conn->prepare("SELECT id, password FROM admin_users WHERE username = ?")) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->bind_result($id, $hash);
            if ($stmt->fetch()) {
                // check password
                if (password_verify($password, $hash)) {
                    $stmt->close();
                    return $id;
                }
            }
            $stmt->close();
        }
        // error encountered
        return null;
    }

    function add_party($conn, $nickname, $guests /* array */, $plus_one_count)
    {
        if (method_exists($conn, 'begin_transaction')) {
            $conn->begin_transaction();
        } else {
            $conn->autocommit(FALSE);
        }

        $stmt = $conn->prepare("INSERT INTO parties (nickname) VALUES (?)");
        $stmt->bind_param('s', $nickname);
        $stmt->execute();
        if ($conn->error) {
            print_error("Could not create party '" . $nickname . "': " . $conn->error);
            $conn->rollback();
            return;
        }
        $stmt->close();

        $party_id = $conn->insert_id;

        // create url key
        if ($error = set_url_key($conn, $party_id)) {
            print_error("Could not set key for party " . $party_id . ": " . $error);
            $conn->rollback();
            return;
        }

        // add guests
        $stmt = $conn->prepare("INSERT INTO guests (party_id, name) VALUES (?, ?)");
        foreach ($guests as $guest) {
            $stmt->bind_param("is", $party_id, $guest);
            $stmt->execute();
            if ($conn->error) {
                print_error("Could not add guest '" . $guest . "': " . $conn->error);
                $conn->rollback();
                return;
            }
        }
        $stmt->close();

        // add plus ones
        $stmt = $conn->prepare("INSERT INTO guests (party_id, is_plus_one) VALUES (?, 1)");
        $stmt->bind_param('i', $party_id);
        for ($i=0; $i<$plus_one_count; $i++) {
            $stmt->execute();
            // fail on any failures
            if ($conn->error) {
                print_error("Could not add plug ones.");
                $conn->rollback();
                return;
            }
        }
        $stmt->close();

        // all done
        $conn->commit();
    }

    // add meals
    function add_meal($conn, $name, $description) {
        $stmt = $conn->prepare("INSERT INTO meals (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        $stmt->execute();
        if ($conn->error) {
            print_error("Could not create meal '" . $name . "': " . $conn->error);
            return;
        }
        $stmt->close();
    }

    function set_url_key($conn, $party_id) {
        $max_attempts = 5;
        $attempt = 0;
        $key_set = false;
        do {
            if ($result = $conn->query("SELECT value FROM url_keys WHERE party_id is null ORDER BY RAND() LIMIT 1")) {
                if ($result->num_rows == 1) {
                    // found an available random word, use it!
                    $value = $result->fetch_assoc();
                    $url_key = $value['value'];
                    // set party id
                    $stmt = $conn->prepare("UPDATE url_keys SET party_id = ? WHERE value = ?");
                    $stmt->bind_param('is', $party_id, $url_key);
                    $stmt->execute();
                } else {
                    $random_string_length = 8;
                    // no random words available; use a random 'n'-character string
                    $url_key = substr(sha1(microtime()), 0, $random_string_length);
                    $from_table = false;
                    // create new word
                    $stmt = $conn->prepare("INSERT INTO url_keys (value, party_id) VALUES (?, ?)");
                    $stmt->bind_param('si', $url_key, $party_id);
                    $stmt->execute();
                }
                // determine if insert/update was successful
                if ($conn->error) {
                    $key_set = false;
                } else {
                    $key_set = true;
                }
            }
        } while (!$key_set && ($attempt++ < $max_attempts));

        if ($attempt >= $max_attempts) {
            // too many attempts
            return "Max attempts failed for creating a unique key.";
        }
        if ($key_set) {
            // set a key
            // unsest any other associate keys
            $stmt = $conn->prepare("UPDATE url_keys SET party_id = NULL WHERE party_id = ? and value != ? and user_key = 1");
            $stmt->bind_param('is', $party_id, $url_key);
            $stmt->execute();
            if (!$conn->error) {
                // completely delete associated system-generated keys
                $stmt->close();
                $stmt = $conn->prepare("DELETE FROM url_keys WHERE party_id = ? and value != ? and user_key = 0");
                $stmt->bind_param('is', $party_id, $url_key);
                if ($stmt->execute()) {
                    // completely successful
                    return null;
                }
            }
        }
        return $conn->error;
    }

    function mass_add_keys($conn, $keys /* array */) {
        $errors = array();
        foreach ($keys as $key) {
            $stmt = $conn->prepare("INSERT INTO url_keys (value, party_id, user_key) VALUES (?, NULL, 1)");
            $stmt->bind_param('s', $key);
            $stmt->execute();
            if ($error = $conn->error) {
                $errors[] = "($key, $error)";
            }
        }
        // attempted all
        return $errors;
    }

    function randomize_keys($conn) {
        if (method_exists($conn, 'begin_transaction')) {
            $conn->begin_transaction();
        } else {
            $conn->autocommit(FALSE);
        }
        // delete any non-user keys
        $result = $conn->query("DELETE FROM url_keys WHERE user_key = 0");
        if ($error = $conn->error) {
            $conn->rollback();
            return $error;
        }
        // unset any user keys
        $result = $conn->query("UPDATE url_keys SET party_id = NULL WHERE user_key = 1");
        if ($error = $conn->error) {
            $conn->rollback();
            return $error;
        }
        // set a key for each party
        $errors = array();
        $result = $conn->query("SELECT id FROM parties");
        while ($party = $result->fetch_assoc()) {
            if ($error = set_url_key($conn, $party['id'])) {
                $conn->rollback();
                return $error;
            }
        }
        // success
        return null;
    }

    function update_party($conn, $party_id, $new_nickname) {
        $stmt = $conn->prepare("UPDATE parties SET nickname = ? WHERE id = ?");
        $stmt->bind_param('si', $new_nickname, $party_id);
        if ($stmt->execute()) {
            print_success("Party $party_id renamed $new_nickname.");
        } else {
            print_error("Could not updated party $party_id: " . $stmt->error);
        }
    }

    function update_guest($conn, $guest_id, $new_name, $new_meal_id, $attending) {
        $response = $attending;
        // ensure that response is null if not set
        if ($attending === "") {
            $response = NULL;
        }
        $stmt = $conn->prepare("UPDATE guests SET name = ?, meal_id = ?, response = ? WHERE id = ?");
        $stmt->bind_param('siii', $new_name, $new_meal_id, $response, $guest_id);
        if ($stmt->execute()) {
            print_success("Guest $guest_id updated.");
        } else {
            print_error("Could not update guest $guest_id: " . $stmt->error);
        }
    }

    function update_meal($conn, $meal_id, $new_name, $new_description) {
        $stmt = $conn->prepare("UPDATE meals SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param('ssi', $new_name, $new_description, $meal_id);
        if ($stmt->execute()) {
            print_success("Meal $meal_id updated.");
        } else {
            print_error("Could not update meal $meal_id: " . $stmt->error);
        }
    }

    function delete_party($conn, $party_id) {
        $stmt = $conn->prepare("DELETE FROM guests WHERE party_id = ?");
        $stmt->bind_param('i', $party_id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("DELETE FROM parties WHERE id = ?");
            $stmt->bind_param('i', $party_id);
            if ($stmt->execute()) {
                print_success("Deleted party $party_id.");
            } else {
                print_error("Could not delete party, but guests for party $party_id deleted: " . $stmt->error);
            }
        } else {
            print_error("Could not delete party $party_id guests:" . $stmt->error);
        }
    }

    function delete_guest($conn, $guest_id) {
        $stmt = $conn->prepare("DELETE FROM guests WHERE id = ?");
        $stmt->bind_param('i', $guest_id);
        if ($stmt->execute()) {
            print_success("Deleted guest $guest_id.");
        } else {
            print_error("Could not delete guest $guest_id: " . $stmt->error);
        }
    }

    function delete_meal($conn, $meal_id) {
        $stmt = $conn->prepare("DELETE FROM meals WHERE id = ?");
        $stmt->bind_param('i', $meal_id);
        if ($stmt->execute()) {
            print_success("Deleted meal $meal_id.");
        } else {
            print_error("Could not delete meal $meal_id: " . $stmt->error);
        }
    }

    function delete_url_key($conn, $url_key_id) {
        $stmt = $conn->prepare("DELETE FROM url_keys WHERE id = ?");
        $stmt->bind_param('i', $url_key_id);
        if ($stmt->execute()) {
            print_success("Deleted URL key $url_key_id.");
        } else {
            print_error("Could not delete URL key $url_key_id: " . $stmt->error);
        }
    }

    function qrcode($data, $filename) {
        global $QR_DIR;
        global $QR_LEVEL;
        global $QR_VERSION;
        global $QR_SIZE;

        $file_path = $QR_DIR."/".urlencode(urlencode($filename)).".png";
        $abs_file_path = realpath(__DIR__) . "/../www" . $QR_DIR."/".urlencode($filename).".png";

        if (file_exists($abs_file_path)) {
            ?><img src="<?=$file_path?>" /><?php
        } else {
            $qr_cmd = escapeshellcmd("qrencode -l $QR_LEVEL -v $QR_VERSION -s $QR_SIZE -o ".escapeshellarg($abs_file_path)." ".escapeshellarg($data));
            exec($qr_cmd, $output, $return_val);
            if ($return_val == 0) {
                ?><img src="<?=$file_path?>" /><?php
            } else {
                print_error("Could not create QR code.  Please reload to try again.");
            }
        }
    }

    function get_email_string($conn, $response_type = null) {
        // different query by response type
        static $FILTER_BY_RESPONSE_SQL = "SELECT email, response FROM party_emails INNER JOIN (SELECT party_id, MAX(response) response FROM guests GROUP BY party_id) A ON A.party_id = party_emails.party_id WHERE COALESCE(TRIM(email), '') != '' and A.response = %b ORDER BY email;";
        if (is_null($response_type)) {
            // all guests
            $result = $conn->query("SELECT email FROM party_emails WHERE COALESCE(TRIM(email), '') != '' ORDER BY email");
        } else if ($response_type) {
            // guests that replied YES
            $result = $conn->query(sprintf($FILTER_BY_RESPONSE_SQL, 1));
        } else {
            // guests that replied NO
            $result = $conn->query(sprintf($FILTER_BY_RESPONSE_SQL, 0));
        }
        $str = "";
        $separator = "; ";
        // build email string
        while ($email = $result->fetch_assoc()) {
            $str .= $email['email'] . $separator;
        }
        return $str;
    }

?>
