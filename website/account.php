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
	header('Location: ' . SITE_ADDRESS . '/login.php');
}

$pageTitle = "New Simulation";
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';
?>
            <h1>Account Settings</h1>
		    <div class="panel panel-default panel-col-nonstandard" style="width: 800px; float: left;">
		    	<div class="panel-body">       
		        <?php		        
		        
		        if (isset($_POST['submitForm']))
		        {
		        	$simEmails = 0;
		        	if (isset($_POST['simEmails']))
		        	{
		        		$simEmails = 1;
		        	}
		        	
		        	$user1 = $UserRepository->UserUpdate($User->UserId, $simEmails);
		        	
		        	if ($user1 == null)
		        	{
		        		echo "<div class=\"alert alert-short alert-danger\">An error occurred while trying to save your account settings.</div>";
		        	}
		        	else
		        	{
		        		echo "<div class=\"alert alert-short alert-success\">Your account settings have been updated.</div>\n";
		        		
		        		$User = $user1;
		        	}
		        }
		        
			    ?> 
			    <form action="account.php" method="post" name="account_form" class="form-horizontal">                    
					<div class="form-group">
						<label class="control-label col-xs-4" for="email">Email Address:</label>
						<div class="col-xs-8">
			    			<p class="form-control-static"><?php echo $User->Email; ?></p>
			    		</div>
			    	</div>                   
					<div class="form-group">
						<label class="control-label col-xs-4" for="accountLevel">Account Level:</label>
						<div class="col-xs-8">
			    			<p class="form-control-static"><?php echo $User->UserLevelTitle; ?></p>
			    		</div>
			    	</div>                   
					<div class="form-group">
						<label class="control-label col-xs-4" for="simulationQueue">Simulation Queue:</label>
						<div class="col-xs-8">
			    			<p class="form-control-static"><?php echo $User->MaxSimQueueSize; ?></p>
			    		</div>
			    	</div>                    
					<div class="form-group">
						<label class="control-label col-xs-4" for="maximumActors">Maximum Actors (Talent Comparison):</label>
						<div class="col-xs-8">
			    			<p class="form-control-static"><?php echo $User->MaxActors; ?></p>
			    		</div>
			    	</div>                   
					<div class="form-group">
						<label class="control-label col-xs-4" for="maximumIterations">Maximum Iterations:</label>
						<div class="col-xs-8">
			    			<p class="form-control-static"><?php echo $User->MaxIterations; ?></p>
			    		</div>
			    	</div>                  
					<div class="form-group">
						<label class="control-label col-xs-4" for="simulationLengthRange">Simulation Length Range:</label>
						<div class="col-xs-8">
			    			<p class="form-control-static"><?php echo $User->MinSimLength . " - " . $User->MaxSimLength; ?> seconds</p>
			    		</div>
			    	</div>                  
					<div class="form-group">
						<label class="control-label col-xs-4" for="maximumReports">Maximum Reports Before Cleanup:</label>
						<div class="col-xs-8">
			    			<p class="form-control-static"><?php echo $User->MaxReports; ?></p>
			    		</div>
			    	</div>                 
					<div class="form-group">
						<label class="control-label col-xs-4" for="daysBeforeReportCleanup">Days Before Report Cleanup:</label>
						<div class="col-xs-8">
			    			<p class="form-control-static"><?php echo $User->DaysBeforeCleanup; ?></p>
			    		</div>
			    	</div>                 
					<div class="form-group">
						<label class="control-label col-xs-4" for="hiddenReports">Can Hide Reports:</label>
						<div class="col-xs-8">
                            <p class="form-control-static"><?php if ($User->HiddenSimulations) { echo "Yes"; } else { echo "No"; } ?></p>
			    		</div>
			    	</div>                 
					<div class="form-group">
						<label class="control-label col-xs-4" for="hiddenReports">Can Queue Custom Profile Simulations:</label>
						<div class="col-xs-8">
                            <p class="form-control-static"><?php if ($User->CustomProfile) { echo "Yes"; } else { echo "No"; } ?></p>
			    		</div>
			    	</div> 
					<div class="form-group">
						<div class="col-xs-offset-4 col-xs-8">
			    			<label><input id="simEmails" name="simEmails" type="checkbox" value="1" <?php if ($User->SimEmails) { echo "checked "; } ?>/> Receive email notifications when simulations complete?</label>
			    		</div>
			    	</div>
			    	<input type="hidden" id="submitForm" name="submitForm" value="true" />
					<div class="form-group">
						<div class="col-xs-offset-4 col-xs-8">
			    			<button id="submitAccountSettings" type="submit" class="btn btn-primary" value="submit">Save Account Settings</button>
			    		</div>
			    	</div>
			    </table>
			    </form>
			    <script>
			    	$("#server").chosen();
			    	$("#fightType").chosen();
			    </script>
			</div>
        </div>
               
        <div class="panel panel-default" style="width: 320px; float: right; margin-top: -55px;">
			<div class="panel-heading" style="padding-top: 5px; padding-bottom: 5px;">Advertisement</div>
			<div class="panel-body" style="padding: 10px;">

			</div>
		</div>
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
