<?php
    require_once("../include/page_wrapper/rsvp_admin_top.php");
    include("../include/admin_functions.php");
    include("../include/session_functions.php");
    // determine whether mysql is setup
    $RSVP_CONFIGURED = isset($MYSQL_USERNAME) && $MYSQL_USERNAME && isset($MYSQL_PASSWORD) && $MYSQL_PASSWORD;
    start_session();
?>
<div id="content">
    <?php
        // handle logout
        if (isset($_POST['logout'])) {
                // logging out
                end_session();
                print_success("You have logged out.");
        }
        // handle log in
        if ($RSVP_CONFIGURED && !logged_in()) {
            if (isset($_POST['username']) && isset($_POST['password'])) {
                // authenticate user
                if ($id = authenticate_admin_user($rsvp_conn, $_POST['username'], $_POST['password'])) {
                    $_SESSION['id'] = $id;
                    $_SESSION['username'] = $_POST['username'];
                } else {
                    print_login_screen("Sorry, please try again.");
                    // done
                }
            } else {
                print_login_screen(null);
                // done
            }
        }
        // handle setup
        if (isset($_POST['root_password'])) {
            ?><div><?php
                include("../include/db_setup_functions.php");
                create_database($_POST['root_password'],
                                $_POST['mysql_username'],
                                $_POST['mysql_password1'],
                                $_POST['admin_username'],
                                $_POST['admin_password1']);
            ?></div><?php
        }
    ?>
    <div>
        <?php
            if (logged_in()) {
                // hello and logout link
                ?>
                <form method="post">
                    <div style="overflow: auto;">
                        Hello, <?=$_SESSION['username']?>.
                        <span style="float: right;">
                            <input type="hidden" name="logout"/>
                            <input type="submit" value="log out"/>
                        </span>
                    </div>
                </form>
                <?php
            }
        ?>
        <div class="accordion">
<?php if (!$RSVP_CONFIGURED) { ?>
            <h3>Setup RSVP</h3>
            <div>
                <form method="post" id="setup_form">
                    <p><strong>WARNING:</strong> This step will fail if MySQL is not already installed and running.</p>
                    <table>
                        <tr><td>MySQL Root Password:</td><td><input type="password" name="root_password" required="required" /></td></tr>
                        <tr><td>MySQL User Name:</td><td><input type="text" name="mysql_username" required="required" value="rsvp_user" /></td></tr>
                        <tr><td>MySQL User Password:</td><td><input type="password" name="mysql_password1" required="required" /></td></tr>
                        <tr><td>MySQL User Password (confirm):</td><td><input type="password" name="mysql_password2" required="required" /><span id="matching_mysql_passwords_error" class="error" style="display: none;">The passwords must match.</span></td></tr>
                        <tr><td>Admin User Name:</td><td><input type="text" name="admin_username" required="required" value="admin" /></td></tr>
                        <tr><td>Admin User Password:</td><td><input type="password" name="admin_password1" required="required" /></td></tr>
                        <tr><td>Admin User Password (confirm):</td><td><input type="password" name="admin_password2" required="required" /><span id="matching_admin_passwords_error" class="error" style="display: none;">The passwords must match.</span></td></tr>
                        <tr><td></td><td><input name="setup_submit" type="submit" /></td></tr>
                    </table>
                </form>
            </div>
<?php } else { ?>
            <h3>Add/Edit Parties/Guests</h3>
            <div>
                <?php
                    // handle post party
                    if (isset($_POST['nickname'])) {
                        $guests = array();
                        for ($i=1; $i<=$_POST['guest_count']; $i++) {
                            if (isset($_POST['guest_name'.$i]) && $_POST['guest_name'.$i]) {
                                $guests[] = $_POST['guest_name'.$i];
                            }
                        }
                        if (count($guests) > 0) {
                            add_party($rsvp_conn, $_POST['nickname'], $guests, $_POST['plus_ones']);
                        }
                    }
                    // handle new key
                    if (isset($_POST['new_key_party_id'])) {
                        if ($error = set_url_key($rsvp_conn, $_POST['new_key_party_id'])) {
                            print_error($error);
                        }
                    }
                    // randomize keys
                    if (isset($_POST['randomize_keys'])) {
                        randomize_keys($rsvp_conn);
                    }
                ?>
                <form method="post" action="#guests">
                    <table>
                        <tr><td>Party Nickname:</td><td><input type="text" name="nickname" placeholder="Nickname" /></td></tr>
                        <tr>
                            <td style="vertical-align: top;">
                                Guest Name(s):
                            </td>
                            <td style="vertical-align: top;">
                                <div id="guest_names" style="display: inline-block;">
                                    <input type="hidden" id="guest_count" name="guest_count" value=1 />
                                    <input type="text" name="guest_name1" placeholder="Guest Name" required="required"/>
                                </div>
                                <input id="add_guest_button" type="button" value="+" class="add_entry" />
                            </td>
                        </tr>
                        <tr><td>Plus Ones:</td><td><input type="text" class="spinner" name="plus_ones" value="0" size=2 /></td></tr>
                        <tr><td></td><td><input type="submit" /></td></tr>
                    </table>
                </form>
                <?php print_party_table($rsvp_conn); ?>
            </div>
            <h3>Add/Edit Meal Options</h3>
            <div>
                <?php
                    // handle post meals
                    if (isset($_POST['meal_name1'])) {
                        for ($i=1; $i<=$_POST['meal_count']; $i++) {
                            if (isset($_POST['meal_name' . $i])) {
                                add_meal($rsvp_conn, $_POST['meal_name'.$i], $_POST['meal_description'.$i]);
                            }
                        }
                    }
                ?>
                <form method="post" action="#meals">
                    <input type="hidden" id="meal_count" name="meal_count" value=1 />
                    <div id="meals" style="display: inline-block;">
                        <input type="text" name="meal_name1" placeholder="Meal" required="required"/><br/>
                        <textarea name="meal_description1" placeholder="Description"></textarea>
                    </div>
                    <input type="button" class="add_entry" id="add_meal_button" value="+" /><br/>
                    <input type="submit" />
                </form>
                <?php print_meal_table($rsvp_conn); ?>
            </div>
            <h3>Manage URL Keys</h3>
            <div>
                <?php
                    if (isset($_POST['mass_url_keys'])) {
                        $keys = array_map('trim', preg_split('/\s+/', $_POST['mass_url_keys']));
                        $errors = mass_add_keys($rsvp_conn, $keys);
                        foreach ($errors as $error) {
                            print_error($error);
                        }
                    }
                ?>
                <form method="post" action="#keys">
                    <textarea name="mass_url_keys" style="height: 120px;" placeholder="Put keys on separate lines."/></textarea><br/>
                    <input type="submit"/>
                </form>
                <?php print_available_keys($rsvp_conn); ?>
            </div>
            <h3>View RSVP URLs</h3>
            <div>
                <?php print_rsvp_urls_table($rsvp_conn); ?>
            </div>
            <!--h3>Contact Guests</h3>
            <div>
                Not yet implemented...
            </div-->
<?php } ?>
        </div>
    </div>
</div>
<?php
    include("../include/page_wrapper/bottom.php");
?>
