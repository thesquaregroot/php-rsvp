<?php
    require_once("../include/rsvp_top.php");
?>
<div id="content">
    <!-- Confirm Identity -->
    <div id="check_identity">
        <strong>Hello, Tester!</strong> <small>(<a id="wrong_person_link" href="#">Not you?</a>)</small>
        <div id="wrong_person_instructions" style="display: none;">
            Oh no!  Send us an email at <a href="mailto:<?=$HOST_CONTACT_EMAIL?>?Subject=<?=$WRONG_PERSON_EMAIL_SUBJECT?>"><?=$HOST_CONTACT_EMAIL?></a> and we'll figure it out.
        </div>
    </div>
    <div>
        <!-- Actual Invitation -->
        <div id="invitation">
            <?=$INVITATION_HTML?>
        </div>
        <!-- RSVP -->
        <div id="rsvp_box">
            <p><input id="rsvp_button" type="button" value="Please RSVP Here" /></p>
            <div id="rsvp_status" style="display: none;">
                <hr/>
                <p>We have your party listed as:</p>
                <ul id="party_list">
                    <li>Person Name 1</li>
                    <li>Person Name 2</li>
                    <li>+1</li>
                </ul>
                <small><a href="#" id="missing_persons_link">Missing someone?</a></small>
                <div id="missing_persons_instructions" style="display: none;">Please email us at <a href="mailto:<?=$HOST_CONTACT_EMAIL?>?Subject=<?=$ADDITIONAL_GUEST_EMAIL_SUBJECT?>"><?=$HOST_CONTACT_EMAIL?></a> to request an additional guest.</div>
                <p>Will anyone be able to make it?</p>
                <div class="accordion">
                    <h3>Yes! :-D</h3>
                    <div id="rsvp_yes">
                        <form id="confirm_yes">
                            <input type="checkbox" id="person1" /><label for="person1">Person Name 1</label><br/>
                            <div id="person1_options" style="display: none;">
                                <input type="radio" id="person1_meal1" name="person1_meal" value="1" /><label for="person1_meal1">Meal 1</label>
                                <input type="radio" id="person1_meal2" name="person1_meal" value="2" /><label for="person1_meal2">Meal 2</label>
                                <input type="radio" id="person1_meal3" name="person1_meal" value="3" /><label for="person1_meal3">Meal 3</label>
                                <input type="radio" id="person1_meal4" name="person1_meal" value="0" /><label for="person1_meal4">No Meal</label>
                            </div>
                            <input type="checkbox" id="person2" /><label for="person2">Person Name 2</label><br/>
                            <div id="person2_options" style="display: none;">
                                <input type="radio" id="person2_meal1" name="person2_meal" value="1" /><label for="person2_meal1">Meal 1</label>
                                <input type="radio" id="person2_meal2" name="person2_meal" value="2" /><label for="person2_meal2">Meal 2</label>
                                <input type="radio" id="person2_meal3" name="person2_meal" value="3" /><label for="person2_meal3">Meal 3</label>
                                <input type="radio" id="person2_meal4" name="person2_meal" value="0" /><label for="person2_meal4">No Meal</label>
                            </div>
                            <input type="checkbox" id="plus1" /><input type="text" id="name_plus1" placeholder="+1 (full name)" /><br/>
                            <div id="plus1_options" style="display: none;">
                                <input type="radio" id="plus1_meal1" name="plus1_meal" value="1" /><label for="plus1_meal1">Meal 1</label>
                                <input type="radio" id="plus1_meal2" name="plus1_meal" value="2" /><label for="plus1_meal2">Meal 2</label>
                                <input type="radio" id="plus1_meal3" name="plus1_meal" value="3" /><label for="plus1_meal3">Meal 3</label>
                                <input type="radio" id="plus1_meal4" name="plus1_meal" value="0" /><label for="plus1_meal4">No Meal</label>
                            </div>
                            <p style="text-align: center;">Also, so that we can send you any updates, please provide your email address:</p>
                            <span style="float: right;">
                                <input type="email" id="email_yes" required="required" />
                                <input type="submit" value="Confirm" />
                            </span>
                        </form>
                    </div>
                    <h3>No... :-(</h3>
                    <div id="rsvp_no">
                        <?=$NO_RESPONSE_HTML?>
                        <p style="margin-top: 2em; text-align: center;">Also, so that we can send you any updates, please provide your email address:</p>
                        <span style="float: right;">
                            <form id="confirm_no">
                                <input type="email" id="email_no" required="required" />
                                <input type="submit" value="Confirm" />
                            </form>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- /RSVP -->
        <!-- Thank you -->
        <div id="thank_you" style="display: none;">
            <?=$THANK_YOU_HTML?>
        </div>
    </div>
    <!-- Additional Details -->
    <div id="additional_details">
        <strong>Additional Details:</strong>
        <?=$ADDITIONAL_DETAILS_HTML?>
    </div>
</div>
<?php
    include("../include/rsvp_bottom.php");
?>
