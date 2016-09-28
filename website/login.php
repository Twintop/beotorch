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
 
//

$UserRepository = new UserRepository($mysqli, $session);
$User = $UserRepository->IsUserLoggedIn();

if ($User == true) {
	header('Location: ' . SITE_ADDRESS . 'index.php');
}

$pageTitle = "Login";
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';
?>

        <?php
        
        if (isset($_GET['error']))
        {
        	switch ($_GET['error'])
        	{
        		case 3: //Account needs to be activated
					$error_msg = "This account has not been activated. Please check your email and follow the link provided. If you did not receive the activation email, <a href=\"" . SITE_ADDRESS . "activate-resend.php\">click here to resend it</a>.";
					break;
				case 4: //Brute force
					$error_msg = "There have been too many failed attempts to log in to this account recently. Please wait 5 minutes before trying again. If this problem persists, please contact the administrator.";
					break;
        		case 6: //User doesn't exist. Don't tell the person logging in that, though
        		case 5: //Wrong username/password
        		case 1: //Shouldn't get error 1 (successful login). Give general message.
        		case 2: //Hash mismatch. Display it as an invalid login.
        		default: //Any other errors
					$error_msg = "Invalid username/password. If you forgot your password, <a href=\"" . SITE_ADDRESS . "resetpassword.php\">click here to reset it.</a>";
					break;
			}
        }
        ?> 
        <div class="panel panel-default panel-col-nonstandard" style="width: 400px; float: left;">
			<div class="panel-heading">Account Login</div>
        	<div class="panel-body">
				<form action="process_login.php" method="post" name="login_form" class="form-horizontal">
				    <?php
					if (!empty($error_msg)) { ?>
					<div class="form-group" style="padding-left: 10px; padding-right: 10px;">
						<div class="col-xs-12 alert-short alert-danger" style="margin-bottom: 0px;">
							<?php echo $error_msg; ?>
						</div>
					</div>    
					<?php
					}
					?>                    
					<div class="form-group">
						<label class="control-label col-xs-3" for="email">Email:</label>
						<div class="col-xs-9">
							<input type="email" name="email" id="email" placeholder="Account Email" class="form-control" />
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-xs-3" for="password">Password:</label>
						<div class="col-xs-9">
							<input type="password" name="password" id="password" placeholder="Account Password" class="form-control" />
						</div>
					</div>
					<div class="form-group"> 
						<div class="col-xs-offset-3 col-xs-9">
							<button type="submit" class="btn btn-default" onclick="formhash(this.form, this.form.password);">Login</button>
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-offset-3 col-xs-9">
							Don't have an account? <a href="register.php">Register here</a>.
						</div>
						<div class="col-xs-offset-3 col-xs-9">
							<a href="resetpassword.php">Forgot password?</a>
						</div>
					</div>
				</form>
        	</div>
        </div>
               
        <div class="panel panel-default" style="width: 320px; float: right;">
			<div class="panel-heading" style="padding-top: 5px; padding-bottom: 5px;">Advertisement</div>
			<div class="panel-body" style="padding: 10px;">

			</div>
		</div>
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
