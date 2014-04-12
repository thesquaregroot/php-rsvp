<?php
    function print_success($message) {
        ?><div class="success">SUCCESS: <?=$message?></div><?php
    }

    function print_error($message) {
        ?><div class="error">ERROR: <?=$message?></div><?php
    }

    function print_meal_table($conn) {
    ?>
    <table class="data_table" id="meal_table" border="1">
        <tr><th>Meal Name</th><th>Description</th></tr>
        <?php
            $result = $conn->query("SELECT id, name, description FROM meals;");
            while ($meal = $result->fetch_assoc()) {
            ?>
            <tr id="meal<?=$meal['id']?>">
                <td><?=$meal['name']?></td>
                <td><?=$meal['description']?></td>
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
            <th>Guests</th>
            <th>Plus Ones</th>
            <th>Total Invited</th>
            <th>URL Key</th>
        </tr>
        <?php
        $result = $conn->query("SELECT parties.id, nickname, guest.guest_list, COALESCE(plus_one.count, 0) AS plus_ones, guest.count + COALESCE(plus_one.count, 0) AS total, url_keys.value AS url_key"
                                    . " FROM parties INNER JOIN (SELECT party_id, GROUP_CONCAT(name SEPARATOR ', ') AS guest_list, COUNT(name) AS count FROM guests WHERE is_plus_one = 0 GROUP BY party_id) guest ON parties.id = guest.party_id"
                                    . " LEFT OUTER JOIN (SELECT party_id, COUNT(*) AS count FROM guests WHERE is_plus_one = 1 GROUP BY party_id) plus_one ON parties.id = plus_one.party_id"
                                    . " INNER JOIN url_keys ON parties.id = url_keys.party_id"
                                    . " GROUP BY parties.id;");
        while ($party = $result->fetch_assoc()) {
        ?>
        <tr id="party<?=$party['id']?>">
            <td><?=$party['id']?> / <?=$party['nickname']?></td>
            <td><?=$party['guest_list']?></td>
            <td><?=$party['plus_ones']?></td>
            <td><?=$party['total']?></td>
            <td>
                <?=$party['url_key']?>
                <form method="post" action="#guests">
                    <input type="hidden" name="new_key_party_id" value="<?=$party['id']?>"/>
                    <small><small><input type="submit" value="New Key"/></small></small>
                </form>
            </td>
        </tr>
        <?php
        }
        $result = $conn->query("SELECT SUM(NOT(is_plus_one)) guest_count, SUM(is_plus_one) AS plus_one_count FROM guests");
        if ($totals = $result->fetch_assoc()) {
            $total = $totals['guest_count'] + $totals['plus_one_count'];
            ?>
            <tr>
                <th>Totals</th>
                <td><?=$totals['guest_count']?></td>
                <td><?=$totals['plus_one_count']?></td>
                <td><?=$total?></td>
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
