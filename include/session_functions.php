<?php
    // functions:
    //
    //      start_session()
    //      end_session()
    //      logged_in() -> bool

    // default to 10 minute session
    function start_session($timeout_length = 600 /* ten minutes */) {
        session_start();
        if (isset($_SESSION['LAST_ACCESS'])) {
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
