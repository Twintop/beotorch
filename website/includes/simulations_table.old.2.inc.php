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
			        			<th style="width: 400px; min-width: 400px; max-width: 400px;">Character</th>
			        			<th style="width: 200px; min-width: 200px; max-width: 200px;">Status</th>
			        			<th style="width: 175px; min-width: 175px; max-width: 175px;">Simulation Details</th>
			        			<th>Result</th>
			        		</tr>
    	        		</thead>
    	        		<tfoot>
		        			<tr>
			        			<th style="width: 400px; min-width: 400px;; max-width: 400px;">Character</th>
			        			<th style="width: 200px; min-width: 200px; max-width: 200px;">Status</th>
			        			<th style="width: 175px; min-width: 175px; max-width: 175px;">Simulation Details</th>
			        			<th>Result</th>
			        		</tr>
    	        		</tfoot>
    	        		<tbody>
    	     <?php  
                /**
                * @var Simulation
                */
    	        foreach ($simulations as $simulation)
    	        {
    	        	$armoryURL = "http://" . $simulation->SimulationActors[0]->RegionURL . $simulation->SimulationActors[0]->ServerName . "/" . $simulation->SimulationActors[0]->CharacterName . "/advanced";
    	        	
    	        	$talentsURL = "http://us.battle.net/wow/en/tool/talent-calculator#" . $simulation->SimulationActors[0]->CalcClass . $simulation->SimulationActors[0]->CalcSpec . "a!" . $simulation->SimulationActors[0]->CalcTalent . "!" . $simulation->SimulationActors[0]->CalcGlyph;
    	        	
    	        	echo "<tr>
    	        			<td><div style=\"float: left; padding-right: 5px;\"><a href=\"" . SITE_ADDRESS . "simulationdetails.php?r=" . $simulation->SimulationGUID . "\"><img src=\"" . $simulation->SimulationActors[0]->RegionThumbnailURL . $simulation->SimulationActors[0]->ThumbnailURL . "?alt=wow/static/images/2d/avatar/" . $simulation->SimulationActors[0]->RaceId . "-" . $simulation->SimulationActors[0]->Gender . ".jpg\" style=\"border: solid 1px black\" /></a></div>
    	        			<div style=\"float: left; width: 330px;\">";
    	        	
    	        	echo "<div class=\"simulationTableName\"><a href=\"" . SITE_ADDRESS . "simulationdetails.php?r=" . $simulation->SimulationGUID . "\">" . $simulation->SimulationActors[0]->CharacterName . "-" . $simulation->SimulationActors[0]->ServerName . "</a> <span style=\"cursor: help;\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Character Region: " . $simulation->SimulationActors[0]->RegionName . "\" class=\"label label-info2 label-as-badge\">" . strtoupper($simulation->SimulationActors[0]->RegionPrefix) . "</span></div><div style=\"clear: both;\">";
    	        			
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
	    	        
	    	        $variance = $simulation->SimulationLengthVariance * 100;
	    	        $durationLow = $simulation->SimulationLength - ($simulation->SimulationLength * $simulation->SimulationLengthVariance);
	    	        $durationHigh = $simulation->SimulationLength + ($simulation->SimulationLength * $simulation->SimulationLengthVariance);
	    	        
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
                        echo "<br />Multiple Talentset Comparison";
                        echo "<br /><a href=\"" . $armoryURL . "\" target=\"_blank\">View Armory</a>";
                    }
					
    	        	echo "</div></div></td>";
    	        	
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
    	        		if ($simulation->SimulationActors[0]->SimulationRole == "Tank" && $simulation->SimulationActors[0]->TMI != null && $simulation->SimulationActors[0]->TMI > 0)
    	        		{
    	        			echo "<b>TMI: </b>" . number_format($simulation->SimulationActors[0]->TMI, 0, ".", ",") . " - ";
    	        		}
    	        		
    	        		if ($simulation->SimulationActors[0]->DPS != null && $simulation->SimulationActors[0]->DPS > 0)
    	        		{
    	        			echo "<b>DPS: </b>" . number_format($simulation->SimulationActors[0]->DPS, 0, ".", ",") . " - ";
    	        		}
    	        		
    	        		if ($simulation->SimulationActors[0]->SimulationRole == "Tank" && $simulation->SimulationActors[0]->DTPS != null && $simulation->SimulationActors[0]->DTPS > 0)
    	        		{
    	        			echo "<b>DTPS: </b>" . number_format($simulation->SimulationActors[0]->DTPS, 0, ".", ",") . " - ";
    	        		}
    	        		
    	        		if ($simulation->SimulationActors[0]->SimulationRole == "Tank" && ($simulation->SimulationActors[0]->HPS != null || $simulation->SimulationActors[0]->APS != null) && ($simulation->SimulationActors[0]->HPS > 0 || $simulation->SimulationActors[0]->APS > 0))
    	        		{
    	        			echo "<b>HPS: </b>" . number_format($simulation->SimulationActors[0]->HPS + $simulation->SimulationActors[0]->APS, 0, ".", ",") . " (<b>APS:</b> " . number_format($simulation->SimulationActors[0]->APS, 0, ".", ",") . ") - ";
    	        		}
    	        	}
    	        	
    	        	echo "<a href=\"" . SITE_ADDRESS . "simulationdetails.php?r=" . $simulation->SimulationGUID . "\">View Simulation Details</a>";  
    	        		
	        		if ($simulation->ReportArchived == 1)
	        		{
	        			echo " (HTML Report Archived)";
	        		}  	        	
    	        	
    	        	if ($simulation->SimulationStatusId == 3)
    	        	{
    	        		if ($simulation->ScaleFactors == 1 && $simulation->ActorCount == 1)
    	        		{    	        				    						
							$outputheader0 = "<th style=\"width: 85px; min-width: 85px; max-width: 85px;\">&nbsp;</th>";//"<th>DPS</th>";	   
							$outputheader1 = "";
							$outputheader2 = "";
							$outputheader3 = "";
			    			$outputrow0 = "<th style=\"text-align: left;\">Scaling Factors</th>";//"<td>" . number_format($simulation->DPS"], 0, ".", "") . "</td>";
			    			$outputrow1 = "";
			    			$outputrow2 = "";
			    			$outputrow3 = "";  
			    			$normalizedrow0 = "<th style=\"text-align: left;\">Normalized</th>";
			    			$normalizedrow1 = "";
			    			$normalizedrow2 = "";
			    			$normalizedrow3 = "";

                            $outputrowsCombined = "";
			    			       
    	        			$primaryStatValue = 1;

                            $actorCount = 0;
                            
                            foreach ($simulation->SimulationActors as $actor)
                            {
                                $actorCount++;
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
                                    
                                $outputrowsCombined .= "<tr style=\"text-align: center\"><th>" . $actor->CharacterName;

                                if ($actorCount > 1)
                                {
                                    $outputrowsCombined .= "_" . $actorCount;
                                }

                                $outputrowsCombined .= "</th>" . $outputrow1 . $outputrow2 . $outputrow3 . "</tr>"; 
                            }
	        				
	        				echo "<br />
			    					<table class=\"table table-hover table-striped table-stat-weights\">
			    						<thead>
				    						<tr>";
				    		
			    			echo $outputheader0 . $outputheader1 . $outputheader2 . $outputheader3;
			    			
			    			echo "</tr>\n</thead>\n";
                            if ($actorCount == 1)
                            {
                                echo "<tr style=\"text-align: center\">";
                            
                                echo $outputrow0 . $outputrow1 . $outputrow2 . $outputrow3 . "</tr>\n";
			    			
                                echo "<tr style=\"text-align: center\">";
			    			
                                echo $normalizedrow0 . $normalizedrow1 . $normalizedrow2 . $normalizedrow3 . "</tr>";
                            }
                            else
                            {
                                echo $outputrowsCombined;
                            }
                            
                            echo "\n</table>\n";
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
    	       		"order": [[ 1, "desc" ]],
    	       		"columns": [
    	       			{ "width": "400px" },
    	       			{ "width": "200px" },
    	       			{ "width": "175px" },
    	       			null
    	       		],
    	       		"oLanguage": {
    	       			"sSearch": "Filter Table:"
    	       		}
           		})
           		.on("draw.dt", function () {
				    $('[data-toggle="tooltip"]').tooltip();
                    updateDateTimeSpans();
           		});
           	} );
           	</script>
