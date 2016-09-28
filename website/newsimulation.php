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
	header('Location: ' . SITE_ADDRESS . '/login.php');
}

$pageTitle = "New Simulation";
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';
?>
    <div style="width: calc(100% - 350px);">
        <h2>New Simulation</h2><div class="alert alert-short alert-warning">Beotorch uses bleeding edge, nightly builds of <a href="http://github.com/simulationcraft/simc" target="_blank">SimulationCraft</a> for its simulations. If you find any bugs within your results, something fails, or if anything in general just feels "off", <a href="https://github.com/simulationcraft/simc/issues" target="_blank">please open a new issue on the SimulationCraft GitHub</a>!</div>
    </div>
    <div style="clear: both">
    
	<form action="newsimulation.php" method="post" name="newsimulation_form" class="form-horizontal"> 
    <div class="panel panel-default panel-col-nonstandard" style="width: 600px; float: left;">
		<div class="panel-heading">Queue a New Simulation</div>
    	<div class="panel-body">   
        <?php
        
        $currentSimQueue = QueuedSimulationCount($mysqli, $_SESSION['user_id']);
        
        if ($currentSimQueue[0]["WeightedQueue"] >= $User->MaxSimQueueSize)
        {	
        	echo "<div class=\"alert alert-short alert-info\">You currently have <b>" . $currentSimQueue[0]["WeightedQueue"] . " of " . $User->MaxSimQueueSize . "</b> simulations in the process queue.</div>";
        	echo "<div class=\"alert alert-short alert-danger\">You have reached the maximum number of queued simulations. You will have to wait until one of your current simulations finishes processing before you can queue another.</div>";
        }
        elseif (isset($_POST['customProfile']) && $_POST['customProfile'])
        {
            if (!$User->CustomProfile)
            {
                echo "<div class=\"alert alert-short alert-danger\">Your account level is not allowed to queue this kind of simulation.</div>\n";
            }
            elseif (strlen($_POST['customProfileText']) == 0)
            {
                echo "<div class=\"alert alert-short alert-danger\">Please enter a custom profile to simulate.</div>\n";
            }
            else
            {
                if (isset($_POST['bosscount']) && $_POST['bosscount'] < 1)
                {
                    $bosscount = 1;
                }
                elseif (isset($_POST['bosscount']) && $_POST['bosscount'] > $User->MaxBossCount)
                {
                    $bosscount = $User->MaxBossCount;
                }
                elseif (isset($_POST['bosscount']))
                {
                    $bosscount = $_POST['bosscount'];
                }
                else
                {
                    $bosscount = 1;
                }  

                if (isset($_POST['iterations']))
                {
                    if ($_POST['iterations'] > $User->MaxIterations)
                    {
                        $_POST['iterations'] = $User->MaxIterations;
                    }
                    elseif ($_POST['iterations'] < 1000)
                    {
                        $_POST['iterations'] = 1000;
                    }
                }
                else
                {
                    $_POST['iterations'] = 10000;
                }

                if (isset($_POST['scaleFactors']) && $_POST['scaleFactors'] == 1)
                {
                    $scaleFactors = 1;
                    $simQueueCost = $bosscount * 5;
                }
                else
                {
                    $scaleFactors = 0;
                    $simQueueCost = $bosscount;
                }

                $simQueueCost = ceil($simQueueCost * (10000 / $_POST['iterations']));

                if ($_POST['fightLength'] > $User->MaxSimLength)
                {
                    $_POST['fightLength'] = $User->MaxSimLength;
                }
                elseif ($_POST['fightLength'] < $User->MinSimLength)
                {
                    $_POST['fightLength'] = $User->MinSimLength;
                }

                $computedVariance = $_POST['fightLengthVariance'] / 100;

                if ($computedVariance > 1.00)
                {
                    $_POST['fightLengthVariance'] = 100;
                    $computedVariance = 1.0;
                }
                elseif ($computedVariance < 0.00)
                {
                    $_POST['fightLengthVariance'] = 0;
                    $computedVariance = 0.00;
                }

                if ($_POST['fightType'] > 7 || $_POST['fightType'] < 1)
                {
                    $_POST['fightType'] = 1;
                }

                if ($_POST['tmiboss'] > 7 || $_POST['tmiboss'] < 1)
                {
                    $_POST['tmiboss'] = 1;
                }

                if ($_POST['tmiwindow'] > 7 || $_POST['tmiwindow'] < 1)
                {
                    $_POST['tmiwindow'] = 6;
                }

                if ($_POST['hiddenSimulation'] && $User->HiddenSimulations && $_POST['hiddenSimulation'] == 1)
                {
                    $hideSimulation = 1;
                }
                else
                {
                    $hideSimulation = 0;
                }
                
                if (!isset($_POST['simcVersion']) || ($_POST['simcVersion'] != "live" && $_POST['simcVersion'] != "ptr" && $_POST['simcVersion'] != "beta"))
                {
                    $simcVersion = "live";
                }
                else
                {
                    $simcVersion = $_POST['simcVersion'];
                }

                $insertResult = $SimulationRepository->QueueSimulation($_POST['iterations'], $_POST['fightType'], $scaleFactors, $_POST['fightLength'], $computedVariance, $bosscount, $_POST['simulationFriendlyName'], null, null, $hideSimulation, $_POST['customProfileText'], $simcVersion);
                echo "<div class=\"alert alert-short alert-success\">Your custom simulation has been queued!</div>\n";
			}
        }
        else
        {
		    $insertResult = false;
		    $characterCheck = true;
		    
		    $strippedName = "";
		    
		    if (isset($_POST['characterName']))
			{
				$strippedName = preg_replace('/[^\p{L}\p{M}*]/u', '', $_POST['characterName']);
			}
		
			if (isset($_POST['bosscount']) && $_POST['bosscount'] < 1)
			{
				$bosscount = 1;
			}
			elseif (isset($_POST['bosscount']) && $_POST['bosscount'] > $User->MaxBossCount)
			{
				$bosscount = $User->MaxBossCount;
			}
			elseif (isset($_POST['bosscount']))
			{
				$bosscount = $_POST['bosscount'];
			}
			else
			{
				$bosscount = 1;
			}  

            if (isset($_POST['iterations']))
            {
                if ($_POST['iterations'] > $User->MaxIterations)
                {
                    $_POST['iterations'] = $User->MaxIterations;
                }
                elseif ($_POST['iterations'] < 1000)
                {
                    $_POST['iterations'] = 1000;
                }
            }
            else
            {
                $_POST['iterations'] = 10000;
            }
                
            if (!isset($_POST['simcVersion']) || ($_POST['simcVersion'] != "live" && $_POST['simcVersion'] != "ptr" && $_POST['simcVersion'] != "beta"))
            {
                $simcVersion = "live";
            }
            else
            {
                $simcVersion = $_POST['simcVersion'];
            }
            
            if (isset($_POST['talentGUIDs']))
            {
                $talentGUIDs = explode(";", $_POST['talentGUIDs']);
            }
            else
            {
                $talentGUIDs = array();
            }
            
			if (isset($_POST['scaleFactors']) && $_POST['scaleFactors'] == 1)
			{
				$scaleFactors = 1;
				$simQueueCost = $bosscount * 5;
			}
			else
			{
				$scaleFactors = 0;
				$simQueueCost = $bosscount;
			}	
            
            if ($User->MaxActors < count($talentGUIDs))
            {
                $simQueueCost = $simQueueCost * $User->MaxActors; 
            }
            else
            {
                $simQueueCost = $simQueueCost * count($talentGUIDs);
            }
            
            $simQueueCost = ceil($simQueueCost * (10000 / $_POST['iterations']));
            
            if (isset($_POST['talentGUIDs']) && count($talentGUIDs) == 0)
            {
				echo "<div class=\"alert-short alert-danger\">You must select at least one set of talents to simulate.</div>";
			}
			elseif (isset($_POST['talentGUIDs']) && isset($_POST['scaleFactors']) && $_POST['scaleFactors'] == 1 && ($currentSimQueue[0]["WeightedQueue"] + $simQueueCost) > $User->MaxSimQueueSize)
			{
				$freeSlots = $User->MaxSimQueueSize - $currentSimQueue[0]["WeightedQueue"];
				$neededSlots = $simQueueCost - $freeSlots;
				echo "<div class=\"alert-short alert-danger\">Queueing a new simulation with Scale Factors enabled for " . $bosscount . " boss targets and " . $count($talentGUIDs) . " talentsets at " . $_POST['iterations'] . " requires at least <b>" . $simQueueCost . " open queue slots</b>. You only have <b>" . $freeSlots . " open queue slot";
				
				if ($freeSlots > 1)
				{
					echo "s";
				}
				
				echo "</b> available. You will have to wait until <b>" . $neededSlots . " of your current simulations</b> have finished processing before you can queue a simulation with scaling factors enabled.</div>";
			}
		    elseif (isset($_POST['talentGUIDs']) && isset($_POST['server'], $_POST['characterName'], $_POST['fightType'], $_POST['iterations'], $_POST['fightLength'], $_POST['fightLengthVariance']) &&
		    	strlen($_POST['characterName']) >= 2 &&
		    	$strippedName == $_POST['characterName'])
		    {
				$serverId = $_POST['server'];
				$characterName = ucfirst(strtolower($_POST['characterName']));

				if ($_POST['fightLength'] > $User->MaxSimLength)
				{
					$_POST['fightLength'] = $User->MaxSimLength;
				}
				elseif ($_POST['fightLength'] < $User->MinSimLength)
				{
					$_POST['fightLength'] = $User->MinSimLength;
				}

				$computedVariance = $_POST['fightLengthVariance'] / 100;
                
				if ($computedVariance > 1.00)
				{
					$_POST['fightLengthVariance'] = 100;
					$computedVariance = 1.0;
				}
				elseif ($computedVariance < 0.00)
				{
					$_POST['fightLengthVariance'] = 0;
					$computedVariance = 0.00;
				}

				if ($_POST['fightType'] > 7 || $_POST['fightType'] < 1)
				{
					$_POST['fightType'] = 1;
				}

				if ($_POST['tmiboss'] > 7 || $_POST['tmiboss'] < 1)
				{
					$_POST['tmiboss'] = 1;
				}

				if ($_POST['tmiwindow'] > 7 || $_POST['tmiwindow'] < 1)
				{
					$_POST['tmiwindow'] = 6;
				}
                
                if ($_POST['hiddenSimulation'] && $User->HiddenSimulations && $_POST['hiddenSimulation'] == 1)
                {
                    $hideSimulation = 1;
                }
                else
                {
                    $hideSimulation = 0;
                }
						
				$server = ServerGet($mysqli, $serverId);
			
				if ($server == null)
				{
					echo "<div class=\"alert alert-short alert-danger\">There was a problem with your queue request.</div>\n";
				}
				else
				{		
					$savedChararacter_json = CharacterTempStorageGet($mysqli, utf8_encode($characterName), $serverId);
					$character_json = $savedChararacter_json[0]["CharacterJSON"];	
					if (strlen($character_json) == 0)				
					{
						$characterCheck = false;
					}
					else
					{
						$characterCheck = true;
					
						$checkCharacter_json = json_decode($character_json, true);
						
						$simRole = "DPS";
				
                        $activeSpecId = substr($_POST['specializationChoice'], -1);
                        
                        $activeSpec = $checkCharacter_json["talents"][$activeSpecId];

                        if (isset($checkCharacter_json["talents"][$activeSpecId]["selected"]))
                        {
                            $armorySpec = "active";
                        }
                        else
                        {
                            $armorySpec = "inactive";
                        }

                        if ($checkCharacter_json["talents"][$activeSpecId]["spec"]["role"] == "TANK" && $_POST['roleChoice'] == "tank")
                        {
                            $simRole = "Tank";
                        }
						
						if ($simRole == "DPS")
						{
							$tmiWindow = null;
							$tmiBoss = null;
						}
						else
						{
							$tmiWindow = $_POST['tmiwindow'];
							
							switch ($_POST['tmiboss'])
							{
								case 1:
								default:
									$tmiBoss = "T17L";
									break;
								case 2:
									$tmiBoss = "T17N";
									break;							
								case 3:
									$tmiBoss = "T17H";
									break;							
								case 4:
									$tmiBoss = "T17M";
									break;							
								case 5:
									$tmiBoss = "T18N";
									break;							
								case 6:
									$tmiBoss = "T18H";
									break;							
								case 7:
									$tmiBoss = "T18M";
									break;
							}
						}
						
                        $talentsList = GetTalentsList($mysqli, $checkCharacter_json["class"], 0, $checkCharacter_json["talents"][$activeSpecId]["calcSpec"]);
                        
                        $calcTalent = "";
                        $simTalent = "";
						
                        $insertResult = $SimulationRepository->QueueSimulation($_POST['iterations'], $_POST['fightType'], $scaleFactors, $_POST['fightLength'], $computedVariance, $bosscount, $_POST['simulationFriendlyName'], $tmiWindow, $tmiBoss, $hideSimulation, "", $simcVersion);
                                                
                        $queuedTalents = array();
                        
						if ($insertResult)
						{
                            $internalTalentIds = [null, null, null, null, null, null, null];
                            
                            foreach ($talentGUIDs as $GUID)
                            {
                                $simTalent = "";
                                $calcTalent = "";

                                for ($x = 1; $x <= 7; $x++)
                                {
                                    if ($_POST["talent" . $x . "DropDown-" . $GUID . "-value"] == "0")
                                    {
                                        $calcTalent .= ".";
                                        $internalTalentIds[$x-1] = null;
                                    }
                                    else
                                    {
                                        $calcTalent .= "" + ($_POST["talent" . $x . "DropDown-" . $GUID . "-value"] - 1);

                                        for (($y = ($x - 1) * 3); $y < ($x * 3); $y++)
                                        {
                                            if ($_POST["talent" . $x . "DropDown-" . $GUID . "-value"] - 1 == $talentsList[$y]["TalentColumn"])
                                            {
                                                $internalTalentIds[$x-1] = $talentsList[$y]["TalentId"];
                                                break;
                                            }
                                        }
                                    }
                                    $simTalent .= $_POST["talent" . $x . "DropDown-" . $GUID . "-value"];
                                }
                                if (!in_array($calcTalent, $queuedTalents))
                                {
                                    $queuedTalents[] = $calcTalent;
                                    $insertResult2 = $SimulationRepository->SimulationActorInsert($insertResult, $serverId, $characterName, $character_json, $activeSpec, $checkCharacter_json["level"], $checkCharacter_json["items"]["averageItemLevelEquipped"], $checkCharacter_json["class"], $checkCharacter_json["race"], $checkCharacter_json["gender"], $checkCharacter_json["thumbnail"], $armorySpec, $simRole, $calcTalent, $simTalent, $internalTalentIds);
                                }
                            }
                            
							echo "<div class=\"alert alert-short alert-success\">Your simulation for " . $characterName . "-" . $checkCharacter_json["realm"] . " with " . count($queuedTalents) . " talentsets has been queued!</div>\n";
							
							$currentSimQueue[0]["WeightedQueue"] += ceil($bosscount * (1 + 4 * $scaleFactors) * count($queuedTalents) * ($_POST['iterations'] / 10000));
							$currentSimQueue[0]["TotalQueue"]++;
						}
						else
						{
							echo "<div class=\"alert alert-short alert-danger\">There was a problem with your queue request.</div>\n";
						}
					}
				}
			}
	
			if (isset($_POST['characterName']) &&
				(strlen($_POST['characterName']) < 2))
			{
				echo "<div class=\"alert alert-short alert-warning\">Please enter a valid Character Name.</div>\n";
			}
			elseif (!$characterCheck)
			{
				echo "<div class=\"alert alert-short alert-warning\">No character named '" . $strippedName . "' exists on the server '" . $server[0]["ServerName"] . "-" . strtoupper($server[0]["RegionPrefix"]) . "'.</div>\n";
			}
        }
				  
        echo "<div class=\"alert alert-short alert-info\">You currently have <b>" . $currentSimQueue[0]["WeightedQueue"] . " of " . $User->MaxSimQueueSize . "</b> simulations in the process queue.</div>";

        if ($currentSimQueue[0]["WeightedQueue"] == $User->MaxSimQueueSize)
        {
            echo "<div class=\"alert-short alert-danger\">You have reached the maximum number of queued simulations. You will have to wait until one of your current simulations finishes processing before you can queue another.</div>";
        }
        else
        {            	
            if ($insertResult)
            {
                $_POST['characterName'] = "";
                $strippedName = "";
            }

    if ($User->CustomProfile)
    {

    ?>
			<div class="form-group">
				<div class="col-xs-offset-4 col-xs-8">
		    		<span style="float: right; height: 24px; line-height: 24px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="Checking this box will let you enter a custom profile to be simulated instead of using a character from Battle.net."></span>
	    			<label style="width: 350px;">
                        <input id="customProfile" name="customProfile" type="checkbox" value="1" onclick="toggleCustomProfileBox()" /> Use Custom Profile?</label>
	    		</div>
	    	</div>  
			<div class="form-group form-group-character">
				<div class="col-xs-12" style="text-align: center; font-weight: bold;">
	    			Or select an existing server and character.
				</div>
	    	</div>
            <script>
                function toggleCustomProfileBox() {
                    var isChecked = $("#customProfile").is(":checked");
                    
                    if (isChecked) {
                        $(".form-group-character").hide();
                        $("#containerCustomProfile").show();				
                        $("#submitCharacter").prop("disabled", false);
                    }
                    else {
                        $(".form-group-character").show();
                        $("#containerCustomProfile").hide();
                        $("#submitCharacter").prop("disabled", true);
                    }
                }
            </script>
	    <?php
        }
	    
	    $resimCharacters = CharacterList($mysqli, $User->UserId);
	    
	    if (count($resimCharacters) > 0)
	    {
	    ?>	    
			<div class="form-group form-group-character">
				<label class="control-label col-xs-4" for="rerunCharacter">Re-Sim Character:</label>
				<div class="col-xs-8" style="height: 38px; line-height: 38px;">
						<select style="width: 350px;"  id="resimCharacter" name="resimCharacter" class="form-control chosen-select" data-placeholder="Choose an existing character..." onchange="setResimCharacter()" onselect="setResimCharacter()">
							<option value="">Re-Sim Character...</option>
							<?php
			
							$lastServer = 0;
							//$lastRegion = 0;
			
							for ($x = 0; $x < count($resimCharacters); $x++)
							{							
								if ($resimCharacters[$x]["ServerId"] != $lastServer)
								{
									if ($lastServer > 0)
									{
										echo "</optgroup>\n";
									}
									echo "<optgroup label=\"" . $resimCharacters[$x]["ServerName"] . " (" . strtoupper($resimCharacters[$x]["RegionPrefix"]) . " - " . $resimCharacters[$x]["ServerType"] . ")\">\n";
									$lastServer = $resimCharacters[$x]["ServerId"];
								}
							
								echo "<option value=\"" . utf8_decode($resimCharacters[$x]["CharacterName"]) . "," . $resimCharacters[$x]["ServerId"] . "\"";
							
								echo ">" . utf8_decode($resimCharacters[$x]["CharacterName"]) . " (" . $resimCharacters[$x]["ClassName"] . ")</option>\n";
							}
						
							echo "</optgroup>\n";
								   
							?>
						</select>
		    		<span style="float: right; height: 38px; line-height: 38px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="Quickly select a character you have already queued a simulation for in the past."></span>
				</div>
			</div>
			<div class="form-group form-group-character">
				<div class="col-xs-12" style="text-align: center; font-weight: bold;">
	    			Or select a server and character.
				</div>
	    	</div> 
			<?php
			}
			?>
            <div class="form-group-character">
                <div class="form-group">
                    <label class="control-label col-xs-4" for="server">Server:</label>
                    <div class="col-xs-8" style="height: 38px; line-height: 38px;">
                        <span onfocusout="getCharacterArmoryEntry()">
                            <select style="width: 350px;" id="server" name="server" class="form-control chosen-select" data-placeholder="Choose a server...">
                                <?php
                                $servers = ServerList($mysqli);    

                                $lastRegion = 0;

                                for ($x = 0; $x < count($servers); $x++)
                                {
                                    if ($servers[$x]["RegionId"] != $lastRegion)
                                    {
                                        if ($lastRegion > 0)
                                        {
                                            echo "</optgroup>\n";
                                        }
                                        echo "<optgroup label=\"" . $servers[$x]["RegionName"] . "\">\n";
                                        $lastRegion = $servers[$x]["RegionId"];
                                    }
                                    echo "<option value=\"" . $servers[$x]["ServerId"] . "\"";

                                    if (isset($_POST['server']) && $_POST['server'] == $servers[$x]["ServerId"])
                                    {
                                        echo " selected";
                                    }

                                    echo ">" . $servers[$x]["ServerName"] . " (" . strtoupper($servers[$x]["RegionPrefix"]) . " - " . $servers[$x]["ServerType"] . ")</option>\n";
                                }

                                echo "</optgroup>\n";

                                ?>
                            </select>
                        </span>
                        <span style="float: right; height: 38px; line-height: 38px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="The server which has the character you would like to run a simulation on."></span>
                    </div>
                </div>
                <div class="form-group" id="characterNameDiv">
                    <label class="control-label col-xs-4" for="characterName">Character:</label>
                    <div class="col-xs-8">
                        <div class="has-error"><span class="glyphicon glyphicon-remove form-control-feedback" id="characterNameErrorSpan" style="display: none;right: 30px;"></span></div>
                        <div class="has-success"><span class="glyphicon glyphicon-ok form-control-feedback" id="characterNameOkSpan" style="display: none; right: 30px;"></span></div>
                        <div class="has-warning"><span class="glyphicon glyphicon-refresh form-control-feedback spinning" id="characterNameSpinnerSpan" style="display: none; right: 30px;"></span></div>
                        <span style="float: right; height: 38px; line-height: 38px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="The name of the character on the selected server that you would like to simulate."></span>
                        <input style="width: 350px;" id="characterName" name="characterName" type="text" maxlength="20"<?php

                        if (isset($_POST['characterName']) && $strippedName != "")
                        {
                            echo " value=\"" . $strippedName . "\"";
                        }

                        ?> class="form-control" onblur="getCharacterArmoryEntry()" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-xs-4" for="specialization">Specialization:</label>
                    <div class="col-xs-8" id="specializationContainer" style="display: none;">
                        <div class="btn-group" data-toggle="buttons" id="specializationChoiceButtons">
                            <label class="btn btn-primary active" style="min-width: 125px;" for="specialization0">
                                <input type="radio" name="specializationChoice" id="specialization0" value="spec0" onchange="specializationRoleCheck()" checked> <span id="spec0label">Spec0</span><span class="glyphicon glyphicon-ok" id="spec0span" style="display: none;"></span>
                            </label>
                            <label class="btn btn-primary" style="min-width: 125px;" for="specialization1">
                                <input type="radio" name="specializationChoice" id="specialization1" value="spec1" onchange="specializationRoleCheck()"> <span id="spec1label">Spec1</span><span class="glyphicon glyphicon-ok" id="spec1span" style="display: none;"></span>
                            </label>
                            <label class="btn btn-primary" style="min-width: 125px;" for="specialization2">
                                <input type="radio" name="specializationChoice" id="specialization2" value="spec2" onchange="specializationRoleCheck()"> <span id="spec2label">Spec2</span><span class="glyphicon glyphicon-ok" id="spec1span" style="display: none;"></span>
                            </label>
                            <label class="btn btn-primary" style="min-width: 125px;" for="specialization3">
                                <input type="radio" name="specializationChoice" id="specialization3" value="spec3" onchange="specializationRoleCheck()"> <span id="spec3label">Spec3</span><span class="glyphicon glyphicon-ok" id="spec1span" style="display: none;"></span>
                            </label>
                        </div>
                        <span style="float: right; height: 38px; line-height: 38px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="Select which character specialization you wish to simulate, based on the chosen character's Armory."></span>
                    </div>
                    <div class="col-xs-8" id="characterSelectMessageContainer">
                        <div id="characterSelectMessage" class="alert-short">Please enter a character.</div>
                    </div>
                </div>
                <div class="form-group" id="roleContainer" style="display: none;">
                    <label class="control-label col-xs-4" for="roleChoice">Role:</label>
                    <div class="col-xs-8">
                        <div class="btn-group" data-toggle="buttons" id="roleChoiceButtons">
                            <label class="btn btn-primary active" for="role0">
                                <input type="radio" name="roleChoice" id="role0" value="dps" onchange="showTankOptionsCheck()" checked> <span id="role0label">DPS</span><span class="glyphicon glyphicon-ok" id="role0span" style="display: none;"></span>
                            </label>
                            <label class="btn btn-primary" for="role1">
                                <input type="radio" name="roleChoice" id="role1" value="tank" onchange="showTankOptionsCheck()"> <span id="role1label">Tank</span><span class="glyphicon glyphicon-ok" id="role1span" style="display: none;"></span>
                            </label>
                        </div>
                        <span style="float: right; height: 38px; line-height: 38px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="If the character's selected specialization is capable of tanking, select if you want a Tank or DPS simulation to be run."></span>
                    </div>
                </div>
                <div class="form-group" id="talentsContainer" style="display: none; height: 47px;">
                    <label class="control-label col-xs-4" for="talents">Talents:</label>
                    <div class="col-xs-8">
                        <div>
                            <div id="talentChoiceButtonsContainer">
                            </div>                           
                            <div id="talentChoiceAddContainer">
                                <a href="#" class="removeTalentsetSpan" onclick="addTalentset()">
                                    <span class="glyphicon glyphicon-plus"></span> Add talentset to comparison
                                </a>
                            </div>
                            <input type="hidden" name="talentGUIDs" id="talentGUIDs" />    
                        </div>
                        <span style="position: absolute; left: 369px; height: 47px; line-height: 47px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="Click each icon to choose which talent to use during the simulation. The talents that will be used during the simulation. By default the talents selected match the talents selected for each spec from the character's armory.<br /><br />If you'd like to compare different talentsets, click on '+ Add talentset to comparison'. You can compare up to <?php print $User->MaxActors; ?> different talent combinations at a time."></span>
                    </div>
                </div>
                <div id="tankOptionsContainer" style="display: none;">	    	
                    <div class="form-group">
                        <div class="col-xs-12" style="text-align: center; font-weight: bold;">
                            The following fields are tanking specific but optional to change.
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-xs-4" for="tmiwindow">TMI Window:</label>
                        <div class="col-xs-8">
                            <span style="float: right; height: 38px; line-height: 38px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="The timeframe window used to calculate spike damage, in seconds."></span>
                            <div style="width: 350px;" class="sliders_buffer">
                                <input type="number" id="tmiwindow" name="tmiwindow"
                                    data-provide="slider"
                                    data-slider-id="tmiwindowSlider"
                                    data-slider-min="1"
                                    data-slider-max="10"
                                    data-slider-step="1"
                                    data-slider-value="<?php if (isset($_POST['tmiwindow'])) { echo $_POST['tmiwindow']; } else { echo "6"; } ?>"
                                    data-slider-ticks="[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]"
                                    data-slider-ticks-labels='["1", "2", "3", "4", "5", "6", "7", "8", "9", "10"]'
                                    class="form-control" style="width: 100%;" />
                            </div>
                        </div>
                    </div>	
                    <div class="form-group">
                        <label class="control-label col-xs-4" for="tmiboss">TMI Standard Boss:</label>
                        <div class="col-xs-8">
                            <span style="float: right; height: 38px; line-height: 38px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="The 'standard' boss difficulty, as related to available current raid tiers. You can change this to whatever difficulty you'd like (from Tier 17 LFR to Tier 18 Mythic), however, Beotorch automatically selects the one that best matches your item level."></span>
                            <div style="width: 350px;" class="sliders_buffer">
                                <input type="number" id="tmiboss" name="tmiboss"
                                    data-provide="slider"
                                    data-slider-id="tmibossSlider"
                                    data-slider-min="1"
                                    data-slider-max="10"
                                    data-slider-step="1"
                                    data-slider-value="<?php if (isset($_POST['tmiwindow'])) { echo $_POST['tmiwindow']; } else { echo "6"; } ?>"
                                    data-slider-ticks="[1, 2, 3, 4, 5, 6, 7]"
                                    data-slider-ticks-labels='["T17L", "T17N", "T17H", "T17M", "T18N", "T18H", "T18M"]'
                                    class="form-control" style="width: 100%;" />
                            </div>
                        </div>
                    </div>	        	
                </div>
            </div>                
			<div class="form-group">
				<div class="col-xs-12" style="text-align: center; font-weight: bold;">
	    			The following fields are optional to change.
				</div>
	    	</div>
			<div class="form-group">
				<label class="control-label col-xs-4" for="simulationFriendlyName">Simulation Name:</label>
				<div class="col-xs-8">
		    		<span style="float: right; height: 38px; line-height: 38px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="Name this character simulation to help identify what it contains. This is useful for when doing many different simulations to compare different items or talents."></span>
	    			<input style="width: 350px;" id="simulationFriendlyName" name="simulationFriendlyName" type="text" maxlength="255"<?php
	    			
			    	if (isset($_POST['simulationFriendlyName']))
			    	{
			    		echo " value=\"" . $_POST['simulationFriendlyName'] . "\"";
			    	}
	    			
	    			?> class="form-control" />
	    		</div>
	    	</div>
            <?php if ($User->HiddenSimulations)
            {?>
            <div class="form-group">
				<div class="col-xs-offset-4 col-xs-8">
		    		<span style="float: right; height: 24px; line-height: 24px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="Checking this box will prevent this simulation from showing up in the 'Recently Completed User Simulations' table or in the Simulation Browser. It will still be available from 'Your Simulations', and, the Simulation Details will still be available to anyone who has a direct link to the report."></span>
	    			<label style="width: 350px;">
                        <input id="hiddenSimulation" name="hiddenSimulation" type="checkbox" value="1" /> Hide Simulation?</label>
	    		</div>
	    	</div>
            <?php } ?>
			<div class="form-group">
				<label class="control-label col-xs-4" for="fightType">Fight Type:</label>
				<div class="col-xs-8" style="height: 38px; line-height: 38px;">
	    			<select style="width: 350px;" id="fightType" name="fightType" class="form-control">
					    <?php
							$simulationTypes = SimulationTypeList($mysqli);
					
						for ($x = 0; $x < count($simulationTypes); $x++)
					    {
					    	echo "<option value=\"" . $simulationTypes[$x]["SimulationTypeId"] . "\"";
					    	
					    	if (isset($_POST['fightType']) && $_POST['fightType'] == $simulationTypes[$x]["SimulationTypeId"])
					    	{
					    		echo " selected";
					    	}
					    	
					    	echo ">" . $simulationTypes[$x]["SimulationTypeFriendlyName"] . "</option>\n";
					    }         
					    ?>
		    		</select>
		    		<span style="float: right; height: 38px; line-height: 38px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="The kind of encounter to simulate. Patchwerk, Light Movement, and Heavy Movement are the most common. Ultraxion is the least common."></span>
	    		</div>
	    	</div>
			<div class="form-group">
				<label class="control-label col-xs-4" for="bosscount">Bosses:</label>
				<div class="col-xs-8">
		    		<span style="float: right; height: 38px; line-height: 38px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="The number of boss targets to simulate having. Increase this if you want to see how the character will do on duo or council fights. More bosses cost more queue position slots to simulate."></span>
					<div style="width: 350px;" class="sliders_buffer">
						<input type="number" id="bosscount" name="bosscount" data-slider-id="bosscountSlider" data-slider-min="1" data-slider-max="<?php echo $User->MaxBossCount; ?>" <?php
						if (isset($_POST['bosscount']))
						{
							echo 'data-slider-value="' . $_POST['bosscount'] . '"';
						}
						else
						{
							echo 'data-slider-value="1"';
						}
						?> data-slider-step="1" class="form-control" style="width: 100%;" />
	    			</div>
		    	</div>
	    	</div>
			<div class="form-group">
				<label class="control-label col-xs-4" for="iterations">Iterations:</label>
				<div class="col-xs-8">
		    		<span style="float: right; height: 38px; line-height: 38px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="The number of times the fight will be simulated. Higher iterations will give more accurate results, while lower iterations will allow for simulations to be processed more quickly. The more iterations you choose to run, the more queue slots the simulation will cost in the queue."></span>
					<div style="width: 350px;" class="sliders_buffer">
						<input type="number" id="iterations" name="iterations" data-slider-id="iterationsSlider" data-slider-min="1000" data-slider-max="<?php echo $User->MaxIterations; ?>" <?php
						if (isset($_POST['iterations']))
						{
							echo 'data-slider-value="' . $_POST['iterations'] . '"';
						}
						elseif ($User->MaxIterations < 10000)
						{
							echo 'data-slider-value="' . $User->MaxIterations . '"';
						}
						else
						{
							echo 'data-slider-value="10000"';
						}
						?> data-slider-step="250" class="form-control" style="width: 100%;" />
					</div>
	    		</div>
	    	</div>
			<div class="form-group">
				<label class="control-label col-xs-4" for="fightLength">Fight Length:</label>
				<div class="col-xs-8">
		    		<span style="float: right; height: 38px; line-height: 38px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="The duration of the fight in the simulation, on average. Below the slider shows the simulation length range (in minutes) when factoring in Fight Length Variance."></span>
					<div style="width: 350px;" class="sliders_buffer">
						<input type="number" id="fightLength" name="fightLength" data-slider-id="fightLengthSlider" data-slider-min="<?php echo $User->MinSimLength; ?>" data-slider-max="<?php echo $User->MaxSimLength; ?>" data-slider-value="<?php if (isset($_POST['fightLength'])) { echo $_POST['fightLength']; } else { echo "300"; } ?>" data-slider-step="15" class="form-control" style="width: 100%;" />
						<div id="fightLengthRange" style="clear: both; text-align: center; width: 100%"></div>
					</div>
	    		</div>
	    	</div>
			<div class="form-group">
				<label class="control-label col-xs-4" for="fightLengthVariance">Fight Length Variance:</label>
				<div class="col-xs-8">
						<span style="float: right; height: 38px; line-height: 38px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="This changes the randomness of the fight length from iteration to iteration. This helps to account for different kill speeds you'd see in-game."></span>
						<div style="width: 350px;" class="sliders_buffer">
						<input type="number" id="fightLengthVariance" name="fightLengthVariance" data-slider-id="fightLengthVarianceSlider" data-slider-min="0" data-slider-max="100" data-slider-value="<?php if (isset($_POST['fightLengthVariance'])) { echo $_POST['fightLengthVariance']; } else { echo "20"; } ?>" data-slider-step="1" class="form-control" style="width: 100%;" />
					</div>
	    		</div>
	    	</div>
			<div class="form-group">
				<div class="col-xs-offset-4 col-xs-8">
		    		<span style="float: right; height: 24px; line-height: 24px;" class="glyphicon glyphicon-question-sign" data-toggle="tooltiplarge" data-placement="right" title="Checking this box will tell Beotorch to compute scaling factors for this character. Simulating scale factors costs 5 times as many queue position slots to run."></span>
	    			<label style="width: 350px;">
                        <input id="scaleFactors" name="scaleFactors" type="checkbox" value="1" onclick="calculateExpectedSimulationCost()" /> Calculate Scale Factors</label>
	    		</div>
	    	</div>
            <div id="expectedCost" class="alert alert-short alert-success" style="display: none;">
                This simulation will cost <span id="expectedCostTotal" style="font-weight: bold;"></span> free queue slots. <span id="expectedCostError">Unfortunately, you only have <b><?php print ($User->MaxSimQueueSize - $currentSimQueue[0]["WeightedQueue"]); ?></b> available free queue slots. You will need to reduce the size of your simulation before queueing or wait until some of your existing queued simulations complete.</span>
            </div>
			<div class="form-group">
				<div class="col-xs-offset-4 col-xs-8">
		    		<button id="submitCharacter" type="submit" class="btn btn-primary" disabled>Queue Simulation</button>
	    		</div>
	    	</div>
	    <?php		
	    	
	    	$bosscount_ticks = "1";
	    	$bosscount_ticks_labels = "'1'";
	    	
	    	for ($x = 2; $x <= $User->MaxBossCount; $x++)
	    	{
	    		$bosscount_ticks .= ", " . $x;
	    		$bosscount_ticks_labels .= ", '" . $x . "'";
	    	}
	    		    	
	    	$iterations_ticks = "1000";
	    	$iterations_ticks_positions = "0";
	    	$iterations_ticks_labels = "'1000'";
	    	
	    	$x = 0;
	    	$increment = round($User->MaxIterations / 5, -3);
	    	
	    	while ($x < $User->MaxIterations)
	    	{
		    	$x += $increment;
		    	if ($x > $User->MaxIterations)
		    	{
		    		$x = $User->MaxIterations;
		    	}
		    	
	    		$iterations_ticks .= ", " . $x;
	    		$iterations_ticks_positions .= ", " . ((100 * $x) / $User->MaxIterations);
	    		$iterations_ticks_labels .= ", '" . number_format($x, 0, "", ",") . "'";
	    	}
	    	
	    	$fightLength_ticks = "" . $User->MinSimLength . "";
	    	$fightLength_ticks_positions = "0";
	    			    				    		
    		$fightSeconds = $User->MinSimLength % 60;
    		$fightMinutes = round($User->MinSimLength / 60, 0);
    		
    		$fightLength_ticks_labels = "'" . $fightMinutes . ":";
    		
    		if ($fightSeconds < 10)
    		{
    			$fightLength_ticks_labels .= "0";
    		}
    		$fightLength_ticks_labels .= $fightSeconds . "'";
	    		
	    	$x = $User->MinSimLength;
	    	$y = 0;
	    	$fightLength_tickcount = 0;
	    	
	    	do
	    	{
	    		$y++;
	    		$fightLength_tickcount = round(($User->MaxSimLength - $User->MinSimLength) / (15 * $y), 0);
	    	}
	    	while ($fightLength_tickcount > 7);
	    	
    		$fightLength_adjusted = ($User->MaxSimLength - $User->MinSimLength) % (15 * $y);
    		
    		if ($fightLength_adjusted != ($User->MaxSimLength - $User->MinSimLength))
    		{
    			$fightLength_tickcount++;
    		}
	    	
	    	$increment = 15 * $y;
	    	
	    	if ($User->MinSimLength % 15 != 0) {
	    		$x = ($User->MinSimLength - $User->MinSimLength % 15) + (15 * ($y + 1));
	    		$fightLength_ticks .= ", " . $x;
	    		$fightLength_ticks_positions .= ", " . ((100 * ($x - $User->MinSimLength)) / ($User->MaxSimLength - $User->MinSimLength));
	    		
	    		$fightSeconds = $x % 60;
	    		$fightMinutes = round($x / 60, 0);
	    		
	    		$fightLength_ticks_labels .= ", '" . $fightMinutes . ":";
	    		
	    		if ($fightSeconds < 10)
	    		{
	    			$fightLength_ticks_labels .= "0";
	    		}
	    		$fightLength_ticks_labels .= $fightSeconds . "'";
	    	}
	    	
	    	while ($x < $User->MaxSimLength)
	    	{
		    	$x += $increment;
		    	if ($x > $User->MaxSimLength)
		    	{
		    		$x = $User->MaxSimLength;
		    	}
		    	
		    	if (($User->MaxSimLength - $x) >= $increment || $x == $User->MaxSimLength) 				
		    	{							
					$fightLength_ticks .= ", " . $x;
					$fightLength_ticks_positions .= ", " . ((100 * ($x - $User->MinSimLength)) / ($User->MaxSimLength - $User->MinSimLength));

					$fightSeconds = $x % 60;
					$fightMinutes = round($x / 60, 0);
					
					$fightLength_ticks_labels .= ", '" . $fightMinutes . ":";
					
					if ($fightSeconds < 10)
					{
						$fightLength_ticks_labels .= "0";
					}
					$fightLength_ticks_labels .= $fightSeconds . "'";
	    		}
	    	}
	    	
	    	
	    	$fightLengthVariance_ticks = "0";
	    	$fightLengthVariance_ticks_labels = "'0%'";
	    	$x = 0;
	    	$increment = 20;
	    	
	    	while ($x < 100)
	    	{
		    	$x += $increment;
		    	if ($x > 100)
		    	{
		    		$x = 100;
		    	}
		    	
	    		$fightLengthVariance_ticks .= ", " . $x;
	    		$fightLengthVariance_ticks_labels .= ", '" . $x . "%'";
	    	}
	    	
	    	$fightLengthVariance_ticks_positions = $fightLengthVariance_ticks;
	    ?>
	    <script>
	    	$("#resimCharacter").chosen({
	    		disable_search_threshold: 10
	    	}).bind('keypress', function(e) {
				if(e.which === 13) {
					setResimCharacter();
				}
			});
			
	    	$("#server").chosen({
	    		disable_search_threshold: 10
	    	}).bind('keypress', function(e) {
				if(e.which === 13) {
					getCharacterArmoryEntry();
				}
			});
			
	    	$("#fightType").chosen({
	    		disable_search_threshold: 10
	    	});
	    	
	    	$("#bosscount").slider({
				ticks: [<?php echo $bosscount_ticks ?>],
				ticks_labels: [<?php echo $bosscount_ticks_labels; ?>]
			})
			.on('slideStop', calculateExpectedSimulationCost);
    
	    	$("#tmiwindow").slider({
				formatter: function (value) {
					return value.toLocaleString('en-US') + " seconds";
				}
			});
	    	
	    	$("#tmiboss").slider({
				formatter: function (value) {
					switch (value) {
						case 1:
						default:
							return "Tier 17 LFR";
							break;
						case 2:
							return "Tier 17 Normal";
							break;
						case 3:
							return "Tier 17 Heroic";
							break;
						case 4:
							return "Tier 17 Mythic";
							break;
						case 5:
							return "Tier 18 Normal";
							break;
						case 6:
							return "Tier 18 Heroic";
							break;
						case 7:
							return "Tier 18 Mythic";
							break;
					}
				}
			});
	    	
	    	$("#iterations").slider({
				ticks: [<?php echo $iterations_ticks; ?>],
				ticks_positions: [<?php echo $iterations_ticks_positions; ?>],
				ticks_labels: [<?php echo $iterations_ticks_labels; ?>],
				ticks_snap_bounds: 499,
				formatter: function (value) {
					return value.toLocaleString('en-US');
				}
			})
			.on('slideStop', calculateExpectedSimulationCost);
			
	    	$("#fightLength").slider({
				ticks: [<?php echo $fightLength_ticks; ?>],
				ticks_positions: [<?php echo $fightLength_ticks_positions; ?>],
				ticks_labels: [<?php echo $fightLength_ticks_labels; ?>],
				ticks_snap_bounds: 5,
				formatter: function (value) {
					var minutes = parseInt(value / 60);
					var seconds = parseInt(value % 60);
					
					var output = minutes + ":";
					
					if (seconds < 10) {
						output += "0";
					}
					
					output += seconds;
					return output;
				}
			})
			.on('slideStop', fightLengthRangeUpdate);
			
	    	$("#fightLengthVariance").slider({
				ticks: [<?php echo $fightLengthVariance_ticks; ?>],
				ticks_positions: [<?php echo $fightLengthVariance_ticks_positions; ?>],
				ticks_labels: [<?php echo $fightLengthVariance_ticks_labels; ?>],
				ticks_snap_bounds: 0,
				formatter: function (value) {
					return value + "%";
				}
			})
			.on('slideStop', fightLengthRangeUpdate);
			
			function fightLengthRangeUpdate() {
				var variance = $("#fightLengthVariance").val() / 100;
				var length = $("#fightLength").val();
				
				if (variance == 0) {
					var minutes = parseInt(length / 60);
					var seconds = parseInt(length % 60);
					
					var output = minutes + ":";
					
					if (seconds < 10) {
						output += "0";
					}
					
					output += seconds;
				
					$("#fightLengthRange").html("(" + output + ")");					
				}
				else {						
					var durationLow = parseInt(length, 10) - parseInt(length * variance, 10);
					var durationHigh = parseInt(length, 10) + parseInt(length * variance, 10);
					var minutesLow = parseInt(durationLow / 60);
					var secondsLow = parseInt(durationLow % 60);
					var minutesHigh = parseInt(durationHigh / 60);
					var secondsHigh = parseInt(durationHigh % 60);
					
					var output = minutesLow + ":";
					
					if (secondsLow < 10) {
						output += "0";
					}
					
					output += secondsLow + " - " + minutesHigh + ":";
					
					if (secondsHigh < 10) {
						output += "0";
					}
					
					output += secondsHigh;
					
					$("#fightLengthRange").html("(" + output + ")");
				}
			}
			
			fightLengthRangeUpdate();
            
            maxActors = <?php print $User->MaxActors; ?>;
            maxAvailableQueueSlots = <?php print ($User->MaxSimQueueSize - $currentSimQueue[0]["WeightedQueue"]); ?>;
	    </script>
	    
	    <?php
	    	}
        ?>
	</div>
</div>
<?php

if ($User->CustomProfile)
{
?>
<div id="containerCustomProfile" class="panel panel-default panel-col-nonstandard" style="width: 300px; float: left; margin-left: 10px; display: none;">
    <div class="panel-heading">Custom Profile</div>
    <div class="panel-body">
        <div class="alert alert-short alert-info">Paste your custom profile here. Make sure you include everything you'd normally see in the "Simulate" tab, including talents, APL, and gear.</div>
	    <select style="width: 270px;" id="simcVersion" name="simcVersion" class="form-control">
            <option value="live" selected>Legion (Live)</option>
        </select>
        <textarea id="customProfileText" name="customProfileText" style="width: 270px; margin-top:10px; height: 464px; border:1px solid #999999; font-size: 10px; font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospace; overflow: scroll; overflow-wrap: normal;"></textarea>
    </div>
</div>
<?php
}
?> 
</form>   
<div class="panel panel-default" style="width: 320px; float: right; margin-top: -55px;">
	<div class="panel-heading" style="padding-top: 5px; padding-bottom: 5px;">Advertisement</div>
	<div class="panel-body" style="padding: 10px;">

	</div>
</div>
        
<script id="simulationTalentDropdowns" type="text/x-jsrender">
    <div id="talentChoiceButtons-{{:GUID}}" containerGuid="{{:GUID}}" class="talentChoiceButtons">
        <div id="talent1DropDown-{{:GUID}}"></div>
        <div id="talent2DropDown-{{:GUID}}"></div>
        <div id="talent3DropDown-{{:GUID}}"></div>
        <div id="talent4DropDown-{{:GUID}}"></div>
        <div id="talent5DropDown-{{:GUID}}"></div>
        <div id="talent6DropDown-{{:GUID}}"></div>
        <div id="talent7DropDown-{{:GUID}}"></div>
        <input type="hidden" name="talent1DropDown-{{:GUID}}-value" id="talent1DropDown-{{:GUID}}-value" />
        <input type="hidden" name="talent2DropDown-{{:GUID}}-value" id="talent2DropDown-{{:GUID}}-value" />
        <input type="hidden" name="talent3DropDown-{{:GUID}}-value" id="talent3DropDown-{{:GUID}}-value" />
        <input type="hidden" name="talent4DropDown-{{:GUID}}-value" id="talent4DropDown-{{:GUID}}-value" />
        <input type="hidden" name="talent5DropDown-{{:GUID}}-value" id="talent5DropDown-{{:GUID}}-value" />
        <input type="hidden" name="talent6DropDown-{{:GUID}}-value" id="talent6DropDown-{{:GUID}}-value" />
        <input type="hidden" name="talent7DropDown-{{:GUID}}-value" id="talent7DropDown-{{:GUID}}-value" />    
        <div class="removeTalentsetContainer">
            <a href="#" id="talentChoiceRemove-{{:GUID}}" class="removeTalentsetSpan" onclick="removeTalentset('{{:GUID}}')">
                <span class="glyphicon glyphicon-minus"></span> Remove talentset from comparison
            </a>
        </div>
    </div>
</script>

<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
