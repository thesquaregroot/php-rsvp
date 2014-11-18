<?php
    include("../../../include/session_functions.php");
    start_session();
    if (!logged_in() || !(isset($_POST['party_id']) || isset($_GET['party_id']))) {
        die();
    }
    $party_id = isset($_POST['party_id']) ? $_POST['party_id'] : $_GET['party_id'];
    include("../../../include/mysql.php");
?>
{
<?php
    $stmt = $rsvp_conn->prepare("SELECT nickname FROM parties WHERE id = ?");
    $stmt->bind_param("i", $party_id);
    $stmt->execute();
    $stmt->bind_result($nickname);
    $stmt->fetch();
?>
    "nickname": "<?=htmlspecialchars($nickname)?>"
}

