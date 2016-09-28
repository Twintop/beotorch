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

if (!$User)
{
	header('Location: ' . SITE_ADDRESS . 'index.php');
}

$pageTitle = "Change Account Password";
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';

?>
		<h2>Change Account Password</h2>
        <?php    
        
        $found = 0;
		$showResetBox = 0;
 
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
		 
	    		$resetCheckResult = UserPasswordReset($mysqli, $User[0]['UserId'], null, null, $password, $random_salt);		
	    		
	    		if ($resetCheckResult->UserId == $User->UserId && $User->UserId != 0)
	    		{
	    			$showResetBox = 3;
	    			$result = "<p>Your password has been updated. You will now need to <a href=\"login.php\">log in</a> with your new password.</p>";
	    		}		
			}
	    }
        ?>
        
        <div class="panel panel-default" style="width: 400px; float: left;">
        	<div class="panel-body">
        		<?php
        		if ($showResetBox == 3)
        		{
        		?>
        		
					<div class="form-group">
						<div class="col-xs-12 alert-short alert-success" style="margin-bottom: 0px;">
							<?php echo $result; ?>
						</div>
					</div> 
				<?php
        		}
        		else
        		{
        		?>
				<form action="<?php echo esc_url($_SERVER['PHP_SELF']); ?>" method="post" name="changepassword_form" class="form-horizontal">
				    <?php
					if (!empty($result))
					{
						if ($showResetBox == 3)
						{					
					?>
					<div class="form-group">
						<div class="col-xs-12 alert-short alert-success" style="margin-bottom: 0px;">
							<?php echo $result; ?>
						</div>
					</div> 
					
						<?php
						}
						else
						{
						?>
					
					<div class="form-group">
						<div class="col-xs-12 alert-short alert-danger" style="margin-bottom: 0px;">
							<?php echo $result; ?>
						</div>
					</div>    
					<?php
					   	} 
					}
					?>    
					<div class="form-group has-feedback" id="emailDiv">
						<label class="control-label col-xs-5" for="email">Email:</label>
						<div class="col-xs-7">
			    			<p class="form-control-static"><?php echo $User->Email; ?></p>							
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
							<button type="submit" class="btn btn-default" onclick="return regformhash(this.form, '<?php echo $User[0]['Email']; ?>', this.form.password, this.form.confirmpwd);">Change Account Password</button>
						</div>
					</div>
				</form>
				<?php
				}
				?>
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
