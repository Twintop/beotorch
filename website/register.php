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
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/register.inc.php';



$UserRepository = new UserRepository($mysqli, $session);
$User = $UserRepository->IsUserLoggedIn();

if ($User)
{
	header('Location: ' . SITE_ADDRESS . '/index.php');
}

$pageTitle = "Account Registration";
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';
?>
        <h2>Register</h2>
        <div class="panel panel-default" style="width: 400px; float: left;">
        	<div class="panel-body">
				<form action="<?php echo esc_url($_SERVER['PHP_SELF']); ?>" method="post" name="registration_form" class="form-horizontal">
				    <?php
				if (!empty($error_msg)) { ?>
					<div class="form-group">
						<div class="col-xs-12 alert-short alert-danger" style="margin-bottom: 0px;">
							<?php echo $error_msg; ?>
						</div>
					</div>    
		<?php        }
					?>     					
					<div class="form-group has-feedback" id="emailDiv">
						<label class="control-label col-xs-5" for="email">Email:</label>
						<div class="col-xs-7">
							<input type="email" name="email" id="email" placeholder="Your Email" class="form-control" />
							<span class="glyphicon glyphicon-remove form-control-feedback" id="emailSpan" style="display: none;"></span>
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
							<button type="submit" class="btn btn-default" onclick="return regformhash(this.form, this.form.email, this.form.password, this.form.confirmpwd);">Register</button>
						</div>
					</div>
				</form>
			</div>
		</div>
        
        <div class="panel panel-default" style="width: 330px; float: right; margin-top: -55px;">
			<div class="panel-heading" style="padding-top: 5px; padding-bottom: 5px;">Advertisement</div>
			<div class="panel-body">

			</div>
		</div>
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
