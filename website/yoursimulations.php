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

$pageTitle = "Your Simulations";
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';
?>
            <div style="float: left">
	            <h2>Your Simulations</h2>
	        </div>		       
		    <div class="panel panel-default" style="width: 478px; float: right; position: relative; right: 1px; padding-top: 5px; padding-left: 5px; padding-right: 5px;">

			</div>
			<div style="clear: both;"></div>

			<div class="panel panel-default">
				<div class="panel-heading"><a data-toggle="collapse" href="#SearchFilterSimulations">Search or Filter Your Simulations</a></div>
				<div id="SearchFilterSimulations" class="panel-collapse collapse<?php
				
		        if (count($_POST) > 0)
				{
					echo " in";
				}
				?>" style="margin-top: 10px;">
					<form action="yoursimulations.php<?php if ($User->UserLevelId == 9 && isset($_GET['UserId'])) { echo '?UserId=' . $_GET['UserId']; } ?>" method="post" name="simulations_form" class="form-horizontal">                            
					<?php
					
					if ($User->UserLevelId == 9 && isset($_GET['UserId']))
					{
						$simUserId = $_GET['UserId'];
					}
					else
					{
						$simUserId = $_SESSION['user_id'];
					}
					
					$simCounts = SimulationCountsGet($mysqli, $simUserId);    
									
					$accountCount = $simCounts[0];
					$characters = $simCounts[1];
					$classes = $simCounts[2];
					$specializations = $simCounts[3];
					$servers = $simCounts[4];
					$simulationTypes = $simCounts[5];
					$iterations = $simCounts[6];
					$statuses = $simCounts[7];
					$itemLevels = $simCounts[8];
					
					?>           
						<div class="form-group col-xs-4">
							<label class="control-label col-xs-5" for="status">Simulation Status:</label>
							<div class="col-xs-7" style="height: 38px; line-height: 38px;">
								<select id="status" name="status" class="form-control chosen-select" data-placeholder="Choose a status...">
									<option value="">Any Status</option>
									<?php
															
									for ($x = 0; $x < count($statuses); $x++)
									{						
										echo "<option value=\"" . $statuses[$x]["SimulationStatusId"] . "\"";
										
										if (isset($_POST['status']) && $_POST['status'] == $statuses[$x]["SimulationStatusId"])
										{
											echo " selected";
										}
										
										echo ">" . $statuses[$x]["StatusName"] . "</option>\n";
									}
										   
									?>
								</select>
							</div>
						</div>    
						<div class="form-group col-xs-4">
							<label class="control-label col-xs-5" for="server">Server:</label>
							<div class="col-xs-7" style="height: 38px; line-height: 38px;">
								<select id="server" name="server" class="form-control chosen-select" data-placeholder="Choose a server..." style="width: 400px;">
									<option value="">All Servers</option>
									<?php								
									
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
							</div>
						</div>  
						<div class="form-group col-xs-4">
							<label class="control-label col-xs-5" for="character">Character:</label>
							<div class="col-xs-7" style="height: 38px; line-height: 38px;">
								<select id="character" name="character" class="form-control chosen-select" data-placeholder="Choose a character...">
									<option value="">All Characters</option>
									<?php
						
									$lastServer = 0;
									//$lastRegion = 0;
						
									for ($x = 0; $x < count($characters); $x++)
									{
										/*if ($characters[$x]["RegionId"] != $lastRegion)
										{
											if ($lastRegion > 0)
											{
												echo "</optgroup>\n";
											}
											echo "<optgroup label=\"" . $servers[$x]["RegionName"] . "\">\n";
											$lastRegion = $servers[$x]["RegionId"];
										}*/
										
										if ($characters[$x]["ServerId"] != $lastServer)
										{
											if ($lastServer > 0)
											{
												echo "</optgroup>\n";
											}
											echo "<optgroup label=\"" . $characters[$x]["ServerName"] . " (" . strtoupper($characters[$x]["RegionPrefix"]) . " - " . $characters[$x]["ServerType"] . ")\">\n";
											$lastServer = $characters[$x]["ServerId"];
										}
										
										echo "<option value=\"" . $characters[$x]["CharacterId"] . "\"";
										
										if (isset($_POST['character']) && $_POST['character'] == $characters[$x]["CharacterId"])
										{
											echo " selected";
										}
										
										echo ">" . utf8_decode($characters[$x]["CharacterName"]) . " (" . $characters[$x]["ClassName"] . ")</option>\n";
									}
									
									echo "</optgroup>\n";
										   
									?>
								</select>
							</div>
						</div> 
						<div class="form-group col-xs-4">
							<label class="control-label col-xs-5" for="class">Class:</label>
							<div class="col-xs-7" style="height: 38px; line-height: 38px;">
								<select id="class" name="class" class="form-control chosen-select" data-placeholder="Choose a class...">
									<option value="">All Classes</option>
									<?php
						
									for ($x = 0; $x < count($classes); $x++)
									{											
										echo "<option value=\"" . $classes[$x]["ClassId"] . "\"";
										
										if (isset($_POST['class']) && $_POST['class'] == $classes[$x]["ClassId"])
										{
											echo " selected";
										}
										
										echo ">" . $classes[$x]["ClassName"] . "</option>\n";
									}
										   
									?>
								</select>
							</div>
						</div>
						<div class="form-group col-xs-4">
							<label class="control-label col-xs-5" for="specialization">Specialization:</label>
							<div class="col-xs-7" style="height: 38px; line-height: 38px;">
								<select id="specialization" name="specialization" class="form-control chosen-select" data-placeholder="Choose a specialization...">
									<option value="">All Specializations</option>
									<?php
									
									$lastClass = -1;
						
									for ($x = 0; $x < count($specializations); $x++)
									{			
										
										if ($specializations[$x]["ClassId"] != $lastClass)
										{
											if ($lastClass > -1)
											{
												echo "</optgroup>\n";
											}
											echo "<optgroup label=\"" . $specializations[$x]["ClassName"] . "\">\n";
											$lastClass = $specializations[$x]["ClassId"];
										}							
										echo "<option value=\"" . $specializations[$x]["SpecializationId"] . "\"";
										
										if (isset($_POST['specialization']) && $_POST['specialization'] == $specializations[$x]["SpecializationId"])
										{
											echo " selected";
										}
										
										echo ">" . $specializations[$x]["SpecializationName"] . "</option>\n";
									}
									
									echo "</optgroup>\n";
										   
									?>
								</select>
							</div>
						</div>           
						<div class="form-group col-xs-4">
							<label class="control-label col-xs-5" for="fightType">Fight Type:</label>
							<div class="col-xs-7" style="height: 38px; line-height: 38px;">
								<select id="fightType" name="fightType" class="form-control chosen-select">
									<option value="">All Fight Types</option>
									<?php
							
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
							</div>
						</div>        
						<div class="form-group col-xs-4">
							<label class="control-label col-xs-5" for="iterations">Iterations:</label>
							<div class="col-xs-7 sliders_buffer" style="height: 76px; line-height: 38px;">
								<input type="number" id="iterations" name="iterations" data-slider-id="iterationsSlider" data-slider-min="<?php echo $iterations[0]['Iterations']; ?>" data-slider-max="<?php echo $iterations[count($iterations)-1]['Iterations']; ?>" data-slider-step="250" class="form-control" style="width: 100%;" />
							</div>
						</div>
						<input type="hidden" name="iterationslow" id="iterationslow"<?php if (isset($_POST['iterationslow']) && $_POST['iterationslow'] > 0)
						{
							echo " value=\"" . $_POST['iterationslow'] . "\"";
						}
						else
						{
							echo " value=\"" . $iterations[0]['Iterations'] . "\"";
						}
						?> />
						<input type="hidden" name="iterationshigh" id="iterationshigh"<?php if (isset($_POST['iterationshigh']) && $_POST['iterationshigh'] > 0)
						{
							echo " value=\"" . $_POST['iterationshigh'] . "\"";
						}
						else
						{
							echo " value=\"" . $iterations[count($iterations)-1]['Iterations'] . "\"";
						}
						?>  />  
						<div class="form-group col-xs-4">
							<label class="control-label col-xs-5" for="itemlevel">Item Level:</label>
							<div class="col-xs-7 sliders_buffer" style="height: 76px; line-height: 38px;">
								<input type="number" id="itemlevel" name="itemlevel" data-slider-id="itemlevelSlider" data-slider-min="<?php echo $itemLevels[0]['ItemLevel']; ?>" data-slider-max="<?php echo $itemLevels[count($itemLevels)-1]['ItemLevel']; ?>" data-slider-step="1" class="form-control" style="width: 100%;" />
							</div>
						</div>
						<input type="hidden" name="itemlevelslow" id="itemlevelslow"<?php if (isset($_POST['itemlevelslow']) && $_POST['itemlevelslow'] > 0)
						{
							echo " value=\"" . $_POST['itemlevelslow'] . "\"";
						}
						else
						{
							echo " value=\"" . $itemLevels[0]['ItemLevel'] . "\"";
						}
						?> />
						<input type="hidden" name="itemlevelshigh" id="itemlevelshigh"<?php if (isset($_POST['itemlevelshigh']) && $_POST['itemlevelshigh'] > 0)
						{
							echo " value=\"" . $_POST['itemlevelshigh'] . "\"";
						}
						else
						{
							echo " value=\"" . $itemLevels[count($itemLevels)-1]['ItemLevel'] . "\"";
						}
						?>  />
						<div class="form-group col-xs-4">
							<label class="control-label col-xs-5" for="bosscount">Bosses:</label>
							<div class="col-xs-7 sliders_buffer" style="height: 76px; line-height: 38px;">
								<input type="number" id="bosscount" name="bosscount" data-slider-id="bosscountSlider" data-slider-min="1" data-slider-max="8" data-slider-step="1" class="form-control" style="width: 100%;" />
							</div>
						</div>
						<input type="hidden" name="bosscountlow" id="bosscountlow"<?php if (isset($_POST['bosscountlow']) && $_POST['bosscountlow'] > 0)
						{
							echo " value=\"" . $_POST['bosscountlow'] . "\"";
						}
						else
						{
							echo " value=\"1\"";
						}
						?> />
						<input type="hidden" name="bosscounthigh" id="bosscounthigh"<?php if (isset($_POST['bosscounthigh']) && $_POST['bosscounthigh'] > 0)
						{
							echo " value=\"" . $_POST['bosscounthigh'] . "\"";
						}
						else
						{
							echo " value=\"8\"";
						}
						?>  /> 
						<div class="form-group col-xs-4">
							<label class="control-label col-xs-5" for="reportarchived">HTML Report Available:</label>
							<div class="col-xs-7" style="height: 38px; line-height: 38px;">
								<select id="reportarchived" name="reportarchived" class="form-control chosen-select">
									<option value="any"<?php if (isset($_POST['reportarchived']) && $_POST['reportarchived'] == "any")
										{
											echo " selected";
										}
										?>>Any</option>
									<option value="true"<?php if (isset($_POST['reportarchived']) && $_POST['reportarchived'] == "true")
										{
											echo " selected";
										}
										?>>Yes</option>
									<option value="false"<?php if (isset($_POST['reportarchived']) && $_POST['reportarchived'] == "false")
										{
											echo " selected";
										}
										?>>No</option>
								</select>
							</div>
						</div>        
						<div class="form-group col-xs-4">
							<label class="control-label col-xs-5" for="scalefactors">Has Scale Factors:</label>
							<div class="col-xs-7" style="height: 38px; line-height: 38px;">
								<select id="scalefactors" name="scalefactors" class="form-control chosen-select">
									<option value="any"<?php if (isset($_POST['scalefactors']) && $_POST['scalefactors'] == "any")
										{
											echo " selected";
										}
										?>>Any</option>
									<option value="true"<?php if (isset($_POST['scalefactors']) && $_POST['scalefactors'] == "true")
										{
											echo " selected";
										}
										?>>Yes</option>
									<option value="false"<?php if (isset($_POST['scalefactors']) && $_POST['scalefactors'] == "false")
										{
											echo " selected";
										}
										?>>No</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<div class="col-xs-12 text-center">
								<button id="submitFilter" type="submit" class="btn btn-primary" onclick="sliderFilterUpdate();">Filter Simulations</button>
								<button id="submitFilter" type="button" class="btn btn-primary" onclick="searchFilterReset();">Reset Filters</button>
							</div>
						</div>
						
					</form>
					<?php	
			    	
			    	$bosscount_ticks = "1";
			    	$bosscount_ticks_labels = "'1'";
			    	
			    	for ($x = 2; $x <= 8; $x++)
			    	{
			    		$bosscount_ticks .= ", " . $x;
			    		$bosscount_ticks_labels .= ", '" . $x . "'";
			    	}
			    				    	
			    	if (isset($_POST['bosscountlow']) && $_POST['bosscountlow'] > 0)
			    	{
			    		$bosscountlow = $_POST['bosscountlow'];
			    	}
			    	else
			    	{
			    		$bosscountlow = 1;
			    	}
			    	
			    	if (isset($_POST['bosscounthigh']) && $_POST['bosscounthigh'] > 0)
			    	{
			    		$bosscounthigh = $_POST['bosscounthigh'];
			    	}
			    	else
			    	{
			    		$bosscounthigh = 8;
			    	}
			    	
					$maxIterations = $iterations[count($iterations)-1]['Iterations'];				
			    	$iterations_ticks = "" . $iterations[0]['Iterations'] . "";
			    	$iterations_ticks_positions = "0";
			    	$iterations_ticks_labels = "'" . $iterations[0]['Iterations'] . "'";
			    	
			    	if (isset($_POST['iterationslow']) && $_POST['iterationslow'] > 0)
			    	{
			    		$iterationsLow = $_POST['iterationslow'];
			    	}
			    	else
			    	{
			    		$iterationsLow = $iterations[0]['Iterations'];
			    	}
			    	
			    	if (isset($_POST['iterationshigh']) && $_POST['iterationshigh'] > 0)
			    	{
			    		$iterationsHigh = $_POST['iterationshigh'];
			    	}
			    	else
			    	{
			    		$iterationsHigh = $maxIterations;
			    	}
			    	
			    	$x = 0;
			    	$increment = round($maxIterations / 4, -3);
			    	
			    	if ($increment < 0)
			    	{
			    		$increment = 1000;
			    	}
			    	
			    	while ($x < $maxIterations)
			    	{
				    	$x += $increment;
				    	if ($x > $maxIterations)
				    	{
				    		$x = $maxIterations;
				    	}
				    	
			    		$iterations_ticks .= ", " . $x;
			    		$iterations_ticks_positions .= ", " . ((100 * $x) / $maxIterations);
			    		$iterations_ticks_labels .= ", '" . number_format($x, 0, "", ",") . "'";
			    	}	    	
			    	
					$maxItemlevel = $itemLevels[count($itemLevels)-1]['ItemLevel'];				
			    	$itemlevel_ticks = "" . $itemLevels[0]['ItemLevel'] . "";
			    	$itemlevel_ticks_positions = "0";
			    	$itemlevel_ticks_labels = "'" . $itemLevels[0]['ItemLevel'] . "'";
			    	
			    	if (isset($_POST['itemlevelslow']) && $_POST['itemlevelslow'] > 0)
			    	{
			    		$itemlevelslow = $_POST['itemlevelslow'];
			    	}
			    	else
			    	{
			    		$itemlevelslow = $itemLevels[0]['ItemLevel'];
			    	}
			    	
			    	if (isset($_POST['itemlevelshigh']) && $_POST['itemlevelshigh'] > 0)
			    	{
			    		$itemlevelshigh = $_POST['itemlevelshigh'];
			    	}
			    	else
			    	{
			    		$itemlevelshigh = $maxItemlevel;
			    	}
			    	
			    	$x = $itemLevels[0]['ItemLevel'];
			    	$increment = round(($maxItemlevel - $itemLevels[0]['ItemLevel']) / 4, -1);
			    	
			    	if ($increment <= 0)
			    	{
			    		$increment = 10;
			    	}
			    	
			    	while ($x < $maxItemlevel)
			    	{
				    	$x += $increment;
				    	if ($x > $maxItemlevel)
				    	{
				    		$x = $maxItemlevel;
				    	}
				    	
			    		$itemlevel_ticks .= ", " . $x;
			    		$itemlevel_ticks_positions .= ", " . ((100 * ($x - $itemLevels[0]['ItemLevel'])) / ($maxItemlevel - $itemLevels[0]['ItemLevel']));
			    		$itemlevel_ticks_labels .= ", '" . number_format($x, 0, "", ",") . "'";
			    	}
					?>
            
					<script type="text/javascript">
						$(".chosen-select").chosen({
							disable_search_threshold: 10,
							width: "100%"
						});
			    	
			    	$("#bosscount").slider({
						ticks: [<?php echo $bosscount_ticks; ?>],
						ticks_labels: [<?php echo $bosscount_ticks_labels; ?>],
						formatter: function (value) {
							return value.toLocaleString('en-US');
						},
						range: true,
						value: [<?php echo $bosscountlow . ", " . $bosscounthigh; ?>],
						formatter: function (value) {
							var lowValue = parseInt(value[0]);
							var highValue = parseInt(value[1]);
							return lowValue.toLocaleString('en-US') + " - " + highValue.toLocaleString('en-US');
						}
					});
			    	
			    	$("#iterations").slider({
						ticks: [<?php echo $iterations_ticks; ?>],
						ticks_positions: [<?php echo $iterations_ticks_positions; ?>],
						ticks_labels: [<?php echo $iterations_ticks_labels; ?>],
						ticks_snap_bounds: 499,
						formatter: function (value) {
							return value.toLocaleString('en-US');
						},
						range: true,
						value: [<?php echo $iterationsLow . ", " . $iterationsHigh; ?>],
						formatter: function (value) {
							var lowValue = parseInt(value[0]);
							var highValue = parseInt(value[1]);
							return lowValue.toLocaleString('en-US') + " - " + highValue.toLocaleString('en-US');
						}
					});
			    	
			    	$("#itemlevel").slider({
						ticks: [<?php echo $itemlevel_ticks; ?>],
						ticks_positions: [<?php echo $itemlevel_ticks_positions; ?>],
						ticks_labels: [<?php echo $itemlevel_ticks_labels; ?>],
						ticks_snap_bounds: 1,
						formatter: function (value) {
							return value.toLocaleString('en-US');
						},
						range: true,
						value: [<?php echo $itemlevelslow . ", " . $itemlevelshigh; ?>],
						formatter: function (value) {
							var lowValue = parseInt(value[0]);
							var highValue = parseInt(value[1]);
							return lowValue.toLocaleString('en-US') + " - " + highValue.toLocaleString('en-US');
						}
					});
					
					$("#SearchFilterSimulations").on("shown.bs.collapse", function () {
						refreshSearchBoxes();
					});
					
					function sliderFilterUpdate() {
						var values = $("#iterations").slider("getValue");
						
						$("input[name=iterationslow]").val(values[0]);
						$("input[name=iterationshigh]").val(values[1]);
						
						var values1 = $("#itemlevel").slider("getValue");
						
						$("input[name=itemlevelslow]").val(values1[0]);
						$("input[name=itemlevelshigh]").val(values1[1]);
						
						var values2 = $("#bosscount").slider("getValue");
						
						$("input[name=bosscountlow]").val(values2[0]);
						$("input[name=bosscounthigh]").val(values2[1]);
					}
					
					function searchFilterReset() {
						window.location.href = "<?php echo esc_url($_SERVER['PHP_SELF']); ?>";
					}
					</script>			    	
				</div>
			<div style="clear: both"></div>
			</div>

            <?php
            /*
            $simulationParams->PrivacyState = 1;
            $simulationParams->ShowControlOptions = 1;
            $simulationParams->User = $User;
            
            echo $simulationParams->PrivacyState;
            */
            if (isset($_POST['server']))
			{
				if (isset($_POST['status']))
				{
					$slStatus = $_POST['status'];
				}
				else
				{
					$slStatus = null;
				}
				
				if (isset($_POST['character']))
				{
					$slCharacter = $_POST['character'];
				}
				else
				{
					$slCharacter = null;
				}
				
				if (isset($_POST['fightType']))
				{
					$slFightType = $_POST['fightType'];
				}
				else
				{
					$slFightType = null;
				}
				
				if (isset($_POST['class']))
				{
					$slClass = $_POST['class'];
				}
				else
				{
					$slClass = null;
				}
				
				if (isset($_POST['specialization']))
				{
					$slSpecialization = $_POST['specialization'];
				}
				else
				{
					$slSpecialization = null;
				}
				
				if (isset($_POST['scalefactors']))
				{
					$slScaleFactors = $_POST['scalefactors'];
				}
				else
				{
					$slScaleFactors = null;
				}
				
				if (isset($_POST['reportarchived']))
				{
					$slReportArchived = $_POST['reportarchived'];
				}
				else
				{
					$slReportArchived = null;
				}
				
				if (isset($_POST['iterationslow']))
				{
					$slIterationsLow = $_POST['iterationslow'];
				}
				else
				{
					$slIterationsLow = null;
				}
				
				if (isset($_POST['iterationshigh']))
				{
					$slIterationsHigh = $_POST['iterationshigh'];
				}
				else
				{
					$slIterationsHigh = null;
				}
				
				if (isset($_POST['itemlevelslow']))
				{
					$slItemlevelsLow = $_POST['itemlevelslow'];
				}
				else
				{
					$slItemlevelsLow = null;
				}
				
				if (isset($_POST['itemlevelshigh']))
				{
					$slItemlevelsHigh = $_POST['itemlevelshigh'];
				}
				else
				{
					$slItemlevelsHigh = null;
				}
				
				if (isset($_POST['bosscountlow']))
				{
					$slBossCountLow = $_POST['bosscountlow'];
				}
				else
				{
					$slBossCountLow = null;
				}
				
				if (isset($_POST['bosscounthigh']))
				{
					$slBossCountHigh = $_POST['bosscounthigh'];
				}
				else
				{
					$slBossCountHigh = null;
				}
							
				
				if (isset($_POST['server']))
				{
					$slServer = $_POST['server'];
				}
				else
				{
					$slServer = null;
				}
                
                /*
                $simulationParams->Status = $slStatus;
                $simulationParams->Character = $slCharacter;
                $simulationParams->FightType = $slFightType;
                $simulationParams->Class = $slClass;
                $simulationParams->Specialization = $slSpecialization;
                $simulationParams->ScaleFactors = $slScaleFactors;
                $simulationParams->ReportArchived = $slReportArchived;
                $simulationParams->IterationsLow = $slIterationsLow;
                $simulationParams->IterationsHigh = $slIterationsHigh;
                $simulationParams->Server = $slServer;
                $simulationParams->ItemlevelsLow = $slItemlevelsLow;
                $simulationParams->ItemlevelsHight = $slItemlevelsHigh;
                $simulationParams->BossCountLow = $slBossCountLow;
                $simulationParams->BossCountHigh = $slBossCountHigh;
                $simulationParams->SimulationArchived = 0;
                $simulationParams->Hidden = 0;
                
                if ($simUserId != $User->UserId && $User->UserLevelId == 9)
                {
                    $simulationParams->SimUserId = $simUserId;
                }*/
                
                $simulations = $SimulationRepository->SimulationList($simUserId, $slStatus, null, 2, 0, $slCharacter, $slFightType, $slClass, $slSpecialization, $slScaleFactors, $slReportArchived, $slIterationsLow, $slIterationsHigh, $slServer, $slItemlevelsLow, $slItemlevelsHigh, $slBossCountLow, $slBossCountHigh);
				
				$simulations = SimulationList($mysqli, null, $slStatus, null, $slCharacter, $slFightType, $slClass, $slSpecialization, $slScaleFactors, $slReportArchived, $slIterationsLow, $slIterationsHigh, $slServer);
				
				//echo "'" . $slStatus . "' - null - '" . $slCharacter . "' - '" . $slFightType . "' - '" . $slClass . "' - '" . $slSpecialization . "' - '" . $slScaleFactors . "' - '" . $slReportArchived . "' - '" . $slIterationsLow . "' - '" .  $slIterationsHigh . "' - '" .  $slServer . "'";
			}
			else
			{
                //$simulationParams->Status = null;
            	$simulations = $SimulationRepository->SimulationList($simUserId, null, null, 2);
            }
            
            //include_simulationsTable_params($simulationParams);
            
            if (count($simulations) > 0)
            {
            	include_simulationsTable($simulations, 1, 1, $User);
            }
            else
            {
            	echo "<p>No simulations match your filters, or, you don't have any simulations yet!</p>";
            }
            
            ?>
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
