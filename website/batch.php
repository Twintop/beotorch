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

?>
            <div style="float: left">
	            <h2>Batched Simulation Report</h2><div class="alert alert-short alert-warning">Beotorch uses bleeding edge, nightly builds of <a href="http://github.com/simulationcraft/simc" target="_blank">SimulationCraft</a> for its simulations. If you find any bugs within your results, something fails, or if anything in general just feels "off", <a href="https://github.com/simulationcraft/simc/issues" target="_blank">please open a new issue on the SimulationCraft GitHub</a>!</div>
	        </div>		       
		    <div class="panel panel-default" style="width: 478px; float: right; position: relative; right: 1px; padding-top: 5px; padding-left: 5px; padding-right: 5px;">

			</div>
			<div style="clear: both;"></div>
			
			<?php
			
			$validBatch = true;
			
			if (!isset($_GET['r']))
			{
				$validBatch = false;
			}
			
			if ($validBatch)
			{
				$batch = $BatchRepository->BatchGetByGUID($_GET['r']);
				
				if ($batch == false || $batch->BatchGUID != $_GET['r'])
				{
					$validBatch = false;
				}
			}
			
			if (!$validBatch)
			{
				?>
				
				        <div class="panel panel-default">
							<div class="panel-body">
								<div>
								<div class="alert alert-short alert-danger">You did not supply a valid batched simulation report to load.</div>
								</div>
							</div>
						</div>
				
				<?php
			}
			else
			{
			?>
            
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="simulationTableName"><?php print $batch->BatchName; ?></div>
                </div>
            </div>
            
			<div class="panel panel-default">
				<div class="panel-heading"><a data-toggle="collapse" href="#SimulationsIncluded">Batched Simulations Included</a></div>
				<div id="SimulationsIncluded" class="panel-collapse collapse in" style="padding-left: 15px;">
					<div>   
                        <table class="table table-bordered table-hover table-striped table-stat-weights" id="simulationsTable">
                            <thead>
                                <tr>
                                    <th>Simulation GUID</th>
                                    <th>Simulation Name</th>
                                    <th>Actors</th>
                                    <th>Stat Weights</th>
                                    <th>Iterations</th>
                                    <th>Fight Type</th>
                                    <th>Bosses</th>
                                    <th>Length</th>
                                    <th>Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                
                                foreach ($batch->Simulations as $simulation)
                                {
                                    print "<tr>";
                                    print "<td><a href=\"" . SITE_ADDRESS . "simulationdetails.php?r=" . $simulation->SimulationGUID . "\" target=\"_blank\">" . $simulation->SimulationGUID . "</a></td>";
                                    print "<td>" . $simulation->SimulationName . "</td>";
                                    print "<td>" . $simulation->ActorCount() . "</td>";
                                    print "<td>";
                                    if ($simulation->ScaleFactors) { print "Yes"; } else { print "No"; }
                                    print "</td>";
                                    print "<td>" . $simulation->Iterations . "</td>";
                                    print "<td>" . $simulation->SimulationTypeFriendlyName . "</td>";
                                    print "<td>" . $simulation->BossCount . "</td>";
                                    print "<td>" . $simulation->SimulationLength . " +/- " . ($simulation->SimulationLengthVariance * 100) . "%</td>";
                                    print "<td><span class=\"dateTimeSpan\">" . $simulation->TimeCompleted . "</span></td>";
                                    print "</tr>";
                                }
                                
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
			<div class="panel panel-default">
				<div class="panel-heading"><a data-toggle="collapse" href="#BatchedSimulationActorResults">Batched Simulation Actor Results</a></div>
				<div id="BatchedSimulationActorResults" class="panel-collapse collapse in" style="padding-left: 15px;">
					<div>           	
            <?php
            $outputheader0 = "<th>Actor</th>";/	   
            $outputheader1 = "";
            $outputheader2 = "";
            $outputheader3 = "";
            $outputrow0 = "<th style=\"text-align: left;\">Scaling Factors</th>";
            $outputrow1 = "";
            $outputrow2 = "";
            $outputrow3 = "";  
            $normalizedrow0 = "<th style=\"text-align: left;\">Normalized</th>";
            $normalizedrow1 = "";
            $normalizedrow2 = "";
            $normalizedrow3 = "";

            $outputrowsCombined = "";
            $normalizedrowsCombined = "";

            $primaryStatValue = 1;

            $actorCount = 0;

            if ($batch->Simulations[0]->SimulationActors[0]->SimTalent != null)
            {
                if ($batch->Simulations[0]->SimulationActors[0]->ClassName == "Death Knight")
                {
                    $talentLevels = [56, 57, 58, 60, 75, 90, 100];
                }
                elseif ($batch->Simulations[0]->SimulationActors[0]->ClassName == "Demon Hunter")
                {
                    $talentLevels = [99, 100, 102, 104, 106, 108, 110];                            
                }
                else
                {
                    $talentLevels = [15, 30, 45, 60, 75, 90, 100];
                }
            }

            $resultsHeader0 = "<th>Actor</th>\n";
            $resultsHeader0 .= "<th>Simulation</th>\n";
            
            for ($x = 0; $x < 7; $x++)
            {
                $resultsHeader0 .= "<th>" . $talentLevels[$x] . "</th>\n";
            }

            $resultsRow = "";

            $damageTableCols = 3;

            if ($batch->Simulations[0]->SimulationActors[0]->SimulationRole == "Tank")
            {
                $resultsHeader0 .= "<th>TMI</th>\n<th>DPS</th>\n<th>DTPS</th>\n<th>HPS (APS)</th>\n";
                $damageTableCols = 6;
            }
            else
            {
                $resultsHeader0 .= "<th>DPS</th>\n";
            }
            
            $resultsHeader0 .= "<th>Fight Type</th>";
            $resultsHeader0 .= "<th>Bosses</th>";
            $resultsHeader0 .= "<th>Sim Rank</th>";
            
            foreach ($batch->Simulations as $simulation)
            {            
                if ($simulation->SimulationStatusId == 3 && count($simulation->SimulationActors) > 0)
                {
                    $statsCount = 0;
                    foreach ($simulation->SimulationActors as $actor)
                    {
                        $statsCount = 0;
                        $actorCount++; 

                        $talentsURL = "http://us.battle.net/wow/en/tool/talent-calculator#" . $actor->CalcClass . $actor->CalcSpec . "a!" . $actor->CalcTalent . "!" . $actor->CalcGlyph;
                        $resultsRow .= "<tr><th><a href=\"" . $talentsURL . "\" target=\"_blank\" style=\"margin-right: 15px;\">";
                        
                        if ($simulation->CustomProfile)
                        {
                            $resultsRow .= $actor->CustomActorName;
                        }
                        else
                        {
                            $resultsRow .= $actor->CharacterName;

                            if ($actorCount > 1)
                            {
                                $resultsRow .= "_" . $actorCount;
                            }
                        }

                        $resultsRow .= "</a></th>\n";
                        $resultsRow .= "<td><a href=\"" . SITE_ADDRESS . "simulationdetails.php?r=" . $simulation->SimulationGUID . "\" target=\"_blank\">" . $simulation->SimulationName . "</a></td>";

                        for ($y = 0; $y < 7; $y++)
                        {
                            if ($actor->Talents[$y]->TalentId == null)
                            {
                                $resultsRow .= "<td style=\"text-align: center; font-size: 0px;\">0<img src=\"http://media.blizzard.com/wow/icons/18/inv_misc_questionmark.jpg\" title=\"Level " . $talentLevels[$y] . ": No Talent\" class=\"simTableTalentIcon\" data-toggle=\"tooltip\" data-placement=\"top\" /></td>\n";
                            }
                            else
                            {
                                $resultsRow .= "<td style=\"text-align: center; font-size: 0px;\">" . ($actor->Talents[$y]->Column+1) . "<img src=\"http://media.blizzard.com/wow/icons/18/" . $actor->Talents[$y]->Icon . ".jpg\" title=\"Level " . $talentLevels[$y] . ": " . $actor->Talents[$y]->Name . "\" class=\"simTableTalentIcon\" data-toggle=\"tooltip\" data-placement=\"top\" /></td>\n";
                            }
                        }

                        if ($actor->SimulationRole == "Tank")
                        {
                            $resultsRow .= "<td>" . $actor->TMI . "</td>\n<td>" . $actor->DPS . "</td>\n<td>" . $actor->DTPS . "</td>\n<td>" . $actor->HPS . " (" . $actor->APS . ")</td>\n";
                        }
                        else
                        {
                            $resultsRow .= "<td>" . $actor->DPS . "</th>\n";
                        }
                        
                        $resultsRow .= "<td>" . $simulation->SimulationTypeFriendlyName . "</td>\n";
                        $resultsRow .= "<td>" . $simulation->BossCount . "</td>\n";
                        $resultsRow .= "<td>" . $simulation->RankInSimulation($actor->SimulationActorId) . "</td>\n";
                        
                        if ($simulation->ScaleFactors == 1)
                        {    
                            $outputheader1 = "";
                            $outputheader2 = "";
                            $outputheader3 = "";
                            $outputrow1 = "";
                            $outputrow2 = "";
                            $outputrow3 = "";  
                            $normalizedrow1 = "";
                            $normalizedrow2 = "";
                            $normalizedrow3 = "";

                            if ($actor->Scaling["PrimaryStat"] != null)
                            {
                                if ($actor->SimulationRole == "Tank")
                                {
                                    $primaryStatValue = $actor->Scaling["Stamina"];
                                    $primaryStatInfo = GetStatDetails("Stamina");
                                }
                                else
                                {
                                    $primaryStatValue = $actor->Scaling["PrimaryStat"];
                                    $primaryStatInfo = GetStatDetails($actor->SpecializationPrimaryStat);
                                }

                                $outputheader1 = "<th>" . $primaryStatInfo["name"] . "</th>";
                                $outputrow1 = "<td>" . number_format((float)$primaryStatValue, 2, ".", "") . "</td>";
                                $normalizedrow1 = "<td>" . number_format((((float)$primaryStatValue) / ((float)$primaryStatValue)), 2, ".", "") . "</td>";

                                if ($actor->SimulationRole == "Tank")
                                {    
                                    $statValue = $actor->Scaling["PrimaryStat"];
                                    $statInfo = GetStatDetails($actor->SpecializationPrimaryStat);	        				
                                    $outputheader1 .= "<th class=\"text-center\">" . $statInfo["name"] . "</th>";
                                    $outputrow1 .= "<td>" . number_format((float)$statValue, 2, ".", "") . "</td>";
                                    $normalizedrow1 .= "<td>" . number_format((((float)$statValue) / ((float)$primaryStatValue)), 2, ".", "") . "</td>";       	        					
                                }
                            }

                            foreach ($actor->Scaling as $key => $value)
                            {
                                $rawName = $key;
                                
                                $tmpStatInfo = GetStatDetails($rawName);

                                if ($tmpStatInfo["name"] != "Speed" &&
                                    $tmpStatInfo["name"] != "SP" &&
                                    $value != null &&
                                    $rawName != "Factors" &&
                                    $rawName != "PrimaryStat" &&
                                    ($actor->SimulationRole != "Tank" || ($actor->SimulationRole == "Tank" && $rawName != "Stamina"))
                                   )
                                {
                                    $statsCount++;
                                    $statInfo = GetStatDetails($rawName);

                                    if ($statInfo["group"] > 0)
                                    {
                                        $oph = "<th>" . $statInfo["name"] . "</th>";
                                        $opr = "<td>" . number_format((float)$value, 2, ".", "") . "</td>";
                                        $onr = "<td>" . number_format((((float)$value) / ((float)$primaryStatValue)), 2, ".", "") . "</td>";

                                        if ($statInfo["group"] == 1)
                                        {
                                            $outputheader1 .= $oph;
                                            $outputrow1 .= $opr;
                                            $normalizedrow1 .= $onr;
                                        }
                                        elseif ($statInfo["group"] == 2)
                                        {
                                            //Get weapons in right order
                                            $outputheader2 = $oph . $outputheader2;
                                            $outputrow2 = $opr . $outputrow2;
                                            $normalizedrow2 = $onr . $normalizedrow2;
                                        }
                                        elseif ($statInfo["group"] == 3)
                                        {
                                            $outputheader3 .= $oph;
                                            $outputrow3 .= $opr;
                                            $normalizedrow3 .= $onr;
                                        }
                                    }
                                }
                            }

                            $outputrowsCombined .= "<tr style=\"text-align: center\"><th><span style=\"margin-right: 10px;\">";
                            $normalizedrowsCombined .= "<tr style=\"text-align: center\"><th><span style=\"margin-right: 10px;\">";
                        
                            if ($simulation->CustomProfile)
                            {
                                $outputrowsCombined .= $actor->CustomActorName;
                                $normalizedrowsCombined .= $actor->CustomActorName;
                            }
                            else
                            {
                                $outputrowsCombined .= $actor->CharacterName;
                                $normalizedrowsCombined .= $actor->CharacterName;

                                if ($actorCount > 1)
                                {
                                $outputrowsCombined .= "_" . $actorCount;
                                $normalizedrowsCombined .= "_" . $actorCount;
                                }
                            }

                            $outputrowsCombined .= "</span></th>" . $outputrow1 . $outputrow2 . $outputrow3 . "</tr>"; 
                            $normalizedrowsCombined .= "</span></th>" . $normalizedrow1 . $normalizedrow2 . $normalizedrow3 . "</tr>"; 
                        }
                        
                        $resultsRow .= $outputrow1 . $outputrow2 . $outputrow3 . $normalizedrow1 . $normalizedrow2 . $normalizedrow3;
                        
                        $resultsRow .= "</tr>\n";
                    }

                }
            }
            
            echo "<table class=\"table table-bordered table-hover table-striped table-stat-weights\" id=\"damageTable\">
                                <caption>Talents and Results</caption>
                                <thead>
                                    <tr>";

            echo $resultsHeader0 . $outputheader1 . $outputheader2 . $outputheader3 . $outputheader1 . $outputheader2 . $outputheader3 . "</tr></thead>" . $resultsRow;

            echo "</table>";

            ?>
					</div>
				</div>
			</div>
            
           	<script type="text/javascript">
           	$(document).ready(function() {
				$('[data-toggle="tooltip"]').tooltip();
                updateDateTimeSpans();
               
                $("#damageTable").DataTable({
                    "dom": '',
                    "order": [[ 9, "desc" ]],
                    "columns": [
                        null,
                        null
                        <?php for ($x = 0; $x < 7; $x++) { print ",null"; } ?>
                        <?php for ($x = 3; $x <= $damageTableCols; $x++) { print ",null"; } ?>,
                        null,
                        null,
                        null
                        <?php
                        if ($batch->Simulations[0]->ScaleFactors == 1)
                        {
                            for ($x = 0; $x < $statsCount+1; $x++) { print ",null,null"; }
                        }
                        ?>
                        ],
                    "autoWidth": true,
                    "paging": false
                });
                
                $("#damageTable thead th").each(function(x){
                    if (x > 1) {
                        $(this).width($(this).width()+25);
                    }
                });
                
                
                $("#simulationsTable").DataTable({
                    "dom": '',
                    "order": [[ 1, "asc" ]],    
                    "autoWidth": true,
                    "paging": false
                });
                
                $("#simulationsTable thead th").each(function(x){
                    if (x > 1) {
                        $(this).width($(this).width()+25);
                    }
                });
                
                updateDateTimeSpans();
           	});
           	</script>
<?php
			}
			
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
