<?php
    require_once("../include/page_wrapper/rsvp_top.php");
    require_once("../include/rsvp_functions.php");
?>
<div id="content">
    <?php
        session_start();
        // get user data
        if (!isset($_SESSION['party_id']) || $ALLOW_CHANGE_OF_PARTY) {
            // no session intialized
            if (!isset($_GET['k'])) {
                // no key passed, ignore user
                ?><div>Sorry, I'm not sure who you are.  Please try you link again or contact your host at <a href="mailto:<?=$HOST_CONTACT_EMAIL?>?Subject=<?=$INVALID_URL_EMAIL_SUBJECT?>"><?=$HOST_CONTACT_EMAIL?></a></div><?php
                // close content div
                ?></div><?php
                // clean up stuff included from top
                include("../include/page_wrapper/bottom.php");
                die();
            } else {
                // key passed, force this key
                $_SESSION['party_id'] = get_party_id($rsvp_conn, $_GET['k']);
                $_SESSION['responded'] = get_rsvp_status($rsvp_conn, $_SESSION['party_id']);
            }
        }
    ?>
    <!-- Confirm Identity -->
    <div id="check_identity">
        <strong>Hello, <?php print_party_names($rsvp_conn, $_SESSION['party_id']); ?>!</strong>
        <small>
            (<a id="wrong_person_link" href="#">Not you?</a>)
            <div id="wrong_person_instructions" style="display: none;">
                Oh no!  Send us an email at <a href="mailto:<?=$HOST_CONTACT_EMAIL?>?Subject=<?=$WRONG_PERSON_EMAIL_SUBJECT?>"><?=$HOST_CONTACT_EMAIL?></a> and we'll figure it out.
            </div>
        </small>
    </div>
    <div>
        <!-- Actual Invitation -->
        <div id="invitation">
            <?=$INVITATION_HTML?>
        </div>
        <!-- Thank you -->
        <?php
            if ($_SESSION['responded']) {
                ?><div id="thank_you"><?php
            } else {
                ?><div id="thank_you" style="display: none;"><?php
            }
        ?>
            <?=$THANK_YOU_HTML?>
            <small><a href="#" id="rsvp_again_link">(need to make a change?)</a></small>
        </div>
        <!-- RSVP -->
        <?php
        if ($_SESSION['responded']) {
            ?><div id="rsvp_box" style="display: none;"><?php
        } else {
            ?><div id="rsvp_box">
                <p><input id="rsvp_button" type="button" value="Please RSVP Here" /></p><?php
        }
        ?>
            <div id="rsvp_status" style="display: none;">
                <hr/>
                <p>We have your party listed as:</p>
                <ul id="party_list">
                <?php
                    $names = get_party_names($rsvp_conn, $_SESSION['party_id']);
                    $plus_ones = get_plus_ones($rsvp_conn, $_SESSION['party_id']);
                    
                    foreach ($names as $name) {
                        echo "<li>$name</li>";
                    }
                    if ($plus_ones > 0) {
                        echo "<li>+$plus_ones</li>";
                    }
                ?>
                </ul>
                <small>
                    <a href="#" id="missing_persons_link">Missing someone?</a>
                    <div id="missing_persons_instructions" style="display: none;">Please email us at <a href="mailto:<?=$HOST_CONTACT_EMAIL?>?Subject=<?=$ADDITIONAL_GUEST_EMAIL_SUBJECT?>"><?=$HOST_CONTACT_EMAIL?></a> to request an additional guest.</div>
                </small>
                <p>Will anyone be able to make it?</p>
                <div class="accordion">
                    <h3>Yes! :-D</h3>
                    <div id="rsvp_yes">
                        <form id="confirm_yes">
                        <?php
                            // pre-emptively get the set of meals
                            $meals = get_meals($rsvp_conn);
                            // get party members
                            $stmt = $rsvp_conn->prepare("SELECT id, name, is_plus_one FROM guests WHERE party_id = ? ORDER BY is_plus_one ASC");
                            $stmt->bind_param('s', $_SESSION['party_id']);
                            $stmt->execute();
                            $stmt->bind_result($id, $name, $is_plus_one);
                            while ($stmt->fetch()) {
                                // check box
                                ?><input type="checkbox" name="guest<?=$id?>" id="guest<?=$id?>" /><?php
                                // name or text box
                                if ($is_plus_one) {
                                    ?><input type="text" name="name_guest<?=$id?>" id="name_guest<?=$id?>" placeholder="+1 (full name)" /><br/><?php
                                } else {
                                    ?><label for="guest<?=$id?>"><?=$name?></label><br/><?php
                                }
                                // box with meal options
                                ?><div id="guest<?=$id?>_options" style="display: none;"><?php
                                foreach ($meals as $meal) {
                                    $description = addcslashes(htmlspecialchars($meal['description']), "\"");
                                    ?>
                                    <input type="radio" id="guest<?=$id?>_meal<?=$meal['id']?>" name="guest<?=$id?>_meal" title="<?=$description?>" value="<?=$meal['id']?>" />
                                    <label for="guest<?=$id?>_meal<?=$meal['id']?>" title="<?=$description?>"><?=$meal['name']?></label>
                                    <?php
                                }
                                ?></div><?php
                            }
                        ?>
                            <p style="text-align: center;">Also, so that we can send you any updates, please provide your email address:</p>
                            <span style="float: right;">
                                <small>You can enter multiple addresses separated by commas.</small><br/>
                                <input id="email_addr" name="email_addr" />
                                <input type="submit" value="Confirm" />
                            </span>
                        </form>
                    </div>
                    <h3>No... :-(</h3>
                    <div id="rsvp_no">
                        <?=$RESPONSE_NO_HTML?>
                        <p style="margin-top: 2em; text-align: center;">Also, so that we can send you any updates, please provide your email address:</p>
                        <span style="float: right;">
                            <form id="confirm_no">
                                <small>You can enter multiple addresses separated by commas.</small><br/>
                                <input id="email_addr" name="email_addr" />
                                <input type="submit" value="Confirm" />
                            </form>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- /RSVP -->
    </div>
    <!-- Additional Details -->
    <div id="additional_details">
        <strong>Additional Details:</strong>
        <?=$ADDITIONAL_DETAILS_HTML?>
    </div>
</div>
<?php
    include("../include/page_wrapper/bottom.php");
?>
