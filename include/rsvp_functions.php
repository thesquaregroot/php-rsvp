<?php
    // functions:
    //
    //  Login/Session:
    //      get_party_id(conn, key)
    //      get_rsvp_status(conn, party_id)
    //
    //  Display:
    //      get_meals(conn)
    //      get_plus_ones(conn, party_id)
    //      get_party_names(conn, party_id)
    //      get_party_names_csv(conn, party_id)
    //      print_party_names(conn, party_id)
    //      get_party_names(conn, party_id)

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

    function get_rsvp_status($conn, $party_id) {
        if ($stmt = $conn->prepare("SELECT COUNT(*) FROM guests WHERE party_id = ? AND response is null")) {
            $stmt->bind_param('i', $party_id);
            $stmt->execute();
            $stmt->bind_result($count);
            if ($stmt->fetch() && $count == 0) {
                // there are no guests without a response
                return true;
            }
        }
        // error or there are guests without a response
        return false;
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
        if ($stmt = $conn->prepare("SELECT SUM(is_plus_one) FROM guests WHERE party_id = ?")) {
            $stmt->bind_param('i', $party_id);
            $stmt->execute();
            $stmt->bind_result($plus_ones);
            if ($stmt->fetch()) {
                return $plus_ones;
            }
        }
        return null;
    }

    function get_party_names($conn, $party_id) {
        if ($stmt = $conn->prepare("SELECT name FROM guests WHERE is_plus_one = 0 AND party_id = ?")) {
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

    function get_party_names_csv($conn, $party_id) {
        if ($names = get_party_names($conn, $party_id)) {
            // print differently based on count
            $size = count($names);
            if ($size == 1) {
                return $names[0];
            } else if ($size == 2) {
                return "{$names[0]} and {$names[1]}";
            } else {
                $names[$size-1] = 'and ' . $names[$size-1];
                return implode(', ', $names);
            }
        }
    }

    function print_party_names($conn, $party_id) {
        if ($names = get_party_names_csv($conn, $party_id)) {
            echo $names;
        }
    }

    function get_url_key($conn, $party_id) {
        $stmt = $conn->prepare("SELECT value FROM url_keys WHERE party_id = ?");
        $stmt->bind_param('i', $party_id);
        $stmt->execute();
        $stmt->bind_result($url_key);
        if ($stmt->fetch()) {
            return urlencode($url_key);
        }
        return null;
    }
?>
