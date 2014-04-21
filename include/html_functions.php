<?php
    //  print_success(message)
    //  print_error(message)
    //
    //  print_meal_table(conn)
    //  print_party_table(conn)

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
                <td><?=$meal['name']?></td>
                <td><?=$meal['description']?></td>
                <td><?=$meal['ordered']?></td>
            </tr>
            <?php
            }
        ?>
        </table>
    <?php
    }

    function print_party_table($conn) {
    ?>
    <table class="data_table" id="party_table" border="1">
        <tr>
            <th>Party # / Nickname</th>
            <th colspan=3>Guests</th>
            <th>Total Attending</th>
            <th>URL Key</th>
        </tr>
        <?php
        $result = $conn->query("SELECT parties.id, nickname, COUNT(guests.id) AS total, SUM(guests.response) AS attending, url_keys.value AS url_key FROM parties"
                                . " INNER JOIN guests ON parties.id = guests.party_id"
                                . " INNER JOIN url_keys ON parties.id = url_keys.party_id"
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
            <td rowspan="<?=$guest_count?>"><?=$party['id']?> / <?=$party['nickname']?></td>
            <td><?=$guest_name?></td>
            <td><?=$meal?></td>
            <td><?=$response?></td>
            <td rowspan="<?=$guest_count?>"><?=$party['attending']?> / <?=$party['total']?></td>
            <td rowspan="<?=$guest_count?>">
                <?=$party['url_key']?>
                <form method="post" action="#guests">
                    <input type="hidden" name="new_key_party_id" value="<?=$party['id']?>"/>
                    <small><small><input type="submit" value="New Key"/></small></small>
                </form>
            </td>
        </tr>
        <?php
                } else {
        ?>
        <tr>
            <td><?=$guest_name?></td>
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
    <?php
    }

    function print_available_keys($conn) {
    ?>
    <table class="data_table" id="url_keys_table" border="1">
        <tr><th>Available Keys</th></tr>
        <?php
        $result = $conn->query("SELECT value FROM url_keys WHERE party_id is null");
        while ($key = $result->fetch_assoc()) {
            ?>
            <tr><td><?=$key['value']?></td></tr>
            <?php
        }
        ?>
    </table>
    <?php
    }

?>
