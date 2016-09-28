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

$pageTitle = "Contact";
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';
?>
        <h2>Contact</h2>
        <div style="clear: both;">
        
        <div class="panel panel-default" style="width: 600px; float: left;">
			<div class="panel-body">
				<div class="col-xs-4 text-right" style="font-weight: bold;">Twitter:</div>
				<div class="col-xs-8"><a href="http://twitter.com/beotorch" target="_blank">@Beotorch</a></div>
			</div>
			<div class="panel-body">
				<div class="col-xs-4 text-right" style="font-weight: bold;">Discord:</div>
				<div class="col-xs-8"><a href='https://discord.gg/0zVQ3V9e7OTH13RG' target='_blank'>Instant Invite: https://discord.gg/0zVQ3V9e7OTH13RG</a></div>
			</div>            
			<div class="panel-body">
				<div class="col-xs-4 text-right" style="font-weight: bold;">Bugs/Suggestions:</div>
				<div class="col-xs-8"><a href="https://github.com/Twintop/beotorch-issues/issues" target="_blank">Create a new issue on GitHub</a></div>
			</div>
			<div class="panel-body">
				<div class="col-xs-4 text-right" style="font-weight: bold;">Email:</div>
				<div class="col-xs-8"><a href="mailto:admin@beotorch.com">admin@beotorch.com</a></div>
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
