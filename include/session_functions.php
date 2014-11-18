<?php
    require_once(__DIR__."/rsvp_config.php");
    // functions:
    //
    //      start_session()
    //      end_session()
    //      logged_in() -> bool

    if (!isset($DEFAULT_SESSION_TIMEOUT)) {
        $DEFAULT_SESSION_TIMEOUT = 600; // ten minutes
    }

    // default to 10 minute session
    function start_session($timeout_length = null) {
        session_start();
        if (isset($_SESSION['LAST_ACCESS'])) {
            if (!isset($timeout_length)) {
                global $DEFAULT_SESSION_TIMEOUT;
                $timeout_length = $DEFAULT_SESSION_TIMEOUT;
            }
            if ($_SESSION['LAST_ACCESS'] < (time() - $timeout_length)) {
                // last access was too long ago
                end_session();
            }
        }
        $_SESSION['LAST_ACCESS'] = time();
    }

    function end_session() {
        session_destroy();
        $_SESSION = array();
    }

    function logged_in() {
        return isset($_SESSION['id']);
    }
?>
