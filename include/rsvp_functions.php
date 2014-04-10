<?php
    require_once(__DIR__."/mysql.php");

    // functions:
    //
    //  Login/Session:
    //      get_party_id(conn, key)
    //
    //  Display:
    //      get_party_names(conn, party_id)
    //      get_meals(conn)
    //      print_party_names(conn, party_id)

    function get_party_id($conn, $key) {
        if ($stmt = $conn->prepare("SELECT party_id FROM url_keys WHERE value = ?")) {
            $stmt->bind_param('s', $key);
            $stmt->execute();
            $stmt->bind_result($id);
            if ($stmt->fetch()) {
                return $id;
            }
        }
        return null;
    }

    function get_party_names($conn, $party_id) {
        if ($stmt = $conn->prepare("SELECT name FROM guests WHERE party_id = ?")) {
            $stmt->bind_param('i', $party_id);
            $stmt->execute();
            $stmt->bind_result($name);
            $names = array();
            while ($stmt->fetch()) {
                $names[] = $name;
            }
            if (count($names) > 0) {
                return $names;
            }
        }
        return null;
    }

    function get_meals($conn) {
        $result = $conn->query("SELECT id, name, description FROM meals");
        $arr = array();
        while ($meal = $result->fetch_assoc()) {
            $arr[] = $meal;
        }
        return $arr;
    }
    
    function get_plus_ones($conn, $party_id) {
        if ($stmt = $conn->prepare("SELECT plus_ones FROM parties WHERE id = ?")) {
            $stmt->bind_param('i', $party_id);
            $stmt->execute();
            $stmt->bind_result($plus_ones);
            if ($stmt->fetch()) {
                return $plus_ones;
            }
        }
        return null;
    }

    function print_party_names($conn, $party_id) {
        if ($names = get_party_names($conn, $party_id)) {
            // print differently based on count
            $size = count($names);
            if ($size == 1) {
                echo $names[0];
            } else if ($size == 2) {
                echo "{$names[0]} and {$names[1]}";
            } else {
                $names[$size-1] = 'and ' . $names[$size-1];
                echo implode(', ', $names);
            }
        }
    }
?>
