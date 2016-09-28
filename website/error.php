<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database_connect.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/Repositories/UserRepository.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
 


$UserRepository = new UserRepository($mysqli, $session);
$User = $UserRepository->IsUserLoggedIn();

$error = filter_input(INPUT_GET, 'err', $filter = FILTER_SANITIZE_STRING);

if (! $error) {
    $error = 'Oops! An unknown error happened.';
}

$pageTitle = "Login Error";

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';
?>

        <h1>There was a problem</h1>
        <p class="error"><?php echo $error; ?></p> 
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
