<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database_connect.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/Repositories/SimulationRepository.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/Repositories/UserRepository.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

$SimulationRepository = new SimulationRepository($mysqli, $session);
$UserRepository = new UserRepository($mysqli, $session);
$User = $UserRepository->IsUserLoggedIn();

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';
?>
		<h2>Beotorch</h2>
        <div class="panel panel-default" style="width: calc(100% - 400px); float: left;">
			<div class="panel-body">
				<div>
				<p>Thanks for stopping by!</p>
				<p>What is Beotorch? Beotorch is a website that allows you to simulate your <a href="http://www.worldofwarcraft.com/" target="_blank">World of Warcraft</a> character in a combat situation using <a href="http://simulationcraft.org/" target="_blank">SimulationCraft</a>.</p>
				<p>Please remember, this tool is currently in <u>beta</u>! The system overall is stable but features may break from time to time.</p>
				<p>If you haven't already, please <a href="<?php echo SITE_ADDRESS; ?>register.php">register for an account</a> (it's free!) and queue up your character. If you already have an account, please <a href="<?php echo SITE_ADDRESS; ?>login.php">log in</a> to see your <a href="<?php echo SITE_ADDRESS; ?>simulations.php">completed simulations</a> or <a href="<?php echo SITE_ADDRESS; ?>newsimulation.php">queue up a new one</a>.</p>
				<p>Have questions? Comments? Feature suggestion? Bug report? Don't hesitate to <a href="mailto:admin@beotorch.com">email me</a> or reach out on <a href="http://twitter.com/beotorch" target="_blank">Twitter</a>, or, <a href="https://github.com/Twintop/beotorch/issues" target="_blank">create a new issue (for a bug report, feature suggestion, etc.) on GitHub</a>. Thanks!</p>
				<p><i>--Twintop</i></p>
				</div>
			</div>
		</div>
        <div class="panel panel-default" style="width: 368px; float: right; margin-top: -55px;">
			<div class="panel-heading" style="padding-top: 5px; padding-bottom: 5px;">Advertisement</div>
			<div class="panel-body">

			</div>
		</div>
		<div style="clear: both;"></div>
        <div class="panel panel-default">
			<div class="panel-heading">Recently Completed User Simulations</div>
			<div class="panel-body">
				<?php
				$simulations = $SimulationRepository->SimulationList(-2, 3, 100, 0, 2);
            
                if ($User->UserLevelId == 9)
                {
                    include_simulationsTable($simulations, 1, 1, $User);
                }
                else
                {
                    include_simulationsTable($simulations);
                }
                
	           	?>
            </div>
        </div>
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
