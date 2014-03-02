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
                    ?><tr id="meal<?=$meal['id']?>"><td><?=$meal['name']?></td><td><?=$meal['description']?></td></tr><?php
                }
            ?>
        </table>
    <?php
    }

    function print_party_table($conn) {
    ?>
        <table class="data_table" id="party_table" border="1">
            <tr><th>Party # / Nickname</th><th>Guests</th><th>Plus Ones</th><th>Total</th></tr>
            <?php
                $result = $conn->query("SELECT parties.id, nickname, GROUP_CONCAT(guests.name SEPARATOR ', ') AS guests, plus_ones, COUNT(guests.name) + plus_ones AS total"
                                            . " FROM parties INNER JOIN guests ON parties.id = guests.party_id"
                                            . " GROUP BY parties.id;");
                while ($party = $result->fetch_assoc()) {
                    ?><tr id="party<?=$party['id']?>"><td><?=$party['id']?> / <?=$party['nickname']?></td><td><?=$party['guests']?></td><td><?=$party['plus_ones']?></td><td><?=$party['total']?></td></tr><?php
                }
                $result = $conn->query("SELECT A.guest_count, B.plus_one_count"
                                        . " FROM (SELECT COUNT(*) AS guest_count FROM guests) AS A"
                                        . " JOIN (SELECT SUM(plus_ones) AS plus_one_count FROM parties) AS B;");
                if ($totals = $result->fetch_assoc()) {
                    $total = $totals['guest_count'] + $totals['plus_one_count'];
                    ?><tr><th>Totals</th><td><?=$totals['guest_count']?></td><td><?=$totals['plus_one_count']?></td><td><?=$total?></td></tr><?php
                }
            ?>
        </table>
    <?php
    }
?>
