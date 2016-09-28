<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/API.class.php';

class SiteAPI extends API
{
    protected $User;

    public function __construct($request, $origin) {
        parent::__construct($request);

        $this->SimulationRepository = new SimulationRepository($this->mysqli, $this->session);
        $this->UserRepository = new UserRepository($this->mysqli, $this->session);
        $this->User = $this->UserRepository->IsUserLoggedIn();

		if (!$this->User)
		{
			throw new Exception('User not logged in.');
		}
    }
    
    /**
    * SimulationToggleHidden()
    * Toggles if a simulation is visible to the public or not.
    * Takes SimulationGUID that matches the simulation we wish to toggle.
    * Returns a an Object with 'Response' = 1 for hidden, 0 for visible, any other value for not authorized.
    */
    protected function SimulationToggleHidden()
    {
        if ($this->method == 'GET')
        {
        	if (count($this->args) == 1)
        	{
        		$simulationGUID = $this->args[0];
				
				if ($stmt = $this->mysqli->prepare("CALL SimulationToggleHidden(?, ?)")) {
					$stmt->bind_param('si', $simulationGUID, $this->User->UserId); 
					$stmt->execute();	// Execute the prepared query.
					$stmt->store_result();
			 
					clearStoredResults($this->mysqli);
	 
					if ($stmt->num_rows != 1)
					{	
						throw new Exception("Database connection error.");
					}
                    
                    $resultRow = fetchMysqlRows($stmt);
					return $resultRow[0];
				}
                
                throw new Exception("Invalid query");
			}
            
            throw new Exception("Invalid parameters");
        }
        else
        {
            throw new Exception("Only accepts GET requests");
        }   	
    }
    
    /**
    * SimulationArchiveReport()
    * Archives the HTML report of the specified Simulation.
    * Takes SimulationGUID that matches the simulation we wish to archive the HTML Report of.
    * Returns a an Object with 'Response' = 1 for hidden, 0 for visible, any other value for not authorized, and a timestamp if successful
    */
    protected function SimulationArchiveReport()
    {
        if ($this->method == 'GET')
        {
        	if (count($this->args) == 1)
        	{
        		$simulationGUID = $this->args[0];
				
				if ($stmt = $this->mysqli->prepare("CALL UserArchiveReport(?, ?)")) {
					$stmt->bind_param('si', $simulationGUID, $this->User->UserId); 
					$stmt->execute();	// Execute the prepared query.
					$stmt->store_result();
			 
					clearStoredResults($this->mysqli);
	 
					if ($stmt->num_rows != 1)
					{	
						throw new Exception("Database connection error.");
					}
                    
                    $resultRow = fetchMysqlRows($stmt);
                    
                    if ($resultRow[0]["Response"] == 1)
                    {
                        //unlink(WAREHOUSE_FOLDER . $simulationGUID . ".html");
                    }
                    
					return $resultRow[0];
				}
                
                throw new Exception("Invalid query");
			}
            
            throw new Exception("Invalid parameters");
        }
        else
        {
            throw new Exception("Only accepts GET requests");
        }   	
    }
    
    /**
    * SimulationArchive()
    * Archives the Simulation, including the HTML report, of the specified Simulation. Archived Simulations do not show up on "Your Simulations" 
    * Takes SimulationGUID that matches the simulation we wish to archive the HTML Report of.
    * Returns a an Object with 'Response' = 1 for hidden, 0 for visible, any other value for not authorized, and a timestamp if successful
    */
    protected function SimulationArchive()
    {
        if ($this->method == 'GET')
        {
        	if (count($this->args) == 1)
        	{
        		$simulationGUID = $this->args[0];
				
				if ($stmt = $this->mysqli->prepare("CALL SimulationArchive(?, ?)")) {
					$stmt->bind_param('si', $simulationGUID, $this->User->UserId); 
					$stmt->execute();	// Execute the prepared query.
					$stmt->store_result();
			 
					clearStoredResults($this->mysqli);
	 
					if ($stmt->num_rows != 1)
					{	
						throw new Exception("Database connection error.");
					}
                    
                    $resultRow = fetchMysqlRows($stmt);
                    
                    if ($resultRow[0]["Response"] == 1)
                    {
                        //unlink(WAREHOUSE_FOLDER . $simulationGUID . ".html");
                    }
                    
					return $resultRow[0];
				}
                
                throw new Exception("Invalid query");
			}
            
            throw new Exception("Invalid parameters");
        }
        else
        {
            throw new Exception("Only accepts GET requests");
        }   	
    }
    
    
    protected function SimulationList()
    {        
        if ($this->method == 'GET')
        {
            $url = parse_url($_SERVER['HTTP_REFERER']);
            
            $result_obj = json_decode($this->request, true);
            
            $simUserId = $this->User->UserId;
            
            if ($result_obj->SimUserId && $this->User->UserLevelId == 9)
            {
                $simUserId = $result_obj->SimUserId;
            }
            
            $hidden = null;
            $showControlOptions = 0;
            
            if ($this->User->UserLevelId == 9)
            {
                $showControlOptions = 1;
            }
            
            if (strtolower($url['path']) == "/yoursimulations.php" || strtolower($url['path']) == "/simulations.php")
            {
                $hidden = 2;
                $showControlOptions = 1;
            }
            
            $simulationArchived = null;
            
            if (strtolower($url['path']) == "/yoursimulations.php")
            {
                $simulationArchived = 0;//
            }
            
            if ($result_obj->Search)
            {          
                $simulations = $this->SimulationRepository->SimulationList($simUserId, $result_obj->Status, null, $hidden, $simulationArchived, $result_obj->Character, $result_obj->FightType, $result_obj->Class, $result_obj->Specialization, $result_obj->ScaleFactors, $result_obj->ReportArchived, $result_obj->IterationsLow, $result_obj->IterationsHigh, $result_obj->Server, $result_obj->ItemlevelsLow, $result_obj->ItemlevelsHigh, $result_obj->BossCountLow, $result_obj->BossCountHigh);
            }
            else
            {  
                $simulations = $this->SimulationRepository->SimulationList($simUserId, null, null, $hidden);
            }
            
            $returnObject->draw = 1;
            $returnObject->recordsTotal = count($simulations);
            $returnObject->recordsFiltered = count($simulations);
            
            foreach ($simulations as $simulation)
            {
                $tempObj = new Object();
                if ($showControlOptions != 0 && $this->User != null)
                {
                    $col0 = "";
                    if ($simulation->UserId == $this->User->UserId || $this->User->UserLevelId == 9)
                    {
                        $col0 += '<div id="simulationHidden-' . $simulation->SimulationGUID . '" data-toggle="tooltip" data-placement="right" class="simulationIcon glyphicon glyphicon-eye-';
                    
                        if($simulation->IsHidden)
                        {
                            $col0 += 'close simulationIconEnabledRed';
                        }
                        else
                        {
                            $col0 += 'open simulationIconEnabledGreen';
                        }

                        if ($this->User->HiddenSimulations || $this->User->UserLevelId == 9)
                        {
                            if ($simulation->IsHidden)
                            {
                                $col0 += ' simulationIconInteractable\" title=\"This is a hidden simulation. Click here to make it public.';
                            }
                            else
                            {
                                $col0 += ' simulationIconInteractable\" title=\"This is a public simulation. Click here to make it hidden.';
                            }
                            $col0 += ' onclick="simulationToggleHidden(\'' . $simulation->SimulationGUID . '\')';
                        }
                        else
                        {
                            if ($simulation->IsHidden)
                            {
                                $col0 += " simulationIconTooltip\" title=\"This is a hidden simulation.";
                            }
                            else
                            {
                                $col0 += " simulationIconTooltip\" title=\"This is a public simulation.";
                            } 
                        }
                        $col0 += '">\n</div>\n';
                        if ($simulation->SimulationStatusId >= 3)
                        {
                            if (!$simulation->ReportArchived)
                            {
                                $col0 += '<div id="simulationArchiveReport-' . $simulation->SimulationGUID . '" data-html="true" data-toggle="tooltip" data-placement="right" class="simulationIcon simulationIconInteractable glyphicon glyphicon-floppy-remove" title="Archive this simulation\'s HTML report." onclick="simulationArchiveReport(\'' . $simulation->SimulationGUID . '\')"></div>\n';
                            }
                            else
                            {
                                $col0 += '<div id="simulationArchiveReport-' . $simulation->SimulationGUID . '" data-html="true" data-toggle="tooltip" data-placement="right" class="simulationIcon simulationIconTooltip glyphicon glyphicon-floppy-saved" title="This simulation\'s HTML report was archived at <span class=\'dateTimeSpan\'>' . $simulation->TimeArchived . '</span>."></div>\n';
                            }

                            if (!$simulation->SimulationArchived)
                            {
                                $col0 += '<div id="simulationArchive-' . $simulation->SimulationGUID . '" data-html="true" data-toggle="tooltip" data-placement="right" class="simulationIcon simulationIconInteractable glyphicon glyphicon-remove" title="Archive this simulation and remove it from being displayed under \'Your Simulations\'. This will also archive the HTML report." onclick="simulationArchive(\'' . $simulation->SimulationGUID . '\')"></div>\n';
                            }
                            else
                            {
                                $col0 += '<div id="simulationArchive-' . $simulation->SimulationGUID . '" data-html="true" data-toggle="tooltip" data-placement="right" class="simulationIcon simulationIconTooltip glyphicon glyphicon-remove simulationIconEnabledRed" title="This simulation has been archived and is not displayed under \'Your Simulations\'. This simulation was archived at ' . $simulation->SimulationTimeArchived . ' UTC."></div>\n';
                            }
                        }
                        
                        array_push($tempObj, $col0);
                    }
                    
                    array_push($returnObject->data, $tempObj);
                }
            }
            
            return json_encode((array)$returnObject, true);
        }
    }
}
 
?>
