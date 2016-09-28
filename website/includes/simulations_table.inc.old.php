<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>


            <table class="table table-bordered table-hover table-striped display" id="simulationsTable">
                <thead>
                    <tr>
                                <?php
                if ($showControlOptions != 0)
                {
                    echo "<th style=\"width: 35px; min-width: 35px; max-width: 35px;\"></th>";
                }
                ?>
                        <th style="width: 400px; min-width: 400px; max-width: 400px;">Character</th>
                        <th style="width: 200px; min-width: 200px; max-width: 200px;">Status</th>
                        <th style="width: 175px; min-width: 175px; max-width: 175px;">Simulation Details</th>
                        <th style="min-width: 200px;">Result</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                                <?php
                if ($showControlOptions != 0)
                {
                    echo "<th style=\"width: 35px; min-width: 35px; max-width: 35px;\"></th>";
                }
                ?>
                        <th style="width: 400px; min-width: 400px; max-width: 400px;">Character</th>
                        <th style="width: 200px; min-width: 200px; max-width: 200px;">Status</th>
                        <th style="width: 175px; min-width: 175px; max-width: 175px;">Simulation Details</th>
                        <th style="min-width: 200px;">Result</th>
                    </tr>
                </tfoot>
                <tbody>

                <?php
    	        foreach ($simulations as $simulation)
    	        {
                    echo "<tr>";
                    
                    if ($showControlOptions != 0 && $User != null)
                    {
                        echo "<td>";
                        if ($simulation->UserId == $User->UserId || $User->UserLevelId == 9)
                        {
                    ?>
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
                        }
                        echo "</td>";
                    }
                    
                    if ($simulation->CustomProfile)
                    {
                        $talentsURL = "http://us.battle.net/wow/en/tool/talent-calculator#" . $simulation->SimulationActors[0]->CalcClass . $simulation->SimulationActors[0]->CalcSpec . "a!" . $simulation->SimulationActors[0]->CalcTalent . "!" . $simulation->SimulationActors[0]->CalcGlyph;

                        
                        echo "<td>";

                        echo "<div class=\"simulationTableName\"><a href=\"" . SITE_ADDRESS . "simulationdetails.php?r=" . $simulation->SimulationGUID . "\">Custom Profile Simulation</a>";

                        if ($simulation->GameVersion == "6.2.4")
                        {
                            echo " <span style=\"cursor: help;\" class=\"label label-as-badge label-wod\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Simmed during Warlords of Draenor, Patch 6.2.4\">WoD</span>";
                        }
                    
                        if ($simulation->IsHidden == 1 && !($simulation->UserId == $User->UserId || $User->UserLevelId == 9))
                        {
                            echo " <span style=\"cursor: help;\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"This is a hidden simulation.\" class=\"label label-info3\"><span class=\"glyphicon glyphicon-eye-close\"></span></span>";
                        }

                        echo "</div><div style=\"clear: both;\">";

                        if ($simulation->SimulationName != "" & $privacyState == 1)
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
                        $armoryURL = "http://" . $simulation->SimulationActors[0]->RegionURL . $simulation->SimulationActors[0]->ServerName . "/" . $simulation->SimulationActors[0]->CharacterName . "/advanced";

                        $talentsURL = "http://us.battle.net/wow/en/tool/talent-calculator#" . $simulation->SimulationActors[0]->CalcClass . $simulation->SimulationActors[0]->CalcSpec . "a!" . $simulation->SimulationActors[0]->CalcTalent . "!" . $simulation->SimulationActors[0]->CalcGlyph;

                        echo "<td><div style=\"float: left; padding-right: 5px;\"><a href=\"" . SITE_ADDRESS . "simulationdetails.php?r=" . $simulation->SimulationGUID . "\"><img src=\"" . $simulation->SimulationActors[0]->RegionThumbnailURL . $simulation->SimulationActors[0]->ThumbnailURL . "?alt=wow/static/images/2d/avatar/" . $simulation->SimulationActors[0]->RaceId . "-" . $simulation->SimulationActors[0]->Gender . ".jpg\" style=\"border: solid 1px black\" /></a></div>
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

                        if ($simulation->SimulationName != "" & $privacyState == 1)
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

                        if ($simulation->ActorCount == 1)
                        {
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

                            echo "<br /><a href=\"" . $armoryURL . "\" target=\"_blank\">View Armory</a>";

                            if ($simulation->SimulationActors[0]->CalcTalent)
                            {
                                echo " • <a href=\"" . $talentsURL . "\" target=\"_blank\">Talent Calculator</a>";
                            }
                        }
                        else
                        {
                            echo "<br />" . $simulation->ActorCount . " Talentset Comparison";
                            echo "<br /><a href=\"" . $armoryURL . "\" target=\"_blank\">View Armory</a>";
                        }
                    }
					
    	        	echo "</div></div></td>";
    	        	                   
	    	        
	    	        $variance = $simulation->SimulationLengthVariance * 100;
	    	        $durationLow = $simulation->SimulationLength - ($simulation->SimulationLength * $simulation->SimulationLengthVariance);
	    	        $durationHigh = $simulation->SimulationLength + ($simulation->SimulationLength * $simulation->SimulationLengthVariance);
	    	        
                    
    	        	echo "<td>
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
					echo "<td><span title=\"" . $simulation->SimulationTypeDescription . "\" data-toggle=\"tooltip\" data-placement=\"top\" style=\"cursor: help;\">" . $simulation->SimulationTypeFriendlyName . " (" . $simulation->BossCount . " Target";
					
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
					echo "<td>";
    	        	
    	        	if ($simulation->SimulationStatusId == 3)
    	        	{
                        if ($simulation->ActorCount > 1)
                        {
                            if ($simulation->CustomProfile)
                            {
                                if ($simulation->SimulationActors[0]->SimulationRole == "Tank")
                                {
                                    echo "<b><u>Highest TMI Profile</u></b><br />";
                                }
                                else
                                {
                                    echo "<b><u>Highest DPS Profile</u></b><br />";
                                }
                            }
                            else 
                            {
                                if ($simulation->SimulationActors[0]->SimulationRole == "Tank")
                                {
                                    echo "<b><u>Highest TMI Talentset</u></b><br />";
                                }
                                else
                                {
                                    echo "<b><u>Highest DPS Talentset</u></b><br />";
                                }
                            }
                        }
                        
    	        		if ($simulation->SimulationActors[0]->SimulationRole == "Tank" && $simulation->SimulationActors[0]->TMI != null && $simulation->SimulationActors[0]->TMI > 0)
    	        		{
    	        			echo "<b>TMI: </b>" . number_format($simulation->SimulationActors[0]->TMI, 0, ".", ",") . "<br />";
    	        		}
    	        		
    	        		if ($simulation->SimulationActors[0]->DPS != null && $simulation->SimulationActors[0]->DPS > 0)
    	        		{
    	        			echo "<b>DPS: </b>" . number_format($simulation->SimulationActors[0]->DPS, 0, ".", ",") . "<br />";
    	        		}
    	        		
    	        		if ($simulation->SimulationActors[0]->SimulationRole == "Tank" && $simulation->SimulationActors[0]->DTPS != null && $simulation->SimulationActors[0]->DTPS > 0)
    	        		{
    	        			echo "<b>DTPS: </b>" . number_format($simulation->SimulationActors[0]->DTPS, 0, ".", ",") . "<br />";
    	        		}
    	        		
    	        		if ($simulation->SimulationActors[0]->SimulationRole == "Tank" && ($simulation->SimulationActors[0]->HPS != null || $simulation->SimulationActors[0]->APS != null) && ($simulation->SimulationActors[0]->HPS > 0 || $simulation->SimulationActors[0]->APS > 0))
    	        		{
    	        			echo "<b>HPS: </b>" . number_format($simulation->SimulationActors[0]->HPS + $simulation->SimulationActors[0]->APS, 0, ".", ",") . " (<b>APS:</b> " . number_format($simulation->SimulationActors[0]->APS, 0, ".", ",") . ")<br />";
    	        		}
    	        	}
    	        	
    	        	echo "</td>
    	        		</tr>";
    	        }
                ?>
				</tbody>       		
           	</table>
           	
           	<script type="text/javascript">
           	$(document).ready(function() {
           		$("#simulationsTable").DataTable({
	           		"dom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
                    "order": [[ <?php if ($showControlOptions != 0) { echo "2"; } else { echo "1"; } ?>, "desc" ]],
    	       		"columns": [
                        <?php if ($showControlOptions != 0) { echo '{ "width": "40px", "orderable": false },'; } ?>
    	       			{ "width": "400px" },
    	       			{ "width": "200px" },
    	       			{ "width": "175px" },
    	       			null
    	       		],
    	       		"oLanguage": {
    	       			"sSearch": "Filter Table:"
    	       		},
                    "autoWidth": false
           		})
           		.on("draw.dt", function () {
				    $('[data-toggle="tooltip"]').tooltip();
                    updateDateTimeSpans();
           		});
           	} );

            var update_size = function() {
                $("#simulationsTable").css({ width: $("#simulationsTable").parent().width() });
                $("#simulationsTable").DataTable().fnAdjustColumnSizing();  
            }

            $(window).resize(function() {
                clearTimeout(window.refresh_size);
                window.refresh_size = setTimeout(function() { update_size(); }, 250);
            });
           	</script>
