<?php
    include("../../../include/session_functions.php");
    start_session();
    if (!logged_in() || !(isset($_POST['meal_id']) || isset($_GET['meal_id']))) {
        die();
    }
    $meal_id = isset($_POST['meal_id']) ? $_POST['meal_id'] : $_GET['meal_id'];
    include("../../../include/mysql.php");
?>
{
<?php
    $stmt = $rsvp_conn->prepare("SELECT name, description FROM meals WHERE id = ?");
    $stmt->bind_param("i", $meal_id);
    $stmt->execute();
    $stmt->bind_result($name, $description);
    $stmt->fetch();
?>
    "name": "<?=htmlspecialchars($name)?>",
    "description": "<?=str_replace("\r\n", "\\r\\n", htmlspecialchars($description))?>"
}

