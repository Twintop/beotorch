<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

/**
 * These are the database login details
 */  
define("HOST", "hostname");     // The host you want to connect to.
define("USER", "username");    // The database username. 
define("PASSWORD", "password");    // The database password. 
define("DATABASE", "databasename");    // The database name.
 
define("CAN_REGISTER", "any");
define("DEFAULT_ROLE", "member");

define("SITE_ADDRESS", 'https://' . $_SERVER['HTTP_HOST'] . '/');

define("WAREHOUSE_FOLDER", '/var/www/html/warehouse/');

define("BLIZZARD_API_KEY", "apikey");

define("SECURE", TRUE);    // FALSE = FOR DEVELOPMENT ONLY!!!!

define("SESSION_NAME", "beotorch");
?>
