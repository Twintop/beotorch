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

if (!$User)
{
	header('Location: ' . SITE_ADDRESS . 'login.php');
}

if ($User->UserLevelId != 9)
{
	header('Location: ' . SITE_ADDRESS . '/index.php');
}

$pageTitle = "All User Sims";

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';
?>
        <?php if ($User) : ?>
            <?php
            
            if (isset($_GET['userid']))
            {
            	$listUserId = $_GET['userid'];
            }
            else
            {
            	$listUserId = -2;
            }
            
				$simulations = $SimulationRepository->SimulationList($listUserId, 0, 250, 2);
            
            if (count($simulations) > 0)
            {
			?>
					<table class="table table-bordered table-hover table-striped display" id="simulationsTable">
    	        		<thead>
		        			<tr>
		        				<th>SimId</th>
		        				<th>UserId</th>
		        				<th>Email</th>
			        			<th>Character</th>
			        			<th>Server</th>
			        			<th>Region</th>
			        			<th>Faction</th>
			        			<th>Race</th>
			        			<th>Class</th>
			        			<th>Fight Type</th>
			        			<th>Iterations</th>
			        			<th>Status</th>
			        			<th>Queued</th>
			        			<th>Completed</th>
			        			<th>Last Log Time</th>
			        			<th>LogId</th>
			        			<th>Result</th>
			        		</tr>
    	        		</thead>
    	        		<tfoot>
		        			<tr>
		        				<th>SimId</th>
		        				<th>UserId</th>
		        				<th>Email</th>
			        			<th>Character</th>
			        			<th>Server</th>
			        			<th>Region</th>
			        			<th>Faction</th>
			        			<th>Race</th>
			        			<th>Class</th>
			        			<th>Fight Type</th>
			        			<th>Iterations</th>
			        			<th>Status</th>
			        			<th>Queued</th>
			        			<th>Completed</th>
			        			<th>Last Log Time</th>
			        			<th>LogId</th>
			        			<th>Result</th>
			        		</tr>
    	        		</tfoot>
			<?php
    	        foreach ($simulations as $simulation)
    	        {
    	        	echo "<tr>
    	        			<td>" . $simulation->SimulationId . "</td>
    	        			<td><a href=\"http://" . $_SERVER['SERVER_NAME'] . "/view_user_sims.php?userid=" . $simulation->UserId . "\" target=\"_blank\">" . $simulation->UserId . "</a></td>
    	        			<td><a href=\"http://" . $_SERVER['SERVER_NAME'] . "/view_user_sims.php?userid=" . $simulation->UserId . "\" target=\"_blank\">" . $simulation->Email . "</a></td>
    	        			<td><a href=\"http://" . $simulation->SimulationActors[0]->RegionURL . $simulation->SimulationActors[0]->ServerName . "/" . $simulation->SimulationActors[0]->CharacterName . "/advanced\" target=\"_blank\">" . $simulation->SimulationActors[0]->CharacterName . "</a> (" . $simulation->SimulationActors[0]->CharacterId . ")</td>
    	        			<td>" . $simulation->SimulationActors[0]->ServerName . " (" . $simulation->SimulationActors[0]->ServerId . ")</td>
    	        			<td>" . $simulation->SimulationActors[0]->RegionName . " (" . $simulation->SimulationActors[0]->RegionId . ")</td>
    	        			<td><span style=\"color: #" . $simulation->SimulationActors[0]->FactionColor . "\">" . $simulation->SimulationActors[0]->FactionName . "</span> (" . $simulation->SimulationActors[0]->Faction . ")</td>
    	        			<td><span style=\"color: #" . $simulation->SimulationActors[0]->FactionColor . "\">" . $simulation->SimulationActors[0]->RaceName . "</span> (" . $simulation->SimulationActors[0]->RaceId . ")</td>
    	        			<td><span style=\"color: #" . $simulation->SimulationActors[0]->ClassColor . "\">" . $simulation->SimulationActors[0]->ClassName . "</span> (" . $simulation->SimulationActors[0]->ClassId . ")</td>
    	        			<td>" . $simulation->SimulationTypeSystemName . " (" . $simulation->SimulationTypeId . ")</td>
    	        			<td>" . $simulation->Iterations . "</td>
    	        			<td>" . $simulation->StatusName . " (" . $simulation->SimulationStatusId . ")</td>
    	        			<td>" . $simulation->TimeQueued . "</td>
    	        			<td>" . $simulation->TimeCompleted . "</td>
    	        			<td>" . $simulation->SimulationLogTime . "</td>
    	        			<td>" . $simulation->SimulationLogId . "</td>
    	        			<td>";
    	        	
    	        	if ($simulation->SimulationStatusId == 3)
    	        	{
    	        		echo "<a href=\"http://" . $_SERVER['SERVER_NAME'] . "/simulationdetails.php?r=" . $simulation->SimulationGUID . "\" target=\"_blank\">View Report</a>";
    	        	}
    	        	
    	        	echo "</td>
    	        		</tr>";
    	        }
    	        		
            	echo "</table>";
            }
            else
            {
            	echo "<p>You have no simulations.</p>";
            }
            
            ?>
            
           	<script type="text/javascript">
           	$(document).ready(function() {
           		$("#simulationsTable").DataTable({
	           		"dom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
    	       		"order": [[ 14, "desc" ]],
    	       		"iDisplayLength": 50
           		});
           	} );
           	</script>
           	
        <?php else : ?>
            <p>
                <span class="error">You are not authorized to access this page.</span> Please <a href="index.php">login</a>.
            </p>
        <?php endif; ?>
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
