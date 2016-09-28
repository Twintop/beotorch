<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database_connect.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
 
if (isset($_POST['email'], $_POST['p']))
{
    $email = $_POST['email'];
    $password = $_POST['p']; // The hashed password.
    
    $login_response = login($mysqli, $session, $email, $password);
    
    if ($login_response == 1) //ok!
    {
        // Login success 
        header('Location: ../index.php');
    }
    else
    {
        // Login failed 
        header('Location: ../login.php?error=' . $login_response);
    }
}
else
{
    // The correct POST variables were not sent to this page. 
    echo 'Invalid Request';
}

?>
