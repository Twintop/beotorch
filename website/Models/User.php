<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

/**
 * User Class
 * Model handles information about a user's account
 */
class User {    
    var $UserId;
    var $Email;
    var $Password;
    var $Salt;
    var $IsActive;
    var $ActivationCode;
    var $SimEmails;
    var $PasswordResetCode;
    var $UserLevelId;
    var $UserLevelTitle;
    var $UserLevelDescription;
    var $DaysBeforeCleanup;
    var $MaxIterations;
    var $MaxSimQueueSize;
    var $MinSimLength;
    var $MaxSimLength;
    var $ScalingEnabled;
    var $MaxReports;
    var $MaxBossCount;
    var $MaxActors;
    var $HiddenSimulations;
    var $CustomProfile;
}
