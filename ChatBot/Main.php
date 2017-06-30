<?php
/* to comment */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$_POST["ref"] = "OJNQotN7OJMKsI58Im8YotAhIvuFMty1PP5KpjIdIFZOC1pmJ2yHYjqzN2VOLtO0OTtQYSN7I2cKBSS1OG0ToNIzNm0NMtI+Im1rsSp/P2xQoyLbHzRWottmImHSp1IeNKMKBygaIQpUqNZvNGVNWDDtN31DBypfImuELDIeOvRSZDAxNQtSoSp2Kw9KWjgzNlx";
$_POST["Date"]  = "06/12/2017";
$_POST["Recherche"]="Dispo";
/* to comment */

require_once 'BotClass.php';
$bot = new Bot();

$bot->user->lib->FormatDay();