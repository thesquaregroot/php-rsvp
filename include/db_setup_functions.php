<?php
    require_once(__DIR__."/html_functions.php");
    require_once(__DIR__."/rsvp_config.php");

    function create_tables($conn) {
        // admin users
        $conn->query("CREATE TABLE admin_users ("
                    . " id INT AUTO_INCREMENT,"
                    . " username VARCHAR(255) NOT NULL,"
                    . " password CHAR(60) NOT NULL,"
                    . " PRIMARY KEY (id)"
                    . ");");
        if ($conn->error) {
            return $conn->error;
        }

        // admin users
        $conn->query("CREATE TABLE parties ("
                    . " id INT AUTO_INCREMENT,"
                    . " nickname VARCHAR(255) NULL,"
                    . " plus_ones INT DEFAULT 0,"
                    . " PRIMARY KEY (id)"
                    . ");");
        if ($conn->error) {
            return $conn->error;
        }
        
        // guests
        $conn->query("CREATE TABLE guests ("
                    . " id INT AUTO_INCREMENT,"
                    . " party_id INT NOT NULL,"
                    . " name VARCHAR(255) NOT NULL,"
                    . " meal_id INT NULL,"
                    . " is_plus_one BOOL NOT NULL DEFAULT 0,"
                    . " PRIMARY KEY (id)"
                    . ");");
        if ($conn->error) {
            return $conn->error;
        }

        // emails
        $conn->query("CREATE TABLE party_emails ("
                    . " party_id INT NOT NULL,"
                    . " email VARCHAR(255) NOT NULL"
                    . ");");
        if ($conn->error) {
            return $conn->error;
        }
        // index for joins with guests
        $conn->query("CREATE INDEX idx_party_emails_party_id ON party_emails (party_id);");
        if ($conn->error) {
            return $conn->error;
        }

        // meals
        $conn->query("CREATE TABLE meals ("
                    . " id INT AUTO_INCREMENT,"
                    . " name VARCHAR(255) NOT NULL,"
                    . " description TEXT NOT NULL,"
                    . " PRIMARY KEY (id)"
                    . ");");
        if ($conn->error) {
            return $conn->error;
        }
    }

    // The security of this method could probably be improved.
    //   This resulted from the lack of support for prepared statements/placeholders
    //   for CREATE and GRANT statements.
    // However, the threat of SQL injection is likely minimal for admin setup.
    function create_mysql_user($conn, $username, $password, $db) {
        $conn->autocommit(FALSE);
        $clean_user = $conn->real_escape_string($username);
        $clean_pass = $conn->real_escape_string($password);
        if ($conn->query("CREATE USER '" . $clean_user . "'@'localhost' IDENTIFIED BY '". $clean_pass ."';")) {
            // grant permissions
            $clean_db = $conn->real_escape_string($db);
            if ($conn->query("GRANT SELECT, UPDATE, INSERT, DELETE ON ". $clean_db .".* TO '". $clean_user ."'@'localhost';")) {
                $conn->commit();
                $conn->autocommit(TRUE);
                return "";
            }
        }
        return $conn->error;
    }

    function create_admin_user($conn, $username, $password) {
        if ($pw_hash = password_hash($password, PASSWORD_BCRYPT)) {
            if ($stmt = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?);")) {
                if ($stmt->bind_param('ss', $username, $pw_hash)) {
                    // successfully bound parameters
                    if ($stmt->execute()) {
                        // success
                        $stmt->close();
                        return "";
                    }
                }
                $error = $stmt->error;
                $stmt->close();
                return $error;
            } else {
                return $conn->error;
            }
        } else {
            // could not hash password
            return "Failure hashing password.";
        }
    }

    function create_database($root_password, $mysql_user, $mysql_pass, $admin_user, $admin_pass) {
        global $MYSQL_DB_NAME;
        $mysql_root = new mysqli("localhost", "root", $root_password);
        if (!$mysql_root->connect_errno) {
            // successful connection
            if ($mysql_root->query("CREATE DATABASE " . $MYSQL_DB_NAME)) {
                $mysql_root->select_db($MYSQL_DB_NAME);
                // create tables                
                if ($error = create_tables($mysql_root)) {
                    print_error("Could not create rsvp tables: " . $error);
                } else {
                    // create mysql user
                    if ($error = create_mysql_user($mysql_root, $mysql_user, $mysql_pass, $MYSQL_DB_NAME)) {
                        print_error("Could not create rsvp user: " . $error);
                    } else {
                        // create admin user
                        if ($error = create_admin_user($mysql_root, $admin_user, $admin_pass)) {
                            print_error("Failure creating admin user: " . $error);
                        } else {
                            // all successful!
                            print_success("Database, mysql user, and admin user created successfully.  Please update rsvp_config.php and refresh the page.");
                            // refresh page button
                            ?><a href="/rsvp_admin.php"><input type="button" value="Refresh Page"></a><?php
                        }
                    }
                }
            } else {
                print_error("Could not create new database: " . $mysql_root->error);
            }
        } else {
            // could not create connection
            print_error("Could not connect as root: " . $mysql_root->connect_error);
        }
    }

?>
