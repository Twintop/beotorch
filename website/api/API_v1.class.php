<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/API.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Enums/Classes.enum.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Enums/Specializations.enum.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Enums/Races.enum.php';

class MyAPI extends API
{
    protected $User;

    public function __construct($request, $origin) {
        parent::__construct($request);
    }
    
    /**
    * Request()
    * Request Work Item
    * Takes Computer API Key that is requesting the work unit.
    * Returns a Simulation record that can be claimed as a new Work Item. Returns empty set if there are no new Work Items.
    */
    protected function request()
    {
        $threads = -1;
        
        if (intval($this->args[1]) > 0)
        {
            $threads = intval($this->args[1]);
        }
        
        if ($this->method == 'GET' && preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/', strtoupper($this->args[0])))
        {
            $results = multiQuery($this->mysqli, "CALL WorkItemRequest(null, '" . $this->args[0] . "', '" . $this->ip_address . "'," . $threads . ")");
            
            if ($results[0] != null)
            {
                $obj = (object) $results[0][0];
                $obj->Actors = array();

                foreach ($results[1] as $actor)
                {
                    $obj->Actors[] = (object) $actor;
                }

                return $obj;
            }
            else
            {
                return "";
            }
        }
        else
        {
            return "Only accepts GET requests - |" . $this->args[0] . "|";
        }   	
    }
    
    /**
    * Claim()
    * Request Work Item
    * Takes ComputerID that is requesting the work unit.
    * Returns a Simulation record that can be claimed as a new Work Item. Returns empty set if there are no new Work Items.
    */
    protected function claim()
    {
        if ($this->method == 'GET')
        {
        	if (count($this->args) == 2)
        	{
        		$computerAPIKey = $this->args[0];
        		$simulationId = $this->args[1];
        	
		    	if ($stmt = $this->mysqli->prepare("CALL WorkItemClaim(null, ?, ?, ?)")) {
					$stmt->bind_param('sis', $computerAPIKey, $simulationId, $this->ip_address);  // Bind ComputerId to parameter.
					$stmt->execute();	// Execute the prepared query.
					$stmt->store_result();
			 
					clearStoredResults($this->mysqli);
	 
					if ($stmt->num_rows == 1)
					{	
						$resultRow = fetchMysqlRows($stmt);
						return $resultRow;
					}
					else //No Work Items at this time.
					{
						return "";
					}
				}
			}
			else
			{
				return "No Simulation Selected";
			}
        }
        else
        {
            return "Only accepts GET requests";
        }   	
    }
    
    /**
    * Storelog()
    * Stores the simc result log.
    * Takes ComputerID that is requesting the work unit.
    * Returns a Simulation record that can be claimed as a new Work Item. Returns empty set if there are no new Work Items.
    */
	protected function storeresults()
	{
        if ($this->method == 'PUT')
        {
        	if (count($this->args) == 2)	
        	{	
                include_once $_SERVER['DOCUMENT_ROOT'] . '/Repositories/SimulationRepository.php';
                include_once $_SERVER['DOCUMENT_ROOT'] . '/Repositories/UserRepository.php';
                
                $SimulationRepository = new SimulationRepository($this->mysqli);
                $UserRepository = new UserRepository($this->mysqli);
                
                $result_obj = json_decode($this->file, true);
                
				$computerAPIKey = $this->args[0];
				$simulationId = $this->args[1];
				
				$simulationResultId = $result_obj["result"];
		    	
				$saveResult = 0;
		    	if ($result_obj["result"] == 3)
				{
					if ($stmt = $this->mysqli->prepare("CALL WorkItemSaveResult(null, ?, ?, ?, ?, ?)"))
					{
//	   1  2  3  4  5  6  7  8  9 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27
						
						$stmt->bind_param('siiss',
				  						  $computerAPIKey,
				  						  $simulationId,
				  						  $simulationResultId,
				  						  $result_obj["log"],
				  						  $this->ip_address
				  						  );
						$stmt->execute();	// Execute the prepared query.
						$stmt->store_result();
			 
						clearStoredResults($this->mysqli);
				 
						$reportsList = fetchMysqlRows($stmt);
				 
						$saveResult = 1;
					}
                    
					if ($saveResult == 1)
					{                        
                        $resultRow = fetchMysqlRows($stmt);
                        
                        foreach($result_obj["Actors"] as $actor)
                        {
                            $processActor = 0;
                            
                            //print "Actor: " . $actor["name"] . "\n";
                            
                            if ($result_obj["CustomProfile"] == 1)
                            {
                                //print "Custom\n";
                                $actor["race"] = str_replace("_", " ", $actor["race"]);
                                $actor["race"] = str_replace(" ", "", $actor["race"]);
                                $actor["class"] = str_replace("_", " ", $actor["class"]);
                                $actor["class"] = str_replace(" ", "", $actor["class"]);
                                $actor["spec"] = str_replace("_", " ", $actor["spec"]);
                                $actor["spec"] = str_replace(" ", "", $actor["spec"]);
                                $specId = null;
                                $classId = null;
                                $raceId = null;
                                
                                if (strtolower($actor["spec"]) == "holy" ||
                                    strtolower($actor["spec"]) == "protection" ||
                                    strtolower($actor["spec"]) == "restoration")
                                {
                                    $actor["spec"] = $actor["spec"] . $actor["class"];
                                }
                                
                                if (Classes::isValidName($actor["class"]))
                                {
                                    if (!Specializations::isValidName($actor["spec"]))
                                    {
                                        $specId = Specializations::getValue(Specializations::None);
                                    }
                                }
                                else
                                {
                                    $specId = Specializations::getValue(Specializations::None);
                                    $classId = Classes::getValue(Classes::UNKNOWN);
                                }
                                
                                $raceId = Races::getValue($actor["race"]);
                                
                                if (!$raceId || $raceId < 0)
                                {
                                    $raceId = 0;
                                }
                                
                                $classId = Classes::getValue($actor["class"]);
                                
                                if (!$classId || $classId < 0)
                                {
                                    $classId = 0;
                                }
                                
                                $specId = Specializations::getValue($actor["spec"]);
                                
                                if (!$specId || $specId < 0)
                                {
                                    $specId = 0;
                                }
                                
                                if ($specId != Specializations::getValue(Specializations::None) && $classId != Classes::getValue(Classes::UNKNOWN))
                                {
                                    $simcVersion = PATCH_LIVE;
                                    
                                    if ($result_obj["simcVersion"] == "beta")
                                    {
                                        $simcVersion = PATCH_BETA;
                                    }
                                    elseif ($result_obj["simcVersion"] == "ptr")
                                    {
                                        $simcVersion = PATCH_PTR;
                                    }
                                    else
                                    {
                                        $simcVersion = PATCH_LIVE;
                                    }                                        
                                                                        
                                    $dbtalents = GetTalentsList($this->mysqli, $classId, $specId, null, $simcVersion);
                                    
                                    $talentIds = Array();
                                    
                                    if (strlen($actor["talents"]) > 7)
                                    {
                                        if (strpos($actor["talents"], "battle.net") !== false)
                                        {
                                            $talents = substr($actor["talents"], strlen($actor["talents"])-7, 7);
                                                                                        
                                            $actor["talents"] = "";
                                            
                                            for ($x = 0; $x < 7; $x++)
                                            {
                                                $letter= substr($talents, $x, 1);
                                                if ($letter == '.')
                                                {
                                                    $actor["talents"] .= "0";
                                                }
                                                elseif  ($letter == '0')
                                                {
                                                    $actor["talents"] .= "1";
                                                }
                                                elseif ($letter == '1')
                                                {
                                                    $actor["talents"] .= "2";
                                                }
                                                else
                                                {
                                                    $actor["talents"] .= "3";
                                                }
                                            }                                            
                                        }
                                    }
                                    
                                    $armoryTalent = "";
                                    
                                    for ($x = 0; $x < 7; $x++) {
                                        $talentCol = substr($actor["talents"], $x, 1);
                                        $talentIds[$x] = null;
                                        
                                        if ($talentCol == 1 || $talentCol == 2 || $talentCol == 3)
                                        {
                                            $armoryTalent = $armoryTalent . ($talentCol - 1);
                                            for ($y = 0; $y < count($dbtalents); $y++)
                                            {
                                                if ($dbtalents[$y]["TalentRow"] == $x && $dbtalents[$y]["TalentColumn"] == $talentCol-1)
                                                {
                                                    $talentIds[$x] = $dbtalents[$y]["TalentId"];
                                                    break;
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $armoryTalent = $armoryTalent . ".";
                                        }
                                    }

                                    $actor["SimulationActorId"] = $SimulationRepository->SimulationActorInsert($simulationId, null, null, null, null, $actor["level"], null, $classId, $raceId, 2, null, null, null, $armoryTalent, $actor["talents"], $talentIds, $specId, 1, $actor["name"]);
                                }
                            }
                            
                            if ($actor["int"])
                            {
                                $actor["primary"] = $actor["int"];
                            }
                            elseif ($actor["agi"])
                            {
                                $actor["primary"] = $actor["agi"];
                            }
                            elseif ($actor["str"])
                            {
                                $actor["primary"] = $actor["str"];
                            }
                            
                            if ($stmt = $this->mysqli->prepare("CALL WorkItemSaveActorResult(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"))
                            {
                                                                                        //	 1  2  3  4  5  6  7  8  9 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27
                                
                                $stmt->bind_param('iidddddddddddddddddiiii',
                                                  $actor["SimulationActorId"],
                                                  $actor["dps"],
                                                  $actor["primary"],
                                                  $actor["sta"],
                                                  $actor["spi"],
                                                  $actor["sp"],
                                                  $actor["ap"],
                                                  $actor["crit"],
                                                  $actor["haste"],
                                                  $actor["mastery"],
                                                  $actor["vers"],
                                                  $actor["mult"],
                                                  $actor["wdps"],
                                                  $actor["wohdps"],
                                                  $actor["armor"],
                                                  $actor["bonusarmor"],
                                                  $actor["avoidance"],
                                                  $actor["leech"],
                                                  $actor["runspeed"],
                                                  $actor["tmi"],
                                                  $actor["dtps"],
                                                  $actor["hps"],
                                                  $actor["aps"]
                                                  );
                                $stmt->execute();	// Execute the prepared query.
                                $stmt->store_result();

                                clearStoredResults($this->mysqli);
                            }
                        }
                        
                        if (count($reportsList) > 0 && $reportsList[0]["SimulationId"] != -1)
                        {
                            for ($x = 0; $x < count($reportsList); $x++) {
                                unlink(WAREHOUSE_FOLDER . $reportsList[$x]["SimulationGUID"] . ".html");

                                if ($stmt = $this->mysqli->prepare("CALL ArchiveReport(null, ?, ?, ?)")) {
                                    $stmt->bind_param('sis', $computerAPIKey, $reportsList[$x]["SimulationId"], $this->ip_address);
                                    $stmt->execute();
                                    $stmt->store_result();

                                    clearStoredResults($this->mysqli);
                                }
                            }
                        }
                    
						$results = multiQuery($this->mysqli, "CALL UserGetBySimulationId(" . $simulationId . ", 1)");
                        
						if ($results[1][0]["SimulationId"] != null )
						{                            
							$user = $UserRepository->FillUserObject($results[0][0]);
							$simulation = $SimulationRepository->FillSimulationObjectFromParams($results[1][0], $results[2], $results[3]);
							
							if ($user->SimEmails)
							{
																
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

$subject = "Beotorch Simulation Complete";

$headers   = array();
$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-type: text/plain; charset=iso-8859-1";
$headers[] = "From: Beotorch <noreply@beotorch.com>";
$headers[] = "Reply-To: Beotorch <noreply@beotorch.com>";
$headers[] = "Subject: {$subject}";
$headers[] = "X-Mailer: PHP/".phpversion();

$emailBody = "Hello,\r\n\r\n";

if ($simulation->CustomProfile)
{
    $emailBody .= "A custom profile simulation that you requested has completed.\n\r";
}
else
{
    $emailBody .= "A simulation that you requested for " . $simulation->SimulationActors[0]->CharacterName . "-" . $simulation->SimulationActors[0]->ServerName . " has completed.\r\n";
}

$emailBody .= "Here is a snapshot of the results:\r\n\r\n";

if (!$simulation->CustomProfile)
{
    $emailBody .= "Character: " . $simulation->SimulationActors[0]->CharacterName . "-" . $simulation->SimulationActors[0]->ServerName . ", " . $simulation->SimulationActors[0]->RaceName . " " . $simulation->SimulationActors[0]->SpecializationName . " " . $simulation->SimulationActors[0]->ClassName . " (" . $simulation->SimulationActors[0]->SimulationRole . ")\r\n";
    $emailBody .= "Region: " . $simulation->SimulationActors[0]->RegionName . "\r\n";
}

if ($simulation->SimulationName != "")
{
	$emailBody .= "Simulation Name: " . $simulation->SimulationName . "\r\n";
}

$emailBody .= "Sim Info: " . $simulation->SimulationTypeFriendlyName . " (" . $simulation->BossCount . " Target";

if ($simulation->BossCount > 1)
{
	$emailBody .= "s";
}

$emailBody .= ") @ " . number_format($simulation->Iterations, 0, ".", ",") . " iterations, " . $durationOutput . " duration\r\n";

if ($simulation->ScaleFactors == 1)
{    	        		
    //$emailBody .= "Scaling Factors:\r\n"; 
    $outputheader0 = "\t\t";//"<th>DPS</th>";	   
    $outputheader1 = "";
    $outputheader2 = "";
    $outputheader3 = "";
    $outputrow0 = "Scale Factors:\t";//"<td>" . number_format($simulations[$x]["DPS"], 0, ".", "") . "</td>";
    $outputrow1 = "";
    $outputrow2 = "";
    $outputrow3 = "";  
    $normalizedrow0 = "Normalized:\t";
    $normalizedrow1 = "";
    $normalizedrow2 = "";
    $normalizedrow3 = "";

    $resultsHeader0 = "\t\t";

    $resultsRow = "";

    if ($simulation->SimulationActors[0]->SimulationRole == "Tank")
    {
        $resultsHeader0 .= "TMI\tDPS\tDTPS\tHPS (APS)\r\n";
    }
    else
    {
        $resultsHeader0 .= "DPS\r\n";
    }

    $outputrowsCombined = "";
    $normalizedrowsCombined = "";

    $primaryStatValue = 1;

    $actorCount = 0;
    foreach ($simulation->SimulationActors as $actor)
    {
        $actorCount++;

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

        if ($actor->SimulationRole == "Tank")
        {
            $resultsRow .= "\t" . $actor->TMI . "\t" . $actor->DPS . "\t" . $actor->DTPS . "\t" . $actor->HPS . " (" . $actor->APS . ")\r\n";
        }
        else
        {
            $resultsRow .= "\t" . $actor->DPS . "\r\n";
        }        

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

            $outputheader1 = $primaryStatInfo["name"] . "\t";
            $outputrow1 = number_format((float)$primaryStatValue, 2, ".", "") . "\t";
            $normalizedrow1 = number_format((((float)$primaryStatValue) / ((float)$primaryStatValue)), 2, ".", "") . "\t";

            if ($actor->SimulationRole == "Tank")
            {    
                $statValue = $actor->Scaling["PrimaryStat"];
                $statInfo = GetStatDetails($actor->SpecializationPrimaryStat);	        				
                $outputheader1 .= $statInfo["name"] . "\t";
                $outputrow1 .= number_format((float)$statValue, 2, ".", "") . "\t";
                $normalizedrow1 .= number_format((((float)$statValue) / ((float)$primaryStatValue)), 2, ".", "") . "\t";       	        					
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
                    $oph = $statInfo["name"] . "\t";
                    $opr = number_format((float)$value, 2, ".", "") . "\t";
                    $onr = number_format((((float)$value) / ((float)$primaryStatValue)), 2, ".", "") . "\t";

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

        $outputrowsCombined .= "\t" . $outputrow1 . $outputrow2 . $outputrow3 . "\r\n"; 
        $normalizedrowsCombined .= "\t" . $normalizedrow1 . $normalizedrow2 . $normalizedrow3 . "\r\n"; 
    }

    $emailBody .= "Simulation Results\n\r";
    $emailBody .= $resultsHeader0 . $resultsRow;


    $emailBody .= "\r\n\t\tScaling Factors\r\n";
    $emailBody .= $outputheader0 . $outputheader1 . $outputheader2 . $outputheader3;

    $emailBody .= "\r\n";
    $emailBody .= $outputrowsCombined;
    $emailBody .= "\t\tNormalized\r\n";
    $emailBody .= $normalizedrowsCombined;
}

$emailBody .= "\n\rYou can view the full report here:\r\n\r\nhttps://" . $_SERVER['SERVER_NAME'] . "/simulationdetails.php?r=" . $simulation->SimulationGUID . "\r\n\r\nIf you'd like to queue up a new simulation, please visit your account at:\r\n\r\nhttps://" . $_SERVER['SERVER_NAME'] . "/\r\n\r\nThank you!\r\n-The Beotorch Team\r\nhttps://www.beotorch.com - http://twitter.com/Beotorch";


$mailSent = mail($user->Email, $subject, $emailBody, implode("\r\n", $headers));
							}
						}				    	
					}				
				}
				else
		    	{
		    		$archive = 1;
					if ($stmt = $this->mysqli->prepare("CALL WorkItemSaveResultShort(null, ?, ?, ?, ?, ?, ?)")) {
                        
						$stmt->bind_param('siissi',
				  						  $computerAPIKey,
				  						  $simulationId,
				  						  $simulationResultId,
				  						  $result_obj["log"],
				  						  $this->ip_address,
				  						  $archive
				  						  );
						$stmt->execute();	// Execute the prepared query.
						$stmt->store_result();
				 
						clearStoredResults($this->mysqli);
					}
				}
	 
				if ($saveResult == 1)
				{						
					if ($simulationResultId == 3)
					{
						$saveHTML = file_put_contents(WAREHOUSE_FOLDER . $result_obj["guid"] . ".html", $result_obj["html"]);
					}
					
					return $resultRow;
				}
				else //No Work Items at this time.
				{
					return "";
				}
        	}
        }
	}
    
    /**
    * cleanupList()
    * Returns JSON of a list of reports that need to be cleaned up as they are past their expiration date.
    */
    protected function GitCheck()
    {
        if ($this->method == 'GET')
        {
        	if (count($this->args) == 2)
        	{
        		$computerAPIKey = $this->args[0];
        		$GitRevision = $this->args[1];
        	
		    	if ($stmt = $this->mysqli->prepare("CALL NodeGitRevisionCheck(null, ?, ?, ?)")) {
					$stmt->bind_param('sis', $computerAPIKey, $this->ip_address, $GitRevision);  // Bind ComputerId to parameter.
					$stmt->execute();	// Execute the prepared query.
					$stmt->store_result();
			 
					clearStoredResults($this->mysqli);
	 
					if ($stmt->num_rows == 1)
					{	
						$resultRow = fetchMysqlRows($stmt);
						return $resultRow;
					}
					else //No Work Items at this time.
					{
						return "";
					}
				}
			}
        }
        else
        {
            return "Only accepts GET requests";
        }   	
    }
    
    /**
    * cleanupList()
    * Returns JSON of a list of reports that need to be cleaned up as they are past their expiration date.
    */
    protected function cleanupList()
    {
        if ($this->method == 'GET')
        {
        	if (count($this->args) == 1)
        	{
        		$computerAPIKey = $this->args[0];
        	
		    	if ($stmt = $this->mysqli->prepare("CALL ReportsToArchiveList(null, ?, ?)")) {
					$stmt->bind_param('ss', $computerAPIKey, $this->ip_address);  // Bind ComputerId to parameter.
					$stmt->execute();	// Execute the prepared query.
					$stmt->store_result();
			 
					clearStoredResults($this->mysqli);
	 
					if ($stmt->num_rows > 0)
					{	
						$resultRow = fetchMysqlRows($stmt);
						return $resultRow;
					}
					else //No Work Items at this time.
					{
						return "";
					}
				}
			}
        }
        else
        {
            return "Only accepts GET requests";
        }   	
    }
    
    /**
    * cleanupList()
    * Returns JSON of a list of reports that need to be cleaned up as they are past their expiration date.
    */
    protected function cleanupReport()
    {
        if ($this->method == 'GET')
        {
        	if (count($this->args) == 2)
        	{
        		$computerAPIKey = $this->args[0];
        		$simulationId = $this->args[1];
        	
		    	if ($stmt = $this->mysqli->prepare("CALL ArchiveReport(null, ?, ?, ?)")) {
					$stmt->bind_param('sis', $computerAPIKey, $simulationId, $this->ip_address);  // Bind ComputerId to parameter.
					$stmt->execute();	// Execute the prepared query.
					$stmt->store_result();
			 
					clearStoredResults($this->mysqli);
	 
					if ($stmt->num_rows == 1)
					{	
						$resultRow = fetchMysqlRows($stmt);
						return $resultRow;
					}
					else //No Work Items at this time.
					{
						return "";
					}
				}
			}
        }
        else
        {
            return "Only accepts GET requests";
        }   	
    }

    /**
    * Example of an Endpoint
    */
    protected function name() {
        if ($this->method == 'GET') {
            return "Your name is " . $this->verb;
        } else {
            return "Only accepts GET requests";
        }
    }
}
 
?>
