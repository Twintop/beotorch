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

if ($User)
{
	header('Location: ' . SITE_ADDRESS . 'index.php');
}

$pageTitle = "Resend Account Activation";
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';

?>
		<h2>Resend Account Activation</h2>
        <?php
        
        if (isset($_POST['email']))
        {
		    $userReset = $UserRepository->UserGet(null, $_POST['email']);
		    
		    if ($userReset->IsActive == 1) //Already active
		    {
		    	$result = "<p>This account has already been activated. Please <a href=\"login.php\">log in</a>.</p>";
		    }
		    elseif ($userReset->UserId == null) //No matching account
		    {
		    	$result = "<p>This is not a valid account email address.</p>";
		    }
		    else
		    {            	
		    	$subject = "Beotorch Account Activation";
            	
	        	$headers   = array();
				$headers[] = "MIME-Version: 1.0";
				$headers[] = "Content-type: text/plain; charset=iso-8859-1";
				$headers[] = "From: Beotorch <noreply@beotorch.com>";
				$headers[] = "Reply-To: Beotorch <noreply@beotorch.com>";
				$headers[] = "Subject: {$subject}";
				$headers[] = "X-Mailer: PHP/".phpversion();
				
				$emailBody = "Hello,\r\n\r\nThank you for registering your account with Beotorch! Before you can\r\nstart using your account we need you to activate it. You can click the\r\nfollowing link to activate your account:\r\n\r\nhttps://www.beotorch.com/activate.php?ac=" . $userReset->ActivationCode . "\r\n\r\nThank you!\r\n-The Beotorch Team\r\nhttps://www.beotorch.com - http://twitter.com/Beotorch";

				$mailSent = mail($userReset->Email, $subject, $emailBody, implode("\r\n", $headers));
				
				if ($mailSent)
				{
			    	$result = "<p>Your activation email has been resent.</p>";	        		
	        	}
	        	else
	        	{
		        	$result = "<p>Could not resend activation email. Please contact the administrator.";
		        }
		    }
        }
        
        if (isset($_POST['email']) && $result)
        {
        	echo $result;
        }
        else
        {
        
        ?> 
        <div class="panel panel-default panel-col-nonstandard" style="width: 400px; float: left;">
        	<div class="panel-body">
				<form action="activate-resend.php" method="post" name="activateresend_form" class="form-horizontal">                   
					<div class="form-group">
						<label class="control-label col-xs-3" for="email">Email:</label>
						<div class="col-xs-9">
							<input type="email" name="email" id="email" placeholder="Account Email" class="form-control" />
						</div>
					</div>
					<div class="form-group"> 
						<div class="col-xs-offset-3 col-xs-9">
							<button type="submit" class="btn btn-default">Resend Activation Code</button>
						</div>
					</div>
				</form>
        	</div>
        </div>
        
		<?php
		}
		?>
               
        <div class="panel panel-default" style="width: 320px; float: right;">
			<div class="panel-heading" style="padding-top: 5px; padding-bottom: 5px;">Advertisement</div>
			<div class="panel-body" style="padding: 10px;">

			</div>
		</div>
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
