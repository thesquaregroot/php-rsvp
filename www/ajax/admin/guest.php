<?php
    include("../../../include/session_functions.php");
    start_session();
    if (!logged_in() || !(isset($_POST['guest_id']) || isset($_GET['guest_id']))) {
        die();
    }
    $guest_id = isset($_POST['guest_id']) ? $_POST['guest_id'] : $_GET['guest_id'];
    include("../../../include/mysql.php");
?>
{
<?php
    $stmt = $rsvp_conn->prepare("SELECT name, meal_id, response, is_plus_one FROM guests WHERE id = ?");
    $stmt->bind_param("i", $guest_id);
    $stmt->execute();
    $stmt->bind_result($name, $meal_id, $response, $is_plus_one);
    $stmt->fetch();
?>
    "name": "<?=htmlspecialchars($name)?>",
    "meal_id": "<?=$meal_id?>",
    "response": "<?=$response?>",
    "is_plus_one": "<?=$is_plus_one?>"
}

