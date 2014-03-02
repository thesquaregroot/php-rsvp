<?php
    require_once("../include/page_wrapper/rsvp_admin_top.php");
    include("../include/db_admin_functions.php");
    $RSVP_CONFIGURED = $MYSQL_USERNAME and $MYSQL_PASSWORD;
    if ($RSVP_CONFIGURED) {
        session_start();
        // authenticate user
    }
?>
<div id="content">
    <?php
        if (isset($_POST['root_password'])) {
            ?><div><?php
                include("../include/db_setup_functions.php");
                create_database($_POST['root_password']);
            ?></div><?php
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
                        $guests[] = $_POST['guest_name'.$i];
                    }
                    add_party($rsvp_conn, $_POST['nickname'], $guests, $_POST['plus_ones']);
                }
            ?>
            <form method="post">
                <table>
                    <tr><td>Party Nickname:</td><td><input type="text" name="nickname" placeholder="Nickename" /></td><td></td></tr>
                    <tr>
                        <td style="vertical-align: top;">
                            Guest Name(s):
                        </td>
                        <td id="guest_names" style="vertical-align: top;">
                            <input type="hidden" id="guest_count" name="guest_count" value=1 />
                            <input type="text" name="guest_name1" placeholder="Guest Name" required="required"/>
                        </td>
                        <td style="vertical-align: bottom;">
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
            <form method="post">
                <input type="hidden" id="meal_count" name="meal_count" value=1 />
                <table>
                    <tr>
                        <td style="vertical-align: top;">
                            <div>Meal Name:</div>
                            <div>Description:</div>
                        </td>
                        <td style="vertical-align: top;">
                            <span id="meals" style="display: inline-block;">
                                <input type="text" name="meal_name1" placeholder="Meal" required="required"/><br/>
                                <textarea name="meal_description1" placeholder="Description"></textarea>
                            </span>
                            <input type="button" class="add_entry" id="add_meal_button" value="+" />
                        </td>
                    </tr>
                    <tr><td></td><td><input type="submit" /></td></tr>
                </table>
            </form>
            <?php print_meal_table($rsvp_conn); ?>
        </div>
<?php } ?>
    </div>
</div>
<?php
    include("../include/page_wrapper/bottom.php");
?>
