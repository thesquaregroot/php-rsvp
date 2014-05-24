<?php
    require_once(__DIR__."/../rsvp_config.php");
    require_once(__DIR__."/../mysql.php");
?>
<!DOCTYPE html>
<html>
<head>
<title><?=$MASTER_PAGE_TITLE?></title>
<!-- jQuery -->
<script type="text/javascript" src="<?=$JQUERY_LOCATION?>"></script>
<!-- jQuery UI -->
<script type="text/javascript" src="<?=$JQUERY_UI_JS_LOCATION?>"></script>
<link rel="stylesheet" type="text/css" href="<?=$JQUERY_UI_CSS_LOCATION?>" />
<!-- php-rsvp -->
<script type="text/javascript" src="<?=$JS_DIR?>/rsvp.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$CSS_DIR?>/rsvp.css" />
</head>

<body>
