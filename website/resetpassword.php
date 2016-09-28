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

$pageTitle = "Reset Account Password";
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';

?>
		<h2>Reset Account Password</h2>
        <?php    
        
        $found = 0;
		$showResetBox = 0;
        
        if (isset($_GET['rc']))
        {    
		    if (isset($_POST['p']))
		    {
				$password = filter_input(INPUT_POST, 'p', FILTER_SANITIZE_STRING);
				if (strlen($password) != 128)
				{
					// The hashed pwd should be 128 characters long.
					// If it's not, something really odd has happened
					$result = '<p>Invalid password configuration.</p>';
				}
				else
				{
					// Create a random salt
					$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
			 
					// Create salted password 
					$password = hash('sha512', $password . $random_salt);
			 
		    		$resetCheckResult = $UserRepository->UserPasswordReset(null, null, $_GET['rc'], $password, $random_salt);		
		    		
		    		if ($resetCheckResult->UserId > 0)
		    		{
		    			$showResetBox = 3;
		    			$result = "<p>Your password has been updated. Please <a href=\"login.php\">log in</a>.</p>";
		    		}		
				}
		    }
		    else
		    {
		    	$resetCheckResult = $UserRepository->UserPasswordReset(null, null, $_GET['rc']);
				
                
				if ($resetCheckResult->UserId > 0) //Already active
				{
					$showResetBox = 1;
				}
				else
				{
					$showResetBox = 2;
					$result = "<p>This is not a valid password reset code. Please <a href=\"login.php\">log in</a>.</p>";
				}
		    }
        }
        elseif (isset($_POST['email']))
        {
		    $userReset = $UserRepository->UserGet(null, $_POST['email']);
		    
		    if ($userReset->UserId == null) //No matching account
		    {
		    	$result = "<p>This is not a valid account email address.</p>";
		    }
		    else
		    {    
		    	$found = 1;
				$reset_code = md5(openssl_random_pseudo_bytes(32)); 
				$UserRepository->UserPasswordResetCodeSet($userReset->UserId, $reset_code);
				       	
		    	$subject = "Beotorch Password Reset";
            	
	        	$headers   = array();
				$headers[] = "MIME-Version: 1.0";
				$headers[] = "Content-type: text/plain; charset=iso-8859-1";
				$headers[] = "From: Beotorch <noreply@beotorch.com>";
				$headers[] = "Reply-To: Beotorch <noreply@beotorch.com>";
				$headers[] = "Subject: {$subject}";
				$headers[] = "X-Mailer: PHP/".phpversion();
				
				$emailBody = "Hello,\r\n\r\nSomeone has requested that this account's password be changed. If this was not you, no further action is required and you can ignore this email. If this was you, please follow the link below to finish resetting your password:\r\n\r\nhttps://www.beotorch.com/resetpassword.php?rc=" . $reset_code . "\r\n\r\nThank you!\r\n-The Beotorch Team\r\nhttps://www.beotorch.com - http://twitter.com/Beotorch";

				$mailSent = mail($userReset->Email, $subject, $emailBody, implode("\r\n", $headers));
				
				if ($mailSent)
				{
			    	$result = "<p>Your password reset email has been sent.</p>";	        		
	        	}
	        	else
	        	{
		        	$result = "<p>Could not send password reset email. Please contact the administrator.";
		        }
		    }
        }
        
        if ((isset($_POST['email']) && $result && $found == 1) || $showResetBox == 2 || $showResetBox == 3)
        {
        	echo $result;
        }
        elseif ($showResetBox == 1)
        {
        ?>
        
        <div class="panel panel-default" style="width: 400px; float: left;">
        	<div class="panel-body">
				<form action="<?php echo esc_url($_SERVER['PHP_SELF']) . "?rc=" . $_GET['rc']; ?>" method="post" name="resetpassword_form" class="form-horizontal">
				    <?php
				if (!empty($result)) { ?>
					<div class="form-group">
						<div class="col-xs-12 alert-short alert-danger" style="margin-bottom: 0px;">
							<?php echo $result; ?>
						</div>
					</div>    
		<?php        }
					?>    
					<div class="form-group has-feedback" id="emailDiv">
						<label class="control-label col-xs-5" for="email">Email:</label>
						<div class="col-xs-7">
			    			<p class="form-control-static"><?php echo $resetCheckResult->Email; ?></p>							
						</div>
					</div>
					<div class="form-group has-feedback" id="passwordDiv">
						<label class="control-label col-xs-5" for="password">Password:</label>
						<div class="col-xs-7">
							<input type="password" name="password" id="password" placeholder="Account Password" class="form-control" onblur="verifyNewAccountPasswords()" />
							<span class="glyphicon glyphicon-remove form-control-feedback" id="passwordSpan" style="display: none;"></span>
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-12 alert-short alert-info" style="margin-bottom: 0px";>
							Passwords must be at least 6 characters long and contain at least: one lowercase letter, one uppercase letter, and one number.
						</div>
					</div>
					<div class="form-group has-feedback" id="confirmpwdDiv">
						<label class="control-label col-xs-5" for="password">Confirm Password:</label>
						<div class="col-xs-7">
							<input type="password" name="confirmpwd" id="confirmpwd" placeholder="Confirm Password" class="form-control" onblur="verifyNewAccountPasswords()" />
							<span class="glyphicon glyphicon-remove form-control-feedback" id="confirmpwdSpan" style="display: none;"></span>
						</div>
					</div>
					<div class="form-group"> 
						<div class="col-xs-offset-5 col-xs-7">
							<button type="submit" class="btn btn-default" onclick="return regformhash(this.form, '<?php echo $resetCheckResult->Email; ?>', this.form.password, this.form.confirmpwd);">Reset Account Password</button>
						</div>
					</div>
				</form>
			</div>
		</div>
        
        <?php
        }
        else
        {
        	if (isset($result))
        	{
        		echo $result;
        	}
        
        ?> 
        <div class="panel panel-default panel-col-nonstandard" style="width: 400px; float: left;">
        	<div class="panel-body">
				<form action="resetpassword.php" method="post" name="resetpassword_form" class="form-horizontal">                   
					<div class="form-group">
						<label class="control-label col-xs-3" for="email">Email:</label>
						<div class="col-xs-9">
							<input type="email" name="email" id="email" placeholder="Account Email" class="form-control" />
						</div>
					</div>
					<div class="form-group"> 
						<div class="col-xs-offset-3 col-xs-9">
							<button type="submit" class="btn btn-default">Send Password Reset Email</button>
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
