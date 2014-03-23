<?php
    require_once(__DIR__."/mysql.php");

    // functions:
    //
    //  Login/Session:
    //      print_login_screen(error)
    //      authenticate_admin_user(conn, username, password)
    //
    //  Adding Entities:
    //      add_party(conn, nickname, guests[], plus_one_count)
    //      add_meal(conn, name, description)

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
        $conn->autocommit(FALSE);
        $stmt = $conn->prepare("INSERT INTO parties (nickname, plus_ones) VALUES (?, ?)");
        $stmt->bind_param('si', $nickname, $plus_one_count);
        $stmt->execute();
        if ($conn->error) {
            print_error("Could not create party '" . $nickname . "': " . $conn->error);
            return;
        }
        $stmt->close();

        $party_id = $conn->insert_id;
        
        // add guests
        $stmt = $conn->prepare("INSERT INTO guests (party_id, name) VALUES (?, ?)");
        foreach ($guests as $guest) {
            $stmt->bind_param("is", $party_id, $guest);
            $stmt->execute();
            if ($conn->error) {
                print_error("Could not add guest '" . $guest . "': " . $conn->error);
                return;
            }
        }
        $stmt->close();
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
?>
