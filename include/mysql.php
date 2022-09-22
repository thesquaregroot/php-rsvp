<?php

    require_once(__DIR__."/rsvp_config.php");
    
    
    if (isset($MYSQL_USERNAME) &&  isset($MYSQL_PASSWORD)) {
        // need to check how secure this is (attack would require access to config)

        
        $rsvp_conn = new mysqli("localhost:3308", $MYSQL_USERNAME, $MYSQL_PASSWORD, $MYSQL_DB_NAME);

       
        if ($rsvp_conn->connect_errno) {
            print_error("Failure connecting to database: " . $rsvp_conn->error);
        }
    }else{
        
    }
    // $rsvp_conn in scope for use elsewhere
?>
