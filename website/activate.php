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

$User = false;
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';

?>
		<h2>Account Activation</h2>
        <?php
                
        if (isset($_GET['ac']))
        {
		    $activationResult = UserActivate($mysqli, $_GET['ac']);
		    
		    if ($activationResult[0]['IsActive'] == 1) //Already active
		    {
		    	echo "<p>This account has already been activated. Please <a href=\"login.php\">log in</a>.</p>";
		    }
		    elseif ($activationResult[0]['UserId'] == null) //No matching account
		    {
		    	echo "<p>This account has already been activated. Please <a href=\"login.php\">log in</a>.</p>";
		    }
		    else
		    {
		    	echo "<p>Thank you for activating your account. You may now <a href=\"login.php\">log in</a>.</p>";
		    }
        }
        else
        {
            echo '<p class="error">No activation code supplied.</p>';
        }
        
        ?> 
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
