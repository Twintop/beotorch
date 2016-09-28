<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/Models/Simulation.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/Models/SimulationActor.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/Models/SimulationLog.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/Models/Talent.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database_connect.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';

/**
 * SimulationRepository
 */
class SimulationRepository {
    
    protected $mysqli;
    protected $session;
    
    function SimulationRepository($mysqli, $session)
    {
        $this->mysqli = $mysqli;
        $this->session = $session;
    }
    
    function SimulationGetByGUID($simulationGUID, $getCharacterJSON = 0, $getSimLog = 0)
    {        
        if (validateGUID($simulationGUID))
        {
            $results = multiQuery($this->mysqli, "CALL SimulationGetByGUID('" . $simulationGUID . "', " . $getCharacterJSON . ", " . $getSimLog .")");

            if ($results[0] != null)
            {
                return $this->FillSimulationObjectFromParams($results[0][0], $results[1], $results[2]);
            }
            else
            {
                return false;
            }
        }
        else 
        {
            return false;
        }
    }   
    
    function FillSimulationObjectFromParams($simulationArray, $actorsArray, $logsArray)
    {
        $Simulation = $this->FillSimulationObject($simulationArray);
        
        $Simulation->SimulationActors = array();
        
        if ($actorsArray != null)
        {
            foreach ($actorsArray as $simActor)
            {        
                $actor = $this->FillSimulationActorObject($simActor);

                $actor->Talents = array();

                for ($x = 1; $x <= 7; $x++)
                {
                    $talent = new Talent();
                    $talent->TalentId = $simActor["Talent" . $x . "Id"];
                    $talent->Name = $simActor["Talent" . $x . "Name"];
                    $talent->Icon = $simActor["Talent" . $x . "Icon"];
                    $talent->SpellId = $simActor["Talent" . $x . "SpellId"];
                    $talent->Row = $simActor["Talent" . $x . "Row"];
                    $talent->Column = $simActor["Talent" . $x . "Column"];
                    $talent->ClassId = $simActor["ClassId"];
                    $talent->ClassName = $simActor["ClassName"];
                    $talent->SpecializationId = $simActor["SpecializationId"];
                    $talent->SpecializationName = $simActor["SpecializationName"];

                    $actor->Talents[] = $talent;
                }

                $Simulation->SimulationActors[] = $actor;
            }
        }
        
        if (count($Simulation->SimulationActors) > 1)
        {
            usort($Simulation->SimulationActors, function ($a, $b)
            {
                return $a->DPS == $b->DPS ? 0 : ($a->DPS < $b->DPS) ? 1 : -1;
            });
        }
        
        $Simulation->SimulationLogs = array();
        
        if ($logsArray != null)
        {
            foreach ($logsArray as $simulationLogEntry)
            {
                $Simulation->SimulationLogs[] = $this->FillSimulationLogObject($simulationLogEntry);
            }
        }
        
        return $Simulation;
    }

    function SimulationList($userid = -1, $status = null, $recordCount = 1000000, $isHidden = 0, $isSimulationArchived = 0, $character = null, $fightType = null, $class = null, $specialization = null, $scaleFactors = null, $reportArchived = null, $iterationsLow = null, $iterationsHigh = null, $serverId = null, $itemlevelsLow = null, $itemlevelsHigh = null, $bossCountLow = null, $bossCountHigh = null)
    {
        if ($userid > 0)
        {
            $user_id = $userid;
        }
        elseif (($userid == -1 || $userid == null) && $this->session->get('user_id'))
        {
            $user_id = $this->session->get('user_id');
        }
        else
        {
            $user_id = 0;
        }

        if ($isHidden < 0)
        {
            $isHidden = 0;
        }
        else if ($isHidden > 1)
        {
            $isHidden = 2;
        }
        else
        {
            $isHidden = 0;
        }

        if ($isSimulationArchived < 0)
        {
            $isSimulationArchived = 0;
        }
        else if ($isSimulationArchived > 1)
        {
            $isSimulationArchived = 2;
        }
        else
        {
            $isSimulationArchived = 0;
        }
        
        if ($recordCount < 1)
        {
            $recordCount = 1000000;
        }

        if (strtolower($scaleFactors) == "true")
        {
            $scaleFactorsString = "yes";
        }
        else if (strtolower($scaleFactors) == "false")
        {
            $scaleFactorsString = "no";
        }
        else
        {
            $scaleFactorsString = "any";
        }

        if (strtolower($reportArchived) == "true")
        {
            $reportArchivedString = "yes";
        }
        else if (strtolower($reportArchived) == "false")
        {
            $reportArchivedString = "no";
        }
        else
        {
            $reportArchivedString = "any";
        }

        if ($stmt = $this->mysqli->prepare("CALL SimulationList(?, ?, ?, ?, null, null, null, null, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"))
        {
            $stmt->bind_param('iiiiiiissiiiiiiiii', $user_id, utf8_encode($character), $status, $fightType,
                                             $recordCount, $class, $specialization,
                                             $scaleFactorsString, $reportArchivedString,
                                             $iterationsLow, $iterationsHigh, $serverId, $itemlevelsLow, $itemlevelsHigh,
                                             $bossCountLow, $bossCountHigh, $isHidden, $isSimulationArchived);
                        
            $stmt->execute();   // Execute the prepared query.
            $stmt->store_result();

            $SimulationList = array();
            
            if ($stmt->num_rows > 0)
            {
                $simList = fetchMysqlRows($stmt);
                clearStoredResults($this->mysqli);	

                foreach ($simList as $sim)
                {                    
                    $Simulation = $this->FillSimulationObject($sim);

                    $Simulation->SimulationActors = array();

                    $actor = $this->FillSimulationActorObject($sim);

                    $actor->Talents = array();

                    for ($x = 1; $x <= 7; $x++)
                    {
                        $talent = new Talent();
                        $talent->TalentId = $sim["Talent" . $x . "Id"];
                        $talent->Name = $sim["Talent" . $x . "Name"];
                        $talent->Icon = $sim["Talent" . $x . "Icon"];
                        $talent->SpellId = $sim["Talent" . $x . "SpellId"];
                        $talent->Row = $sim["Talent" . $x . "Row"];
                        $talent->Column = $sim["Talent" . $x . "Column"];
                        $talent->ClassId = $sim["ClassId"];
                        $talent->ClassName = $sim["ClassName"];
                        $talent->SpecializationId = $sim["SpecializationId"];
                        $talent->SpecializationName = $sim["SpecializationName"];

                        $actor->Talents[] = $talent;
                    }

                    $Simulation->SimulationActors[] = $actor;
                    
                    $SimulationList[] = $Simulation;
                }
            }
            else
            {
                clearStoredResults($this->mysqli);
            }
            return $SimulationList;
        }
    }

    function SimulationList2($userid = -1, $status = null, $recordCount = 1000000, $isHidden = 0, $isSimulationArchived = 0, $character = null, $fightType = null, $class = null, $specialization = null, $scaleFactors = null, $reportArchived = null, $iterationsLow = null, $iterationsHigh = null, $serverId = null, $itemlevelsLow = null, $itemlevelsHigh = null, $bossCountLow = null, $bossCountHigh = null)
    {
        if ($userid > 0)
        {
            $user_id = $userid;
        }
        elseif (($userid == -1 || $userid == null) && $this->session->get('user_id'))
        {
            $user_id = $this->session->get('user_id');
        }
        else
        {
            $user_id = 0;
        }

        if ($isHidden < 0)
        {
            $isHidden = 0;
        }
        else if ($isHidden > 1)
        {
            $isHidden = 2;
        }
        else
        {
            $isHidden = 0;
        }

        if ($isSimulationArchived < 0)
        {
            $isSimulationArchived = 0;
        }
        else if ($isSimulationArchived > 1)
        {
            $isSimulationArchived = 2;
        }
        else
        {
            $isSimulationArchived = 0;
        }
        
        if ($recordCount < 1)
        {
            $recordCount = 1000000;
        }

        if (strtolower($scaleFactors) == "true")
        {
            $scaleFactorsString = "yes";
        }
        else if (strtolower($scaleFactors) == "false")
        {
            $scaleFactorsString = "no";
        }
        else
        {
            $scaleFactorsString = "any";
        }

        if (strtolower($reportArchived) == "true")
        {
            $reportArchivedString = "yes";
        }
        else if (strtolower($reportArchived) == "false")
        {
            $reportArchivedString = "no";
        }
        else
        {
            $reportArchivedString = "any";
        }

        if ($stmt = $this->mysqli->prepare("CALL SimulationList2(?, ?, ?, ?, null, null, null, null, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"))
        {
            $stmt->bind_param('iiiiiiissiiiiiiiii', $user_id, utf8_encode($character), $status, $fightType,
                                             $recordCount, $class, $specialization,
                                             $scaleFactorsString, $reportArchivedString,
                                             $iterationsLow, $iterationsHigh, $serverId, $itemlevelsLow, $itemlevelsHigh,
                                             $bossCountLow, $bossCountHigh, $isHidden, $isSimulationArchived);
                        
            $stmt->execute();   // Execute the prepared query.
            $stmt->store_result();

            $SimulationList = array();
            
            if ($stmt->num_rows > 0)
            {
                $simList = fetchMysqlRows($stmt);
                clearStoredResults($this->mysqli);	

                foreach ($simList as $sim)
                {                    
                    $Simulation = $this->FillSimulationObject($sim);

                    $Simulation->SimulationActors = array();

                    $actor = $this->FillSimulationActorObject($sim);

                    $actor->Talents = array();

                    for ($x = 1; $x <= 7; $x++)
                    {
                        $talent = new Talent();
                        $talent->TalentId = $sim["Talent" . $x . "Id"];
                        $talent->Name = $sim["Talent" . $x . "Name"];
                        $talent->Icon = $sim["Talent" . $x . "Icon"];
                        $talent->SpellId = $sim["Talent" . $x . "SpellId"];
                        $talent->Row = $sim["Talent" . $x . "Row"];
                        $talent->Column = $sim["Talent" . $x . "Column"];
                        $talent->ClassId = $sim["ClassId"];
                        $talent->ClassName = $sim["ClassName"];
                        $talent->SpecializationId = $sim["SpecializationId"];
                        $talent->SpecializationName = $sim["SpecializationName"];

                        $actor->Talents[] = $talent;
                    }

                    $Simulation->SimulationActors[] = $actor;
                    
                    $SimulationList[] = $Simulation;
                }
            }
            else
            {
                clearStoredResults($this->mysqli);
            }
            return $SimulationList;
        }
    }
    
    function QueueSimulationOLD($serverId, $characterName, $iterations, $fightType, $rawJSON, $spec, $level, $itemLevel, $classId, $raceId, $gender, $thumbnail, $scaleFactors, $fightLength, $fightLengthVariance, $bossCount, $simulationName, $armorySpec, $simulationRole, $tmiWindow, $tmiBoss, $calcTalent, $simTalent, $internalTalentIds)
    {
        if ($this->session->get('user_id'))
        { 
            $user_id = $this->session->get('user_id');
            if ($stmt = $this->mysqli->prepare("CALL SimulationInsert(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"))
            {
                // Bind "$user_id" to parameter. 
                $stmt->bind_param('iisiiiiiisiidssssiisssissiiiiiii', $user_id, $serverId, $characterName, $iterations, $fightType, $level, $classId, $raceId, $gender, $thumbnail, $scaleFactors, $fightLength, $fightLengthVariance, $rawJSON, $spec["calcSpec"], $calcTalent, $spec["calcGlyph"], $itemLevel, $bossCount, $simulationName, $armorySpec, $simulationRole, $tmiWindow, $tmiBoss, $simTalent, $internalTalentIds[0], $internalTalentIds[1], $internalTalentIds[2], $internalTalentIds[3], $internalTalentIds[4], $internalTalentIds[5], $internalTalentIds[6]);
                $stmt->execute();   // Execute the prepared query.
                $stmt->store_result();

                if ($stmt->num_rows == 1)
                {
                    // If the user exists get variables from result.
                    $result = fetchMysqlRows($stmt);
                    clearStoredResults($this->mysqli);

                    if ($result[0]["SimulationId"] > 0)
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
                else
                {
                    clearStoredResults($this->mysqli);
                    return false;
                }
            }
        }
    }       
    
    function QueueSimulation($iterations, $fightType, $scaleFactors, $fightLength, $fightLengthVariance, $bossCount, $simulationName, $tmiWindow, $tmiBoss, $hideSimulation = 0, $customProfile = "", $simcVersion)
    {
        if ($this->session->get('user_id'))
        { 
            $user_id = $this->session->get('user_id');
            if ($stmt = $this->mysqli->prepare("CALL SimulationInsert(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"))
            {
                $stmt->bind_param('iiiiidisisiss', $user_id, $iterations, $fightType, $scaleFactors, $fightLength, $fightLengthVariance, $bossCount, $simulationName, $tmiWindow, $tmiBoss, $hideSimulation, $customProfile, $simcVersion);
                $stmt->execute();   // Execute the prepared query.
                $stmt->store_result();

                if ($stmt->num_rows == 1)
                {
                    // If the user exists get variables from result.
                    $result = fetchMysqlRows($stmt);
                    clearStoredResults($this->mysqli);

                    if ($result[0]["SimulationId"] > 0)
                    {
                        return $result[0]["SimulationId"];
                    }
                    else
                    {
                        return false;
                    }
                }
                else
                {
                    clearStoredResults($this->mysqli);
                    return false;
                }
            }
        }
    }
    
    function SimulationActorInsert($simulationId, $serverId, $characterName, $rawJSON, $spec, $level, $itemLevel, $classId, $raceId, $gender, $thumbnail, $armorySpec, $simulationRole, $calcTalent, $simTalent, $internalTalentIds, $specializationId = null, $IsCustomActor = 0, $CustomActorName = null)
    {
        if ($stmt = $this->mysqli->prepare("CALL SimulationActorInsert(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"))
        {
            $specName = null;
            $specGlyph = null;
            if ($spec != null)
            {
                $specName = $spec["calcSpec"];            
                $specGlyph = $spec["calcGlyph"];
            }
            
            // Bind "$user_id" to parameter. 
            $stmt->bind_param('iisiiiisssssisssiiiiiiiiis', $simulationId, $serverId, utf8_encode($characterName), $level, $classId, $raceId, $gender, $thumbnail, $rawJSON, $specName, $calcTalent, $specGlyph, $itemLevel, $armorySpec, $simulationRole, $simTalent, $internalTalentIds[0], $internalTalentIds[1], $internalTalentIds[2], $internalTalentIds[3], $internalTalentIds[4], $internalTalentIds[5], $internalTalentIds[6], $specializationId, $IsCustomActor, $CustomActorName);
            $stmt->execute();   // Execute the prepared query.
            $stmt->store_result();

            if ($stmt->num_rows == 1)
            {
                // If the user exists get variables from result.
                $result = fetchMysqlRows($stmt);
                clearStoredResults($this->mysqli);

                if ($result[0]["SimulationActorId"] > 0)
                {
                    return $result[0]["SimulationActorId"];
                }
                else
                {
                    return false;
                }
            }
            else
            {                
                clearStoredResults($this->mysqli);
                return false;
            }
        }
    }
    
    protected function FillSimulationObject($simulation)
    {
        $obj = new Simulation();
        
        foreach ($simulation as $key => $value)
        {
            switch (strtoupper($key))
            {
                case "USERID":
                    $obj->UserId = $value;
                    break;
                case "EMAIL":
                    $obj->Email = $value;
                    break;
                case "SIMULATIONID":
                    $obj->SimulationId = $value;
                    break;
                case "SIMULATIONNAME":
                    $obj->SimulationName = $value;
                    break;
                case "SIMULATIONGUID":
                    $obj->SimulationGUID = $value;
                    break;
                case "BOSSCOUNT":
                    $obj->BossCount = $value;
                    break;
                case "ITERATIONS":
                    $obj->Iterations = $value;
                    break;
                case "SIMULATIONLENGTH":
                    $obj->SimulationLength = $value;
                    break;
                case "SIMULATIONLENGTHVARIANCE":
                    $obj->SimulationLengthVariance = $value;
                    break;
                case "TIMEQUEUED":
                    $obj->TimeQueued = $value;
                    break;
                case "TIMECOMPLETED":
                    $obj->TimeCompleted = $value;
                    break;
                case "SIMULATIONTYPEID":
                    $obj->SimulationTypeId = $value;
                    break;
                case "SIMULATIONTYPESYSTEMNAME":
                    $obj->SimulationTypeSystemName = $value;
                    break;
                case "SIMULATIONTYPEFRIENDLYNAME":
                    $obj->SimulationTypeFriendlyName = $value;
                    break;
                case "SIMULATIONTYPEDESCRIPTION":
                    $obj->SimulationTypeDescription = $value;
                    break;
                case "SIMULATIONLOGID":
                    $obj->SimulationLogId = $value;
                    break;
                case "TMIWINDOW":
                    $obj->TMIWindow = $value;
                    break;
                case "TMIBOSS":
                    $obj->TMIBoss = $value;
                    break;
                case "SCALEFACTORS":
                    $obj->ScaleFactors = $value;
                    break;
                case "REPORTARCHIVED":
                    $obj->ReportArchived = $value;
                    break;
                case "TIMEARCHIVED":
                    $obj->TimeArchived = $value;
                    break;
                case "SIMULATIONRAWLOG":
                    $obj->SimulationRawLog = $value;
                    break;
                case "QUEUEPOSITION":
                    $obj->QueuePosition = $value;
                    break;
                case "TOTALQUEUE":
                    $obj->TotalQueue = $value;
                    break;
                case "SIMULATIONLOGTIME":
                    $obj->SimulationLogTime = $value;
                    break;
                case "SIMULATIONSTATUSID":
                    $obj->SimulationStatusId = $value;
                    break;
                case "STATUSNAME":
                    $obj->StatusName = $value;
                    break;
                case "STATUSDESCRIPTION":
                    $obj->StatusDescription = $value;
                    break;
                case "ACTORCOUNT":
                    $obj->ActorCount = $value;
                    break;
                case "ISHIDDEN":
                    $obj->IsHidden = $value;
                    break;
                case "CUSTOMPROFILE":
                    $obj->CustomProfile = $value;
                    break;
                case "SIMULATIONCRAFTVERSION":
                    $obj->SimulationCraftVersion = $value;
                    break;
                case "GAMEVERSION":
                    $obj->GameVersion = $value;
                    break;
                case "SIMULATIONARCHIVED":
                    $obj->SimulationArchived = $value;
                    break;
                case "SIMULATIONTIMEARCHIVED":
                    $obj->SimulationTimeArchived = $value;
                    break;
                default:
                    break;
            }
        }
        
        return $obj;
    } 
    
    protected function FillSimulationActorObject($simulation)
    {
        $obj = new SimulationActor();
        
        $obj->Scaling = array();
        
        foreach ($simulation as $key => $value)
        {
            switch (strtoupper($key))
            {
                case "SIMULATIONACTORID":
                    $obj->SimulationActorId = $value;
                    break;
                case "SIMULATIONID":
                    $obj->SimulationId = $value;
                    break;
                case "SIMULATIONGUID":
                    $obj->SimulationGUID = $value;
                    break;
                case "CHARACTERID":
                    $obj->CharacterId = $value;
                    break;
                case "CHARACTERNAME":
                    $obj->CharacterName = utf8_decode($value);
                    break;
                case "LEVEL":
                    $obj->Level = $value;
                    break;
                case "ITEMLEVEL":
                    $obj->ItemLevel = $value;
                    break;
                case "CALCTALENT":
                    $obj->CalcTalent = $value;
                    break;
                case "SIMTALENT":
                    $obj->SimTalent = $value;
                    break;
                case "CALCSPEC":
                    $obj->CalcSpec = $value;
                    break;
                case "CALCCLASS":
                    $obj->CalcClass = $value;
                    break;
                case "CALCGLYPH":
                    $obj->CalcGlyph = $value;
                    break;
                case "GENDER":
                    $obj->Gender = $value;
                    break;
                case "GENDERNAME":
                    $obj->GenderName = $value;
                    break;
                case "THUMBNAILURL":
                    $obj->ThumbnailURL = $value;
                    break;
                case "SERVERID":
                    $obj->ServerId = $value;
                    break;
                case "SERVERNAME":
                    $obj->ServerName = $value;
                    break;
                case "SERVERTYPE":
                    $obj->ServerType = $value;
                    break;
                case "RACEID":
                    $obj->RaceId = $value;
                    break;
                case "RACENAME":
                    $obj->RaceName = $value;
                    break;
                case "FACTION":
                    $obj->Faction = $value;
                    break;
                case "FACTIONNAME":
                    $obj->FactionName = $value;
                    break;
                case "FACTIONCOLOR":
                    $obj->FactionColor = $value;
                    break;
                case "CLASSID":
                    $obj->ClassId = $value;
                    break;
                case "CLASSNAME":
                    $obj->ClassName = $value;
                    break;
                case "CLASSCOLOR":
                    $obj->ClassColor = $value;
                    break;
                case "ARMORYSPEC":
                    $obj->ArmorySpec = $value;
                    break;
                case "SPECIALIZATIONID":
                    $obj->SpecializationId = $value;
                    break;
                case "SPECIALIZATIONNAME":
                    $obj->SpecializationName = $value;
                    break;
                case "SPECIALIZATIONPOSITION":
                    $obj->SpecializationPosition = $value;
                    break;
                case "SPECIALIZATIONROLE":
                    $obj->SpecializationRole = $value;
                    break;
                case "SPECIALIZATIONPRIMARYSTAT":
                    $obj->SpecializationPrimaryStat = $value;
                    break;
                case "SIMULATIONROLE":
                    $obj->SimulationRole = $value;
                    break;
                case "REGIONID":
                    $obj->RegionId = $value;
                    break;
                case "REGIONNAME":
                    $obj->RegionName = $value;
                    break;
                case "REGIONURL":
                    $obj->RegionURL = $value;
                    break;
                case "REGIONPREFIX":
                    $obj->RegionPrefix = $value;
                    break;
                case "REGIONAPIURL":
                    $obj->RegionAPIUrl = $value;
                    break;
                case "REGIONTHUMBNAILURL":
                    $obj->RegionThumbnailURL = $value;
                    break;
                case "DPS":
                    $obj->DPS = $value;
                    break;
                case "TMI":
                    $obj->TMI = $value;
                    break;
                case "DTPS":
                    $obj->DTPS = $value;
                    break;
                case "HPS":
                    $obj->HPS = $value;
                    break;
                case "APS":
                    $obj->APS = $value;
                    break;
                case "ISCUSTOMACTOR":
                    $obj->IsCustomActor = $value;
                    break;
                case "CUSTOMACTORNAME":
                    $obj->CustomActorName = $value;
                    break;
                case "SCALEARMOR":
                    $obj->Scaling["Armor"] = $value;
                    break;
                case "SCALEATTACKPOWER":
                    $obj->Scaling["AttackPower"] = $value;
                    break;
                case "SCALEAVOIDANCE":
                    $obj->Scaling["Avoidance"] = $value;
                    break;
                case "SCALEBONUSARMOR":
                    $obj->Scaling["BonusArmor"] = $value;
                    break;
                case "SCALECRIT":
                    $obj->Scaling["Crit"] = $value;
                    break;
                case "SCALEHASTE":
                    $obj->Scaling["Haste"] = $value;
                    break;
                case "SCALELEECH":
                    $obj->Scaling["Leech"] = $value;
                    break;
                case "SCALEMASTERY":
                    $obj->Scaling["Mastery"] = $value;
                    break;
                case "SCALEMOVEMENTSPEED":
                    $obj->Scaling["MovementSpeed"] = $value;
                    break;
                case "SCALEMULTISTRIKE":
                    $obj->Scaling["Multistrike"] = $value;
                    break;
                case "SCALEOFFHANDWEAPONDPS":
                    $obj->Scaling["OffhandWeaponDPS"] = $value;
                    break;
                case "SCALEPRIMARYSTAT":
                    $obj->Scaling["PrimaryStat"] = $value;
                    break;
                case "SCALESPELLPOWER":
                    $obj->Scaling["SpellPower"] = $value;
                    break;
                case "SCALESPIRIT":
                    $obj->Scaling["Spirit"] = $value;
                    break;
                case "SCALESTAMINA":
                    $obj->Scaling["Stamina"] = $value;
                    break;
                case "SCALEVERSATILITY":
                    $obj->Scaling["Versatility"] = $value;
                    break;
                case "SCALEWEAPONDPS":
                    $obj->Scaling["WeaponDPS"] = $value;
                    break;
                case "CHARACTERJSON":
                    $obj->CharacterJSON = $value;
                    break;
                default:
                    break;
            }
        }
        
        return $obj;
    } 
    
    protected function FillSimulationLogObject($simulationlog)
    {
        $obj = new SimulationLog();
        
        foreach ($simulationlog as $key => $value)
        {
            switch (strtoupper($key))
            {
                case "SIMULATIONLOGID":
                    $obj->SimulationLogId = $value;
                    break;
                case "SIMULATIONLOGTIME":
                    $obj->SimulationLogTime = $value;
                    break;
                case "ISARCHIVE":
                    $obj->IsArchive = $value;
                    break;
                case "SIMULATIONSTATUSID":
                    $obj->SimulationStatusId = $value;
                    break;
                case "STATUSNAME":
                    $obj->StatusName = $value;
                    break;
                case "STATUSDESCRIPTION":
                    $obj->StatusDescription = $value;
                    break;
                case "COMPUTERID":
                    $obj->ComputerId = $value;
                    break;
                case "COMPUTERNAME":
                    $obj->ComputerName = $value;
                    break;
                case "COMPUTERDESCRIPTION":
                    $obj->ComputerDescription = $value;
                    break;
                default:
                    break;
            }
        }
        
        return $obj;
    }
    
    protected function FillTalentObject($talent)
    {
        $obj = new Talent();
        
        foreach ($talent as $key => $value)
        {
            switch (strtoupper($key))
            {
                case "TALENTID":
                    $obj->TalentId = $value;
                    break;
                case "NAME":
                    $obj->Name = $value;
                    break;
                case "ICON":
                    $obj->Icon = $value;
                    break;
                case "SPELLID":
                    $obj->SpellId = $value;
                    break;
                case "ROW":
                    $obj->Row = $value;
                    break;
                case "COLUMN":
                    $obj->Column = $value;
                    break;
                case "CLASSID":
                    $obj->ClassId = $value;
                    break;
                case "CLASSNAME":
                    $obj->ClassName = $value;
                    break;
                case "SPECIALIZATIONID":
                    $obj->SpecializationId = $value;
                    break;
                case "SPECIALIZATIONNAME":
                    $obj->SpecializationName = $value;
                    break;
                default:
                    break;
            }
        }
        
        return $obj;
    }
}
