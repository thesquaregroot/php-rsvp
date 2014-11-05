<?php
    require_once(__DIR__.'/rsvp_config.php');

    //  print_success(message)
    //  print_error(message)
    //
    //  print_meal_table(conn)
    //  print_party_table(conn)
    //  print_available_keys_table(conn)
    //  print_rsvp_urls_table(conn)

    function print_success($message) {
        ?><div class="success">SUCCESS: <?=$message?></div><?php
    }

    function print_error($message) {
        ?><div class="error">ERROR: <?=$message?></div><?php
    }

    function print_meal_table($conn) {
    ?>
    <table class="data_table" id="meal_table" border="1">
        <tr><th>Meal Name</th><th>Description</th><th>Total Requested</th></tr>
        <?php
            $result = $conn->query("SELECT meals.id, meals.name, meals.description, COUNT(meal_id) AS ordered FROM meals"
                                    . " LEFT OUTER JOIN guests ON guests.meal_id = meals.id"
                                    . " GROUP BY meals.id;");
            while ($meal = $result->fetch_assoc()) {
            ?>
            <tr id="meal<?=$meal['id']?>">
                <td>
                    <?=$meal['name']?>
                    <small style="float: right;">
                        <button value="<?=$meal['id']?>" class="edit_button edit_meal">edit</button>
                        <form method="post" action="#meals" class="button_form"><button class="delete_button" name="delete_meal" value="<?=$meal['id']?>">delete</button></form>
                    </small>
                </td>
                <td><?=$meal['description']?></td>
                <td><?=$meal['ordered']?></td>
            </tr>
            <?php
            }
        ?>
    </table>
    <!-- edit party dialog -->
    <div id="edit_meal_dialog" class="dialog_form">
        <form method="post" action="#meals">
            <fieldset>
                <input type="hidden" name="meal_id"/>
                <div>
                    <label for="new_meal_name">Name:</label>
                    <input type="text" id="new_meal_name" name="new_meal_name"/>
                </div>
                <div>
                    <label for="new_description">Description:</label>
                    <textarea id="new_description" name="new_description"></textarea>
                </div>
                <input type="submit" value="Save"/>
            </fieldset>
        </form>
    </div>
    <?php
    }

    function print_party_table($conn) {
    ?>
    <table class="data_table" id="party_table" border="1">
        <tr>
            <th>Party # / Nickname</th>
            <th colspan=3>Guests</th>
            <th>Total Attending</th>
            <th>Comment</th>
            <th>URL Key</th>
        </tr>
        <?php
        $result = $conn->query("SELECT parties.id, nickname, COUNT(guests.id) AS total, SUM(guests.response) AS attending, url_keys.value AS url_key, rsvp_comment FROM parties"
                                . " INNER JOIN guests ON parties.id = guests.party_id"
                                . " LEFT OUTER JOIN url_keys ON parties.id = url_keys.party_id"
                                . " GROUP BY parties.id");
        while ($party = $result->fetch_assoc()) {
            $stmt = $conn->prepare("SELECT guests.id, guests.name, response, meals.name, is_plus_one FROM guests"
                                    . " LEFT OUTER JOIN meals ON guests.meal_id = meals.id WHERE guests.party_id = ?"
                                    . " ORDER BY is_plus_one, guests.name");
            $stmt->bind_param('i', $party['id']);
            $stmt->execute();
            $stmt->store_result();
            $guest_count = $stmt->num_rows;
            $stmt->bind_result($guest_id, $guest_name, $guest_response, $meal, $plus_one);
            $new = true;
            while ($stmt->fetch()) {
                if ($plus_one) {
                    if (strlen($guest_name) == 0) {
                        // plus one without name (supply something)
                        $guest_name = "(plus-one)";
                    } else {
                        $guest_name .= " (plus-one)";
                    }
                }
                if ($guest_response === null) {
                    $response = "<span style=\"color: gray\">-</span>";
                } else {
                    if ($guest_response) {
                        // coming -- green
                        $response = "<span style=\"color: green;\">Y</span>";
                    } else {
                        // not coming -- red
                        $response = "<span style=\"color: red;\">N</span>";
                    }
                }
                if ($new) {
                    $new = false;
        ?>
        <tr id="party<?=$party['id']?>">
            <td rowspan="<?=$guest_count?>">
                <?=$party['id']?> / <?=$party['nickname']?>
                <small style="float: right;">
                    <button value="<?=$party['id']?>" class="edit_button edit_party">edit</button>
                    <form method="post" action="#guests" class="button_form"><button class="delete_button" name="delete_party" value="<?=$party['id']?>">delete</button></form>
                </small>
            </td>
            <td>
                <?=$guest_name?>
                <small style="float: right;">
                    <button value="<?=$guest_id?>" class="edit_button edit_guest">edit</button>
                    <form method="post" action="#guests" class="button_form"><button class="delete_button" name="delete_guest" value="<?=$guest_id?>">delete</button></form>
                </small>
            </td>
            <td><?=$meal?></td>
            <td><?=$response?></td>
            <td rowspan="<?=$guest_count?>"><?=$party['attending']?> / <?=$party['total']?></td>
            <td rowspan="<?=$guest_count?>"><?=$party['rsvp_comment']?></td>
            <td rowspan="<?=$guest_count?>">
                <?=$party['url_key']?>
                <form method="post" action="#guests" class="button_form">
                    <input type="hidden" name="new_key_party_id" value="<?=$party['id']?>"/><br/>
                    <small><small><input name="new_url_key" type="submit" value="New Key"/></small></small>
                </form>
            </td>
        </tr>
        <?php
                } else {
        ?>
        <tr>
            <td>
                <?=$guest_name?>
                <small style="float: right;">
                    <button value="<?=$guest_id?>" class="edit_button edit_guest">edit</button>
                    <form method="post" action="#guests" class="button_form"><button class="delete_button" name="delete_guest" value="<?=$guest_id?>">delete</button></form>
                </small>
            </td>
            <td><?=$meal?></td>
            <td><?=$response?></td>
        </tr>
        <?php
                }
            }
            $stmt->free_result();
            $stmt->close();
        }
        $result = $conn->query("SELECT SUM(NOT(is_plus_one)) guest_count, SUM(is_plus_one) AS plus_one_count, SUM(response) AS attending FROM guests");
        if ($totals = $result->fetch_assoc()) {
            $total = $totals['guest_count'] + $totals['plus_one_count'];
            ?>
            <tr>
                <th>Totals</th>
                <td><?=$totals['guest_count']?></td>
                <td><?=$totals['plus_one_count']?></td>
                <td><?=$totals['attending']?></td>
                <td><?=$totals['attending']?> / <?=$total?></td>
                <td>
                    <form method="post" action="#guests">
                        <input type="hidden" name="randomize_keys" value="1"/>
                        <small><small><input type="submit" value="Randomize"/></small></small>
                    </form>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
    <!-- edit party dialog -->
    <div id="edit_party_dialog" class="dialog_form">
        <form method="post" action="#guests">
            <fieldset>
                <input type="hidden" name="party_id"/>
                <div>
                    <label for="new_nickname">Nickname:</label>
                    <input type="text" id="new_nickname" name="new_nickname"/>
                </div>
                <input type="submit" value="Save"/>
            </fieldset>
        </form>
    </div>
    <!-- edit guest dialog -->
    <div id="edit_guest_dialog" class="dialog_form">
        <form method="post" action="#guests">
            <fieldset>
                <input type="hidden" name="party_id"/>
                <div>
                    <label for="new_name">Name:</label>
                    <input type="text" id="new_name" name="new_name"/>
                </div>
                <div>
                    <label for="attending">Attending:</label>
                    <select id="attending" name="attending">
                        <option value="">[Blank]</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div>
                    <label for="new_meal">Meal:</label>
                    <select id="new_meal" name="new_meal">
                        <option value="">[Blank]</option>
                    <?php
                        $result = $conn->query("SELECT id, name FROM meals;");
                        while ($meal = $result->fetch_assoc()) {
                        ?>
                            <option value="<?=$meal['id']?>"><?=$meal['name']?></option>
                        <?php
                        }
                    ?>
                    </select>
                </div>
                <input type="submit" value="Save"/>
            </fieldset>
        </form>
    </div>
    <?php
    }

    function print_available_keys_table($conn) {
    ?>
    <table class="data_table" id="url_keys_table" border="1">
        <tr><th>Available Keys</th></tr>
        <?php
        $result = $conn->query("SELECT id, value FROM url_keys WHERE party_id is null");
        while ($key = $result->fetch_assoc()) {
            ?>
            <tr>
                <td>
                    <?=$key['value']?>
                    <small style="float: right;">
                        <form method="post" action="#keys" class="button_form"><button class="delete_button" name="delete_url_key" value="<?=$key['id']?>">delete</button></form>
                    </small>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
    }

    function print_rsvp_urls_table($conn) {
        global $BASE_RSVP_URL;
    ?>
    <table id="rsvp_urls_table" border=1>
    <?php
        $result = $conn->query("SELECT GROUP_CONCAT(COALESCE(guests.name, '+1') ORDER BY guests.name SEPARATOR '; ') AS guests, GROUP_CONCAT(DISTINCT party_emails.email ORDER BY party_emails.email SEPARATOR '; ') AS emails, url_keys.value AS url_key"
                                . " FROM parties INNER JOIN guests ON guests.party_id = parties.id"
                                . " LEFT OUTER JOIN party_emails ON party_emails.party_id = parties.id"
                                . " INNER JOIN url_keys ON url_keys.party_id = parties.id"
                                . " GROUP BY parties.id");
        $has_guests = false;
        while ($party = $result->fetch_assoc()) {
            $has_guests = true;
            $url = $BASE_RSVP_URL . urlencode($party['url_key']);
            ?>
            <tr><td><?=$party['guests']?></td><td><?=$party['emails']?></td></tr>
            <tr>
                <td colspan=2 style="text-align: center;">
                    <?=$url?><br/>
                    <?php qrcode($url, $party['url_key']); ?>
                </td>
            </tr>
            <?php
        }
        if (!$has_guests) {
            ?>Add some guests to view party URLs.<?php
        }
    ?>
    </table>
    <?php
    }
?>
