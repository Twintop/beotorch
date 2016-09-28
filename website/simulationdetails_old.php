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

$pageTitle = "Simulation Details";
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';

?>
            <div style="float: left">
	            <h2>Simulation Details</h2><div class="alert alert-short alert-warning">Beotorch uses bleeding edge, nightly builds of <a href="http://github.com/simulationcraft/simc" target="_blank">SimulationCraft</a> for its simulations. If you find any bugs within your results, something fails, or if anything in general just feels "off", <a href="https://github.com/simulationcraft/simc/issues" target="_blank">please open a new issue on the SimulationCraft GitHub</a>!</div>
	        </div>		       
		    <div class="panel panel-default" style="width: 478px; float: right; position: relative; right: 1px; padding-top: 5px; padding-left: 5px; padding-right: 5px;">

			</div>
			<div style="clear: both;"></div>
			
			<?php
			
			$validSim = true;
			
			if (!isset($_GET['r']))
			{
				$validSim = false;
			}
			
			if ($validSim)
			{
				$simulation = $SimulationRepository->SimulationGetByGUID($_GET['r'], 1, 1);
				
				if ($simulation == false || $simulation->SimulationGUID != $_GET['r'])
				{
					$validSim = false;
				}
			}
			
			if (!$validSim)
			{
				?>
				
				        <div class="panel panel-default">
							<div class="panel-body">
								<div>
								<div class="alert alert-short alert-danger">You did not supply a valid report to load.</div>
								</div>
							</div>
						</div>
				
				<?php
			}
			else
			{
			?>
			
            		<table class="table table-striped display" id="simulationsTable">
    	        		<tbody>
                            <tr>
    	     <?php  
             
                if ($simulation->UserId == $User->UserId || $User->UserLevelId == 9)
                {
                    ?>
                            <td style="width: 40px;">
                                <div id="simulationHidden-<?php echo $simulation->SimulationGUID; ?>" data-toggle="tooltip" data-placement="right" class="simulationIcon glyphicon glyphicon-eye-<?php
                                    if($simulation->IsHidden)
                                    {
                                        echo "close simulationIconEnabledRed";
                                    }
                                    else
                                    {
                                        echo "open simulationIconEnabledGreen";
                                    }
                                    
                                    if ($User->HiddenSimulations || $User->UserLevelId == 9)
                                    {
                                        if ($simulation->IsHidden)
                                        {
                                            echo " simulationIconInteractable\" title=\"This is a hidden simulation. Click here to make it public.";
                                        }
                                        else
                                        {
                                            echo " simulationIconInteractable\" title=\"This is a public simulation. Click here to make it hidden.";
                                        }
                                        ?>" onclick="simulationToggleHidden('<?php echo $simulation->SimulationGUID; ?>')<?php
                                    }
                                    else
                                    {
                                        if ($simulation->IsHidden)
                                        {
                                            echo " simulationIconTooltip\" title=\"This is a hidden simulation.";
                                        }
                                        else
                                        {
                                            echo " simulationIconTooltip\" title=\"This is a public simulation.";
                                        } 
                                    }
                                    ?>">
                                </div>
                    <?php
                    if ($simulation->SimulationStatusId >= 3)
                    {
                        if (!$simulation->ReportArchived)
                        {
                            ?>

                                <div id="simulationArchiveReport-<?php echo $simulation->SimulationGUID; ?>" data-html="true" data-toggle="tooltip" data-placement="right" class="simulationIcon simulationIconInteractable glyphicon glyphicon-floppy-remove" title="Archive this simulation's HTML report." onclick="simulationArchiveReport('<?php echo $simulation->SimulationGUID; ?>')">
                                </div>

                        <?php
                        }
                        else
                        {
                            ?>
                                <div id="simulationArchiveReport-<?php echo $simulation->SimulationGUID; ?>" data-html="true" data-toggle="tooltip" data-placement="right" class="simulationIcon simulationIconTooltip glyphicon glyphicon-floppy-saved" title="This simulation's HTML report was archived at <span class='dateTimeSpan'><?php echo $simulation->TimeArchived; ?></span>.">
                                </div>
                            <?php
                        }
                    
                        if (!$simulation->SimulationArchived)
                        {
                            ?>

                                <div id="simulationArchive-<?php echo $simulation->SimulationGUID; ?>" data-html="true" data-toggle="tooltip" data-placement="right" class="simulationIcon simulationIconInteractable glyphicon glyphicon-remove" title="Archive this simulation and remove it from being displayed under 'Your Simulations'. This will also archive the HTML report." onclick="simulationArchive('<?php echo $simulation->SimulationGUID; ?>')">
                                </div>

                        <?php
                        }
                        else
                        {
                            ?>
                                <div id="simulationArchive-<?php echo $simulation->SimulationGUID; ?>" data-html="true" data-toggle="tooltip" data-placement="right" class="simulationIcon simulationIconTooltip glyphicon glyphicon-remove simulationIconEnabledRed" title="This simulation has been archived and is not displayed under 'Your Simulations'. This simulation was archived at <?php echo $simulation->SimulationTimeArchived; ?> UTC.">
                                </div>
                            <?php
                        }
                    }
                    echo "</td>";
                }
             
                $armoryURL = "http://" . $simulation->SimulationActors[0]->RegionURL . $simulation->SimulationActors[0]->ServerName . "/" . $simulation->SimulationActors[0]->CharacterName . "/advanced";

                $talentsURL = "http://us.battle.net/wow/en/tool/talent-calculator#" . $simulation->SimulationActors[0]->CalcClass . $simulation->SimulationActors[0]->CalcSpec . "a!" . $simulation->SimulationActors[0]->CalcTalent . "!" . $simulation->SimulationActors[0]->CalcGlyph;
                
                if ($simulation->CustomProfile)
                {
                    $talentsURL = "http://us.battle.net/wow/en/tool/talent-calculator#" . $simulation->SimulationActors[0]->CalcClass . $simulation->SimulationActors[0]->CalcSpec . "a!" . $simulation->SimulationActors[0]->CalcTalent . "!" . $simulation->SimulationActors[0]->CalcGlyph;

                    echo "<td style=\"width: 450px; max-width: 450px;\">";

                    echo "<div class=\"simulationTableName\">Custom Profile Simulation";
                    
                    if ($simulation->GameVersion == "6.2.4")
                    {
                        echo " <span style=\"cursor: help;\" class=\"label label-as-badge label-wod\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Simmed during Warlords of Draenor, Patch 6.2.4\">WoD</span>";
                    }

                    if ($simulation->IsHidden == 1 && !($simulation->UserId == $User->UserId || $User->UserLevelId == 9))
                    {
                        echo " <span style=\"cursor: help;\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"This is a hidden simulation.\" class=\"label label-info3\"><span class=\"glyphicon glyphicon-eye-close\"></span></span>";
                    }

                    echo "</div><div style=\"clear: both;\">";

                    if ($simulation->SimulationName != "" && $simulation->UserId == $User->UserId)
                    {
                        echo "<div class=\"simulationFriendlyName\">" . $simulation->SimulationName . "</div><div style=\"clear: both;\"></div>";
                    }

                    if ($simulation->ActorCount == 1)
                    {
                        if ($simulation->SimulationActors[0]->Level > 0)
                        {
                            echo "<span style=\"cursor: help; padding-right: 5px;\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Character Level\">" . $simulation->SimulationActors[0]->Level . "</span>";
                        }

                        if ($simulation->SimulationActors[0]->RaceId > 0)
                        {
                            echo "<span style=\"color: #" . $simulation->SimulationActors[0]->FactionColor . ";\">" . $simulation->SimulationActors[0]->RaceName . "</span> ";
                        }

                        if ($simulation->SimulationActors[0]->ClassId > 0)
                        {
                            echo "<span style=\"color: #" . $simulation->SimulationActors[0]->ClassColor . ";\">";

                            if ($simulation->SimulationActors[0]->SpecializationId > 0)
                            {
                                echo $simulation->SimulationActors[0]->SpecializationName . " ";
                            }

                            echo $simulation->SimulationActors[0]->ClassName . "</span>";
                        }

                        echo " <span title=\"Character role used for the simulation.\" data-toggle=\"tooltip\" data-placement=\"top\" style=\"cursor: help;\" class=\"badge\">" . $simulation->SimulationActors[0]->SimulationRole . "</span>";

                        if ($simulation->SimulationActors[0]->ItemLevel > 0)
                        {
                            echo " <span title=\"Average Equipped Item Level\" data-toggle=\"tooltip\" data-placement=\"top\" style=\"cursor: help;\" class=\"badge\">" . $simulation->SimulationActors[0]->ItemLevel . "</span>";
                        }

                        if ($simulation->SimulationActors[0]->SimTalent != null)
                        {
                            if ($simulation->SimulationActors[0]->ClassName == "Death Knight")
                            {
                                $talentLevels = [56, 57, 58, 60, 75, 90, 100];
                            }
                            elseif ($simulation->SimulationActors[0]->ClassName == "Demon Hunter")
                            {
                                $talentLevels = [99, 100, 102, 104, 106, 108, 110];                            
                            }
                            else
                            {
                                $talentLevels = [15, 30, 45, 60, 75, 90, 100];
                            }

                            echo "<br />";
                            for ($y = 0; $y < 7; $y++)
                            {
                                if ($simulation->SimulationActors[0]->Talents[$y]->TalentId == null)
                                {
                                    echo "<img src=\"http://media.blizzard.com/wow/icons/18/inv_misc_questionmark.jpg\" title=\"Level " . $talentLevels[$y] . ": No Talent\" class=\"simTableTalentIcon\" data-toggle=\"tooltip\" data-placement=\"top\" />";
                                }
                                else
                                {
                                    echo "<img src=\"http://media.blizzard.com/wow/icons/18/" . $simulation->SimulationActors[0]->Talents[$y]->Icon . ".jpg\" title=\"Level " . $talentLevels[$y] . ": " . $simulation->SimulationActors[0]->Talents[$y]->Name . "\" class=\"simTableTalentIcon\" data-toggle=\"tooltip\" data-placement=\"top\" />";
                                }
                            }
                        }

                        if ($simulation->SimulationActors[0]->CalcTalent)
                        {
                            echo "<br /><a href=\"" . $talentsURL . "\" target=\"_blank\">Talent Calculator</a>";
                        }
                    }
                    elseif ($simulation->ActorCount > 1)
                    {
                        echo $simulation->ActorCount . " Profiles Compared";
                    }
                }
                else
                {             
                    echo "<td style=\"width: 450px; max-width: 450px;\"><div style=\"float: left; padding-right: 5px;\"><a href=\"" . SITE_ADDRESS . "simulationdetails.php?r=" . $simulation->SimulationGUID . "\"><img src=\"" . $simulation->SimulationActors[0]->RegionThumbnailURL . $simulation->SimulationActors[0]->ThumbnailURL . "?alt=wow/static/images/2d/avatar/" . $simulation->SimulationActors[0]->RaceId . "-" . $simulation->SimulationActors[0]->Gender . ".jpg\" style=\"border: solid 1px black\" /></a></div>
                        <div style=\"float: left; width: 330px;\">";

                    echo "<div class=\"simulationTableName\"><a href=\"" . SITE_ADDRESS . "simulationdetails.php?r=" . $simulation->SimulationGUID . "\">" . $simulation->SimulationActors[0]->CharacterName . "-" . $simulation->SimulationActors[0]->ServerName . "</a> <span style=\"cursor: help;\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Character Region: " . $simulation->SimulationActors[0]->RegionName . "\" class=\"label label-info2 label-as-badge\">" . strtoupper($simulation->SimulationActors[0]->RegionPrefix) . "</span>";

                    if ($simulation->GameVersion == "6.2.4")
                    {
                        echo " <span style=\"cursor: help;\" class=\"label label-as-badge label-wod\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Simmed during Warlords of Draenor, Patch 6.2.4\">WoD</span>";
                    }
                    
                    if ($simulation->IsHidden == 1 && !($simulation->UserId == $User->UserId || $User->UserLevelId == 9))
                    {
                        echo " <span style=\"cursor: help;\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"This is a hidden simulation.\" class=\"label label-info3\"><span class=\"glyphicon glyphicon-eye-close\"></span></span>";
                    }

                    echo "</div><div style=\"clear: both;\">";

                    if ($simulation->SimulationName != "" && $simulation->UserId == $User->UserId)
                    {
                        echo "<div class=\"simulationFriendlyName\">" . $simulation->SimulationName . "</div><div style=\"clear: both;\"></div>";
                    }

                    if ($simulation->SimulationActors[0]->Level > 0)
                    {
                        echo "<span style=\"cursor: help; padding-right: 5px;\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Character Level\">" . $simulation->SimulationActors[0]->Level . "</span>";
                    }

                    if ($simulation->SimulationActors[0]->RaceId > 0)
                    {
                        echo "<span style=\"color: #" . $simulation->SimulationActors[0]->FactionColor . ";\">" . $simulation->SimulationActors[0]->RaceName . "</span> ";
                    }

                    if ($simulation->SimulationActors[0]->ClassId > 0)
                    {
                        echo "<span style=\"color: #" . $simulation->SimulationActors[0]->ClassColor . ";\">";

                        if ($simulation->SimulationActors[0]->SpecializationId > 0)
                        {
                            echo $simulation->SimulationActors[0]->SpecializationName . " ";
                        }

                        echo $simulation->SimulationActors[0]->ClassName . "</span>";
                    }

                    echo " <span title=\"Character role used for the simulation.\" data-toggle=\"tooltip\" data-placement=\"top\" style=\"cursor: help;\" class=\"badge\">" . $simulation->SimulationActors[0]->SimulationRole . "</span>";

                    if ($simulation->SimulationActors[0]->ItemLevel > 0)
                    {
                        echo " <span title=\"Average Equipped Item Level\" data-toggle=\"tooltip\" data-placement=\"top\" style=\"cursor: help;\" class=\"badge\">" . $simulation->SimulationActors[0]->ItemLevel . "</span>";
                    }

                    echo "<br /><a href=\"" . $armoryURL . "\" target=\"_blank\">View Armory</a>";

                    if ($simulation->SimulationActors[0]->CalcTalent && $simulation->ActorCount == 1)
                    {
                        echo " • <a href=\"" . $talentsURL . "\" target=\"_blank\">Talent Calculator</a>";
                    }

                    echo "</div></div></td>";
                }

                $variance = $simulation->SimulationLengthVariance * 100;
                $durationLow = $simulation->SimulationLength - ($simulation->SimulationLength * $simulation->SimulationLengthVariance);
                $durationHigh = $simulation->SimulationLength + ($simulation->SimulationLength * $simulation->SimulationLengthVariance);

                echo "<td style=\"width: 225px; max-width: 225px;\">
                        <span style=\"font-size: 12px;\">Updated @ <span class=\"dateTimeSpan\">" . $simulation->SimulationLogTime . "+00:00</span></span>
                        <div class=\"progress simulations_progress\" title=\"" . $simulation->StatusDescription . "\" data-toggle=\"tooltip\" data-placement=\"top\">";

                switch ($simulation->SimulationStatusId)
                {
                    case 1:
                        echo "<div class=\"progress-bar progress-bar-striped progress-bar-warning active simulations_progress-bar\" role=\"progressbar\" style=\"width: 33%; font-weight: bold;\">" . $simulation->StatusName . "</div>";
                        break;
                    case 2:
                        echo "<div class=\"progress-bar progress-bar-striped progress-bar-info active simulations_progress-bar\" role=\"progressbar\" style=\"width: 66%; font-weight: bold;\">" . $simulation->StatusName . "</div>";
                        break;
                    case 3:
                        echo "<div class=\"progress-bar progress-bar-striped progress-bar-success simulations_progress-bar\" role=\"progressbar\" style=\"width: 100%; font-weight: bold;\">Simulation Complete</div>";
                        break;
                    case 4:
                    default:
                        echo "<div class=\"progress-bar progress-bar-striped progress-bar-danger simulations_progress-bar\" role=\"progressbar\" style=\"width: 100%; font-weight: bold;\">" . $simulation->StatusName . "</div>";
                        break;
                }

                echo "</div>";


                if ($simulation->QueuePosition != null)
                {
                    echo "<b>" . $simulation->QueuePosition . " of " . $simulation->TotalQueue ."</b> in queue";
                }

                echo "</td>";
                echo "<td style=\"min-width: 200px;\"><span title=\"" . $simulation->SimulationTypeDescription . "\" data-toggle=\"tooltip\" data-placement=\"top\" style=\"cursor: help;\">" . $simulation->SimulationTypeFriendlyName . " (" . $simulation->BossCount . " Target";

                if ($simulation->BossCount > 1)
                {
                    echo "s";
                }

                echo ")</span><br />" . number_format($simulation->Iterations, 0, ".", ",") . " iterations<br />";

                if ($simulation->SimulationActors[0]->SimulationRole == "Tank")
                {
                    echo "<span title=\"For tanking simulations, the standard boss difficulty and window for TMI spike damage.\" data-toggle=\"tooltip\" data-placement=\"top\" style=\"cursor: help;\">TMI: " . $simulation->TMIBoss . " @ " . $simulation->TMIWindow . " seconds</span><br />";
                }

                echo "<span title=\"" . $simulation->SimulationLength . " seconds ± " . number_format(($simulation->SimulationLengthVariance * 100), 0, ".", ",") . "%\" data-toggle=\"tooltip\" data-placement=\"top\" style=\"cursor: help;\">Duration: ";

                if ($simulation->SimulationLengthVariance == null || $simulation->SimulationLengthVariance == 0)
                {
                    $minutes = floor($simulation->SimulationLength / 60);
                    $seconds = $simulation->SimulationLength % 60;

                    $durationOutput = $minutes . ":";

                    if ($seconds < 10)
                    {
                        $durationOutput .= "0";
                    }

                    $durationOutput .= $seconds;
                }
                else
                {
                    $durationLow = $simulation->SimulationLength - round($simulation->SimulationLength * $simulation->SimulationLengthVariance);
                    $durationHigh = $simulation->SimulationLength + round($simulation->SimulationLength * $simulation->SimulationLengthVariance);

                    $minutesLow = floor($durationLow / 60);
                    $secondsLow = $durationLow % 60;
                    $minutesHigh = floor($durationHigh / 60);
                    $secondsHigh = $durationHigh % 60;

                    $durationOutput = $minutesLow . ":";

                    if ($secondsLow < 10) {
                        $durationOutput .= "0";
                    }

                    $durationOutput .= $secondsLow . " - " . $minutesHigh . ":";

                    if ($secondsHigh < 10) {
                        $durationOutput .= "0";
                    }

                    $durationOutput .= $secondsHigh;
                }

                echo $durationOutput . "</span><br /><span>Scale Factors? ";

                if ($simulation->ScaleFactors == 1)
                {
                    echo "Yes";
                }
                else
                {
                    echo "No";
                }

                echo "</span></td>";

                echo "</td>
                    </tr>";
			?>
				</tbody>       		
           	</table>
           	
            <?php
           	
           	if ($simulation->SimulationStatusId == 3 && $simulation->ActorCount > 0)
           	{
           	
           	?>
           	
			<div class="panel panel-default">
				<div class="panel-heading"><a data-toggle="collapse" href="#SimulationResults">Simulation Results</a></div>
				<div id="SimulationResults" class="panel-collapse collapse in" style="padding-left: 15px;">
					<div>
                        <?php
                        

                    $outputheader0 = "<th>&nbsp;</th>";	   
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

                    $resultsHeader0 = "<th>&nbsp;</th>\n<th class=\"text-center\">Talents</th>\n";

                    $resultsRow = "";

                    if ($simulation->SimulationActors[0]->SimulationRole == "Tank")
                    {
                        $resultsHeader0 .= "<th class=\"text-center\">TMI</th>\n<th class=\"text-center\">DPS</th>\n<th class=\"text-center\">DTPS</th>\n<th class=\"text-center\">HPS (APS)</th>\n";
                    }
                    else
                    {
                        $resultsHeader0 .= "<th class=\"text-center\">DPS</th>\n";
                    }

                    if ($simulation->SimulationActors[0]->SimTalent != null)
                    {
                        if ($simulation->SimulationActors[0]->ClassName == "Death Knight")
                        {
                            $talentLevels = [56, 57, 58, 60, 75, 90, 100];
                        }
                        elseif ($simulation->SimulationActors[0]->ClassName == "Demon Hunter")
                        {
                            $talentLevels = [99, 100, 102, 104, 106, 108, 110];                            
                        }
                        else
                        {
                            $talentLevels = [15, 30, 45, 60, 75, 90, 100];
                        }
                    }

                    foreach ($simulation->SimulationActors as $actor)
                    {
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

                        $resultsRow .= "</a></th>\n<td>";

                        for ($y = 0; $y < 7; $y++)
                        {
                            if ($actor->Talents[$y]->TalentId == null)
                            {
                                $resultsRow .= "<img src=\"http://media.blizzard.com/wow/icons/18/inv_misc_questionmark.jpg\" title=\"Level " . $talentLevels[$y] . ": No Talent\" class=\"simTableTalentIcon\" data-toggle=\"tooltip\" data-placement=\"top\" />";
                            }
                            else
                            {
                                $resultsRow .= "<img src=\"http://media.blizzard.com/wow/icons/18/" . $actor->Talents[$y]->Icon . ".jpg\" title=\"Level " . $talentLevels[$y] . ": " . $actor->Talents[$y]->Name . "\" class=\"simTableTalentIcon\" data-toggle=\"tooltip\" data-placement=\"top\" />";
                            }
                        }

                        $resultsRow .= "</td>";

                        if ($actor->SimulationRole == "Tank")
                        {
                            $resultsRow .= "<td>" . $actor->TMI . "</td>\n<td>" . $actor->DPS . "</td>\n<td>" . $actor->DTPS . "</td>\n<td>" . $actor->HPS . " (" . $actor->APS . ")</td>\n";
                        }
                        else
                        {
                            $resultsRow .= "<td>" . $actor->DPS . "</th>\n";
                        }

                        $resultsRow .= "</tr>\n";
                            
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

                                $outputheader1 = "<th class=\"text-center\">" . $primaryStatInfo["name"] . "</th>";
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

                                if ($value != null &&
                                    $rawName != "Factors" &&
                                    $rawName != "PrimaryStat" &&
                                    ($actor->SimulationRole != "Tank" || ($actor->SimulationRole == "Tank" && $rawName != "Stamina"))
                                   )
                                {
                                    $statInfo = GetStatDetails($rawName);

                                    if ($statInfo["group"] > 0)
                                    {
                                        $oph = "<th class=\"text-center\">" . $statInfo["name"] . "</th>";
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
                    }

                    echo "<table class=\"table table-bordered table-hover table-striped table-stat-weights\">
                                <caption>Talents and Results</caption>
                                <thead>
                                    <tr>";

                    echo $resultsHeader0 . "</tr></thead>" . $resultsRow;

                    echo "</table>";

                    if ($simulation->ScaleFactors == 1)
                    { 
                        echo "<table class=\"table table-bordered table-hover table-striped table-stat-weights\">
                                    <caption>Scaling Factors / Stat Weights</caption>
                                    <thead>
                                        <tr>";

                        echo $outputheader0 . $outputheader1 . $outputheader2 . $outputheader3;

                        echo "</tr>\n</thead>\n";
                        echo $outputrowsCombined;

                        echo "<tr><th colspan=\"42\" class=\"text-center\">Normalized</th></tr>";

                        echo "<tr>";

                        echo $outputheader0 . $outputheader1 . $outputheader2 . $outputheader3;

                        echo "</tr>\n";

                        echo $normalizedrowsCombined;


                        echo "\n</table>\n";
                    }
                        
                        ?>
					</div>
				</div>
			</div>
           	
           	<?php
           	}
           	
           	if ($simulation->ReportArchived == 0 && $simulation->SimulationStatusId == 3)
           	{
           	
           	?>
           	
			<div class="panel panel-default">
				<div class="panel-heading"><a data-toggle="collapse" href="#SimulationResultsReport" onclick="adjustReportDimensions('reportiframe');">SimulationCraft HTML Results Report</a></div>
				<div id="SimulationResultsReport" class="panel-collapse collapse">
					<div>
						<iframe id="reportiframe" src="<?php echo SITE_ADDRESS . '/warehouse/' . $simulation->SimulationGUID . '.html'; ?>" style="width: 100%;"></iframe>
					</div>
				</div>
			</div>
           	
           	<?php
           	}
           	
           	if ($simulation->SimulationRawLog != null)
           	{
           	?>
           	
           	<div style="clear: both;"></div>
			<div class="panel panel-default">
				<div class="panel-heading"><a data-toggle="collapse" href="#SimulationRawLog" onclick="adjustReportDimensions('reportrawlog');">Raw Simulation Output Log</a></div>
				<div id="SimulationRawLog" class="panel-collapse collapse">
					<div>
					<pre id="reportrawlog"><?php echo $simulation->SimulationRawLog; ?>
					</pre>
					</div>
				</div>
			</div>
           	
           	<?php
           	}
            
            if ($simulation->SimulationActors[0] && $simulation->SimulationActors[0]->CharacterJSON)
            {
           	?>
           	
           	<div style="clear: both;"></div>           	
			<div class="panel panel-default">
				<div class="panel-heading"><a data-toggle="collapse" href="#CharacterJSON" onclick="adjustReportDimensions('reportcharacterjson');">Battle.net API / World of Warcraft Armory Character JSON</a></div>
				<div id="CharacterJSON" class="panel-collapse collapse">
					<div>
                        <pre id="reportcharacterjson" class="preJSON"><?php echo $simulation->SimulationActors[0]->CharacterJSON; ?>
					</pre>
					</div>
				</div>
			</div>
           	 
            <?php
            }
            
            if ($simulation->CustomProfile)
            {
           	?>
           	
           	<div style="clear: both;"></div>           	
			<div class="panel panel-default">
				<div class="panel-heading"><a data-toggle="collapse" href="#CustomProfile" onclick="adjustReportDimensions('customsimulationcraftprofile');">Custom SimulationCraft Profile</a></div>
				<div id="CustomProfile" class="panel-collapse collapse">
					<div>
                        <pre id="customsimulationcraftprofile"><?php echo $simulation->CustomProfile; ?>
					</pre>
					</div>
				</div>
			</div>
           	 
            <?php
            }
            
            ?>
           	<script type="text/javascript">
           	$(document).ready(function() {
				$('[data-toggle="tooltip"]').tooltip();
				
				var characterJSON = $("#reportcharacterjson").text();
				var characterObject = JSON.parse(characterJSON);
				var characterJSONFormatted = JSON.stringify(characterObject, undefined, 4);
				
				$("#reportcharacterjson").html(syntaxHighlight(characterJSONFormatted));
                
                updateDateTimeSpans();
           	});
           	</script>
<?php
			}
			
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
