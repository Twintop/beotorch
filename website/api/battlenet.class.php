<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/API.class.php';

class BattleNetAPI extends API
{
    protected $User;

    public function __construct($request, $origin) {
        parent::__construct($request);

        $UserRepository = new UserRepository($this->mysqli, $this->session);
        $this->User = $UserRepository->IsUserLoggedIn();

		if (!$this->User)
		{
			throw new Exception('User not logged in.');
		}
    }
    
    /**
    * CharacterArmory()
    * Requests character armory data from Battle.net API.
    * Takes ServerId, Region, and CharacterName.
    * Returns a JSON response from Battle.net. If the character exists, also returns the internal talent information for the character's specs.
    */
    protected function CharacterArmory()
    {
        if ($this->method == 'GET')
        {
        	if (count($this->args) == 2)
        	{
        		$serverId = $this->args[0];
        		$characterName = ucfirst(strtolower($this->args[1]));
        		
        		$server = ServerGet($this->mysqli, $serverId);
        		
        		if ($server[0]["ServerId"] != $serverId)
        		{
        			throw new Exception('Invalid server.');
        		}        	
						
				if ($server[0]["RegionPrefix"] == "eu")
				{
					$locale = "en_GB";
				}
				elseif ($server[0]["RegionPrefix"] == "kr")
				{
					//$locale = "ko_KR";
					$locale = "en_US";
				}
				elseif ($server[0]["RegionPrefix"] == "tw")
				{
					//$locale = "zh_TW";
					$locale = "en_US";
				}
				else 
				{
					$locale = "en_US";
				}			    	  
				 	
				$apiCheckUrl = $server[0]["RegionAPIUrl"] . str_replace("+", "%20", urlencode($server[0]["ServerName"])) . "/" . urlencode($characterName) . "?locale=" . $locale . "&fields=talents,items,professions&apikey=" . BLIZZARD_API_KEY;
								
				$character_json = @file_get_contents($apiCheckUrl);
		
		
				if (strlen($character_json) == 0)
				{
					throw new Exception("This character is not available through the Battle.net Armory.");
				}
				
				if ($stmt = $this->mysqli->prepare("CALL CharacterTempStorageSave(?, ?, ?, ?)")) {
					$stmt->bind_param('siis', utf8_encode($characterName), $serverId, $this->User->UserId, $character_json); 
					$stmt->execute();	// Execute the prepared query.
					$stmt->store_result();
			 
					clearStoredResults($this->mysqli);
	 
					if ($stmt->num_rows != 1)
					{	
						throw new Exception("Database connection error. - " . $stmt->num_rows);
					}
				}
				
				$checkCharacter_json = json_decode($character_json, true);
                
                for($x = 0; $x < count($checkCharacter_json["talents"]); $x++)
                {                    
                    if ($checkCharacter_json["talents"][$x]["calcSpec"] != "")
                    {
                        $checkCharacter_json["talents"][$x]["dbtalents"] = GetTalentsList($this->mysqli, $checkCharacter_json["class"], 0, $checkCharacter_json["talents"][$x]["calcSpec"]);
                    }
                }
                
				return $checkCharacter_json;
			}
        }
        else
        {
            throw new Exception("Only accepts GET requests");
        }   	
    }
}
 
?>
