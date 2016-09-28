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
include_once $_SERVER['DOCUMENT_ROOT'] . '/Repositories/BatchRepository.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/Repositories/UserRepository.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
 


$SimulationRepository = new SimulationRepository($mysqli, $session);
$UserRepository = new UserRepository($mysqli, $session);
$BatchRepository = new BatchRepository($mysqli, $session, $SimulationRepository);

$User = $UserRepository->IsUserLoggedIn();

$pageTitle = "Batched Simulation Report";
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';

$batchList = $BatchRepository->BatchList(15);

?>
            <div style="float: left">
	            <h2>Batched Simulations</h2><div class="alert alert-short alert-warning">Beotorch uses bleeding edge, nightly builds of <a href="http://github.com/simulationcraft/simc" target="_blank">SimulationCraft</a> for its simulations. If you find any bugs within your results, something fails, or if anything in general just feels "off", <a href="https://github.com/simulationcraft/simc/issues" target="_blank">please open a new issue on the SimulationCraft GitHub</a>!</div>
	        </div>		       
		    <div class="panel panel-default" style="width: 478px; float: right; position: relative; right: 1px; padding-top: 5px; padding-left: 5px; padding-right: 5px;">

			</div>
			<div style="clear: both;"></div>
			
            
			<div class="panel panel-default">
				<div class="panel-heading"><a data-toggle="collapse" href="#BatchList">Batched Simulations</a></div>
				<div id="BatchList" class="panel-collapse collapse in" style="padding-left: 15px;">
					<div>   
                        <table class="table table-bordered table-hover table-striped table-stat-weights" id="batchedTable">
                            <thead>
                                <tr>
                                    <th>Simulation Batch</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                
                                foreach ($batchList as $batch)
                                {
                                    print "<tr>";
                                    print "<td><a href=\"" . SITE_ADDRESS . "batch.php?r=" . $batch->BatchGUID . "\" target=\"_blank\">" . $batch->BatchName . "</a></td>";
                                    print "</tr>";
                                }
                                
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
           	<script type="text/javascript">
           	$(document).ready(function() {    
                $("#batchedTable").DataTable({
                    "dom": '',
                    "order": [[ 0, "asc" ]],    
                    "autoWidth": true,
                    "paging": false
                });
           	});
           	</script>
<?php
			
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
