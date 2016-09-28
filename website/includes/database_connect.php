<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 'On');
/*include_once $_SERVER['DOCUMENT_ROOT'] . '/system-config.php';*/
include_once $_SERVER['DOCUMENT_ROOT'] . '/beotorch-config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/SecureSessionHandler.php';

define("PATCH_LIVE", "7.0");
define("PATCH_PTR", "7.0");
define("PATCH_BETA", "7.0");

$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);

$mysqli->set_charset('utf8');
$mysqli->query("SET NAMES utf8");
$mysqli->query("SET @@session.time_zone='+00:00'");
$mysqli->query("SET @IPAddress = '" . $mysqli->real_escape_string($_SERVER['REMOTE_ADDR']) . "'");

$session = new SecureSessionHandler("0xqgU9UcYkYg7E5SyMA5HnM9", SESSION_NAME);
ini_set('session.save_handler', 'files');
session_set_save_handler($session, true);
session_save_path($_SERVER['DOCUMENT_ROOT'] . '/includes/sessions');
$session->start();

if (!$session->isValid())
{
    $session->forget();
}

?>
