<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/beotorch-config.php';
 
function page_load_prep($mysqli, $session)
{
	//sec_session_start();
    //SessionManager::sessionStart(SESSION_NAME, 0, '/', SITE_ADDRESS, SECURE);
}

function sec_session_start()
{
    return;
	// Forces sessions to only use cookies.
	if (ini_set('session.use_only_cookies', 1) === FALSE)
	{
		header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
		exit();
	}
	// Gets current cookies params.
	session_name(SESSION_NAME);
	session_set_cookie_params(60*60*24*30,
		"/", 
		SITE_ADDRESS, 
		SECURE,
		true);
	session_start();			// Start the PHP session 
    //setcookie(session_name(),session_id(),time()+(60*60*24*30));
	session_regenerate_id(true);	// regenerated the session, delete the old one. 
}

function login($mysqli, $session, $enteredEmail, $enteredPassword)
{
	$login_response = 0;
	// Using prepared statements means that SQL injection is not possible.
	if ($stmt = $mysqli->prepare("CALL UserGet(null, ?)"))
	{
		$stmt->bind_param('s', $enteredEmail);  // Bind "$email" to parameter.
		$stmt->execute();	// Execute the prepared query.
		$stmt->store_result();
 		
		if ($stmt->num_rows == 1)
		{
			$userAccount = fetchMysqlRows($stmt); 
			clearStoredResults($mysqli);
			
			// hash the password with the unique salt.
		    $password = hash('sha512', $enteredPassword . $userAccount[0]['Salt']);
		    
			// If the user exists we check if the account is locked
			// from too many login attempts  
			if (checkbrute($userAccount[0]['UserId'], $mysqli) == true)
			{
				// Account is locked 
				$login_response = 4;
			}
			else
			{
				$mysqli->query("SET @UserId = '" . $mysqli->real_escape_string($userAccount[0]['UserId']) . "'");
				// Check if the account needs to be activated first, then
				// Check if the password in the database matches
				// the password the user submitted.
				if ($userAccount[0]['IsActive'] == 0)
				{
					$login_response = 3;
				}
				elseif ($userAccount[0]['Password'] == $password)
				{
					// Password is correct!		
					// Get the user-agent string of the user.
					$user_browser = $_SERVER['HTTP_USER_AGENT'];
					// XSS protection as we might print this value
					$user_id = preg_replace("/[^0-9]+/", "", $userAccount[0]['UserId']);
					$session->put('user_id', $user_id);
					// XSS protection as we might print this value
					$email = preg_replace("/[^a-zA-Z0-9_\-@.]+/", 
										  "", 
										  $userAccount[0]['Email']);
					$session->put('email', $email);
					$session->put('login_string', hash('sha512', $password . $user_browser));
                    $session->put('_last_activity', time());
					// Login successful.
					$login_response = 1;
				}
				else
				{
					// Password is not correct
					$login_response = 5;
				}
			}
		}
		else
		{
			// No user exists.
			$login_response = 6;
		}
	}
	clearStoredResults($mysqli);
	$mysqli->query("CALL LoginAttemptInsert(@UserId, " . $login_response . ", @IPAddress);");
	return $login_response;
}

function checkbrute($user_id, $mysqli)
{
	$mysqli->query("SET @UserId = '" . $mysqli->real_escape_string($user_id) . "'");
	$mysqli->query("SET @ResultCode = 5");
	$mysqli->query("SET @TimeOffset = -5");
	$sql = "CALL LoginAttemptList(@UserId, @ResultCode, @TimeOffset)";
	$stmt = $mysqli->query($sql);
	
	// If there have been more than 5 failed logins 
	if ($stmt->num_rows >= 5)
	{
		clearStoredResults($mysqli);
		return true;
	}
	else
	{
		clearStoredResults($mysqli);
		return false;
	}
}

function UserActivate($mysqli, $activationCode)
{
	if ($stmt = $mysqli->prepare("CALL UserActivate(?)"))
	{
		$stmt->bind_param('s', $activationCode);
		$stmt->execute();   // Execute the prepared query.
		$stmt->store_result();

		if ($stmt->num_rows == 1)
		{
			$activationResult = fetchMysqlRows($stmt);
			
			clearStoredResults($mysqli);	
			
			return $activationResult;				
		}
		else
		{
			clearStoredResults($mysqli);
			
			return null;
		}
	}
}

function SimulationCountsGet($mysqli, $userId = null)
{
	if ($userId == null)
	{
		$userId = "null";
	}
	
	$results = multiQuery($mysqli, "CALL SimulationCountsGet(" . $userId . ")");
	
	return $results;
}

function QueuedSimulationCount($mysqli, $userId)
{
	if (isset($_SESSION['user_id']))
	{ 
		if ($stmt = $mysqli->prepare("CALL QueuedSimulationCount(?)"))
		{
			// Bind "$user_id" to parameter. 
			$stmt->bind_param('i', $userId);
			$stmt->execute();   // Execute the prepared query.
			$stmt->store_result();
 
			if ($stmt->num_rows == 1)
			{
				// If the user exists get variables from result.
				$queueCount = fetchMysqlRows($stmt);
				
				clearStoredResults($mysqli);	
				
				if ($queueCount[0]["WeightedQueue"] == null)
				{
					$queueCount[0]["WeightedQueue"] = 0;
				}
				
				return $queueCount;	
			}
			else
			{
				clearStoredResults($mysqli);
				
				$queueCount[0]["TotalQueue"] = -1;
				$queueCount[0]["WeightedQueue"] = -1;
				
				return $queueCount;
			}
		}
	}
}

function ServerGet($mysqli, $serverId = 0)
{
	if (isset($_SESSION['user_id']))
	{ 
		$user_id = $_SESSION['user_id'];
		if ($stmt = $mysqli->prepare("CALL ServerGet(?)"))
		{
			// Bind "$user_id" to parameter. 
			$stmt->bind_param('i', $serverId);
			$stmt->execute();   // Execute the prepared query.
			$stmt->store_result();
 
			if ($stmt->num_rows == 1)
			{
				// If the user exists get variables from result.
				$server = fetchMysqlRows($stmt);
				
				clearStoredResults($mysqli);	
				
				return $server;				
			}
			else
			{
				clearStoredResults($mysqli);
				
				return null;
			}
		}
	}
}

function SimulationTypeList($mysqli)
{
	if (isset($_SESSION['user_id']))
	{ 
		$user_id = $_SESSION['user_id'];
		if ($stmt = $mysqli->prepare("CALL SimulationTypeList()"))
		{
			$stmt->execute();   // Execute the prepared query.
			$stmt->store_result();
 
			if ($stmt->num_rows > 0)
			{
				// If the user exists get variables from result.
				$simulationTypes = fetchMysqlRows($stmt);
				
				clearStoredResults($mysqli);	
				
				return $simulationTypes;				
			}
			else
			{
				clearStoredResults($mysqli);
				
				return null;
			}
		}
	}
}

function ServerList($mysqli)
{
	if (isset($_SESSION['user_id']))
	{ 
		$user_id = $_SESSION['user_id'];
		if ($stmt = $mysqli->prepare("CALL ServerList(null)"))
		{
			$stmt->execute();   // Execute the prepared query.
			$stmt->store_result();
 
			if ($stmt->num_rows > 0)
			{
				$servers = fetchMysqlRows($stmt);
				
				clearStoredResults($mysqli);	
				
				return $servers;				
			}
			else
			{
				clearStoredResults($mysqli);
				
				return null;
			}
		}
	}
}

function RegionGet($mysqli, $regionId = 0, $serverId = 0)
{
	if (isset($_SESSION['user_id']))
	{ 
		$user_id = $_SESSION['user_id'];
		if ($stmt = $mysqli->prepare("CALL RegionGet(?, ?)"))
		{
			// Bind "$user_id" to parameter. 
			$stmt->bind_param('ii', $regionId, $serverId);
			$stmt->execute();   // Execute the prepared query.
			$stmt->store_result();
 
			if ($stmt->num_rows == 1)
			{
				// If the user exists get variables from result.
				$region = fetchMysqlRows($stmt);
				
				clearStoredResults($mysqli);	
				
				return $region;				
			}
			else
			{
				clearStoredResults($mysqli);
				
				return null;
			}
		}
	}
}

function RegionList($mysqli)
{
	if (isset($_SESSION['user_id']))
	{ 
		$user_id = $_SESSION['user_id'];
		if ($stmt = $mysqli->prepare("CALL RegionList()"))
		{
			$stmt->execute();   // Execute the prepared query.
			$stmt->store_result();
 
			if ($stmt->num_rows > 0)
			{
				$regions = fetchMysqlRows($stmt);
				
				clearStoredResults($mysqli);	
				
				return $regions;				
			}
			else
			{
				clearStoredResults($mysqli);
				
				return null;
			}
		}
	}
}

function FightTypeList($mysqli)
{
	if (isset($_SESSION['user_id']))
	{ 
		$user_id = $_SESSION['user_id'];
		if ($stmt = $mysqli->prepare("CALL ServerList(null)"))
		{
			$stmt->execute();   // Execute the prepared query.
			$stmt->store_result();
 
			if ($stmt->num_rows > 0)
			{
				// If the user exists get variables from result.
				$servers = fetchMysqlRows($stmt);
				
				clearStoredResults($mysqli);	
				
				return $servers;				
			}
			else
			{
				clearStoredResults($mysqli);
				
				return null;
			}
		}
	}
}

function GetTalentsList($mysqli, $classId, $specId, $calcSpec, $patchVersion = "7.0")
{
    if ($specId == null)
    {
        $specId = 0;
    }
    
    if ($stmt = $mysqli->prepare("CALL TalentsList(?, ?, ?, ?)")) {
        $stmt->bind_param('iiss', $classId, $specId, $calcSpec, $patchVersion); 
        $stmt->execute();	// Execute the prepared query.
        $stmt->store_result();

        clearStoredResults($mysqli);

        if ($stmt->num_rows != 21)
        {	
            throw new Exception("Database connection error - " . $stmt->num_rows . " - " . $calcSpec . " - " . $specId . " - " . $classId);
        }
        else
        {
            $resultRow = fetchMysqlRows($stmt);
            
            return $resultRow;
        }
    }
}

function CharacterList($mysqli, $userid)
{
	if (isset($_SESSION['user_id']))
	{ 
		if ($userid > 0)
		{
			$user_id = $userid;
		}
		else
		{
			$user_id = $_SESSION['user_id'];
		}
		$user_id = $_SESSION['user_id'];
		if ($stmt = $mysqli->prepare("CALL CharacterList(?)"))
		{
			// Bind "$user_id" to parameter. 
			$stmt->bind_param('i', $user_id);
			$stmt->execute();   // Execute the prepared query.
			$stmt->store_result();
 
			if ($stmt->num_rows > 0)
			{
				// If the user exists get variables from result.
				$characterList = fetchMysqlRows($stmt);
				
				clearStoredResults($mysqli);	
				
				return $characterList;				
			}
			else
			{
				clearStoredResults($mysqli);
				
				return null;
			}
		}
	}
}

function CharacterTempStorageGet($mysqli, $characterName, $serverId)
{
	if (isset($_SESSION['user_id']))
	{ 
		$user_id = $_SESSION['user_id'];
		if ($stmt = $mysqli->prepare("CALL CharacterTempStorageGet(?, ?, ?)"))
		{
			// Bind "$user_id" to parameter. 
			$stmt->bind_param('sii', $characterName, $serverId, $user_id);
			$stmt->execute();   // Execute the prepared query.
			$stmt->store_result();
 
			if ($stmt->num_rows == 1)
			{
				// If the user exists get variables from result.
				$charJSON = fetchMysqlRows($stmt);
				
				clearStoredResults($mysqli);	
				
				return $charJSON;				
			}
			else
			{
				clearStoredResults($mysqli);
				
				return null;
			}
		}
	}
}

function GetStatDetails($stat)
{
	$returnArray = [];
	$returnArray["name"] = $stat;
	$returnArray["group"] = 0;
	switch (strtolower($stat))
	{
		case "intellect":
		case "intel":
		case "int":
			$returnArray["name"] = "Int";
			$returnArray["group"] = 0;
			break;
		case "agility":
		case "agili":
		case "agi":
			$returnArray["name"] = "Agi";
			$returnArray["group"] = 0;
			break;
		case "strength":
		case "stren":
		case "str":
			$returnArray["name"] = "Str";
			$returnArray["group"] = 0;
			break;
		case "critical strike":
		case "critical":
		case "crit":
			$returnArray["name"] = "Crit";
			$returnArray["group"] = 1;
			break;
		case "haste":
		case "hast":
		case "has":
			$returnArray["name"] = "Haste";
			$returnArray["group"] = 1;
			break;
		case "mastery":
		case "mast":
		case "mas":
			$returnArray["name"] = "Mast";
			$returnArray["group"] = 1;
			break;
		case "multistrike":
		case "multi strike":
		case "multi":
		case "mult":
		case "ms":
		case "mul":
			$returnArray["name"] = "Multi";
			$returnArray["group"] = 1;
			break;
		case "versatility":
		case "versa":
		case "vers":
		case "ver":
			$returnArray["name"] = "Vers";
			$returnArray["group"] = 1;
			break;
		case "attack power":
		case "attackpower":
		case "att":
		case "ap":
			$returnArray["name"] = "AP";
			$returnArray["group"] = 1;
			break;
		case "spell power":
		case "spellpower":
		case "spell":
		case "sp":
			$returnArray["name"] = "SP";
			$returnArray["group"] = 1;
			break;
		case "armor":
		case "arm":
			$returnArray["name"] = "Arm";
			$returnArray["group"] = 1;
			break;
		case "bonus armor":
		case "bonusarmor":
		case "barm":
		case "ba":
			$returnArray["name"] = "BArm";
			$returnArray["group"] = 1;
			break;
		case "spirit":
		case "spir":
		case "spi":
			$returnArray["name"] = "Spi";
			$returnArray["group"] = 1;
			break;
		case "stamina":
		case "stam":
		case "sta":
			$returnArray["name"] = "Stam";
			$returnArray["group"] = 1;
			break;
		case "weapon dps":
		case "weapondps":
		case "wdps":
			$returnArray["name"] = "Wdps";
			$returnArray["group"] = 2;
			break;
		case "offhand weapon dps":
		case "offhandweapondps":
		case "ohwdps":
			$returnArray["name"] = "WOHdps";
			$returnArray["group"] = 2;
			break;
		case "avoidance":
		case "avoid":
			$returnArray["name"] = "Avoid";
			$returnArray["group"] = 3;
			break;
		case "leech":
		case "ls":
		case "lee":
			$returnArray["name"] = "Leech";
			$returnArray["group"] = 3;
			break;
		case "movementspeed":
		case "runspeed":
		case "speed":
			$returnArray["name"] = "Speed";
			$returnArray["group"] = 3;
			break;
		default:
			break;
	}
	return $returnArray;
}

//privacyState 0 = don't use custom names, 1 = use names
function include_simulationsTable($simulations, $privacyState = 0, $showControlOptions = 0, $User = null)
{
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/simulations_table.inc.old.php");
}

function include_simulationsTable_params($simulationParams)
{
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/simulations_table.inc.php");
}

function validateGUID($guid)
{
    if (preg_match('/^\{?[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}\}?$/', $guid))
    {
        return true;
    }
    else
    {
        return false;
    }
}

#------------------------------------------
function esc_url($url)
#------------------------------------------
{
    if ('' == $url)
    {
        return $url;
    }
 
    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
 
    $strip = array('%0d', '%0a', '%0D', '%0A');
    $url = (string) $url;
 
    $count = 1;
    while ($count)
    {
        $url = str_replace($strip, '', $url, $count);
    }
 
    $url = str_replace(';//', '://', $url);
 
    $url = htmlentities($url);
 
    $url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);
 
    if ($url[0] !== '/')
    {
        // We're only interested in relative links from $_SERVER['PHP_SELF']
        return '';
    }
    else
    {
        return $url;
    }
}

#------------------------------------------
function clearStoredResults($mysqli_link)
#------------------------------------------
{
    while($mysqli_link->next_result())
    {
    	if ($l_result = $mysqli_link->store_result())
    	{
    		$l_result->free();
		}
    }
}

#-------------------------------------------
function fetchMysqlRows($result)
#-------------------------------------------
{    
    $array = array();
    
    if ($result instanceof mysqli_stmt)
    {
        $result->store_result();
        
        $variables = array();
        $data = array();
        $meta = $result->result_metadata();
        
        while ($field = $meta->fetch_field())
        {
            $variables[] = &$data[$field->name]; // pass by reference
        }
        
        call_user_func_array(array($result, 'bind_result'), $variables);
        
        $i=0;
        while ($result->fetch())
        {
            $array[$i] = array();
            foreach ($data as $k => $v)
            {
                $array[$i][$k] = $v;
            }
            $i++;
            
            // don't know why, but when I tried $array[] = $data, I got the same one result in all rows
        }
    }
    elseif ($result instanceof mysqli_result)
    {
        while ($row = $result->fetch_assoc())
            $array[] = $row;
    }
    
    return $array;
}

#-------------------------------------------
function multiQuery(mysqli $mysqli, $query)
#-------------------------------------------
{
	$array = array();
    if ($mysqli->multi_query($query))
    {
    	$x = 0;
		do
		{
			$array[$x] = array();
		    if ($result = $mysqli->store_result())
		    {
		    	$fields = array();
		    	$z = 0;
				while ($fieldInfo = mysqli_fetch_field($result))
				{
					$fields[$z] = $fieldInfo->name;
					$z++;
				}
		    	$y = 0;
		        while ($row = $result->fetch_row())
		        {
		        	$array[$x][$y] = array();
		            $z = 0;
		            foreach ($row as $key => $value) 
		            {
			            $array[$x][$y][$fields[$z]] = $value;
			            $z++;
		            }
		            $y++;
		        }
		        $result->free();
		    }
		    $x++;
		} while ($mysqli->more_results() && $mysqli->next_result());
    }
    return $array;
}
?>
