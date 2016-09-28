<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

/**
 * Simulation Class
 * Model handles information about a simulation that has been run
 */
class Simulation {
    var $UserId;
    var $Email;
    var $SimulationId;
    var $SimulationName;
    var $SimulationGUID;
    var $BossCount;
    var $Iterations;
    var $SimulationLength;
    var $SimulationLengthVariance;
    var $TimeQueued;
    var $TimeCompleted;
    var $SimulationTypeId;
    var $SimulationTypeSystemName;
    var $SimulationTypeFriendlyName;
    var $SimulationTypeDescription;
    var $SimulationLogId;
    var $TMIWindow;
    var $TMIBoss;
    var $ScaleFactors;
    var $ReportArchived;
    var $TimeArchived;
    var $SimulationRawLog;
    var $QueuePosition;
    var $TotalQueue;
    var $SimulationLogTime;
    var $SimulationStatusId;
    var $StatusName;
    var $StatusDescription;
    var $ActorCount;
    var $IsHidden;
    var $SimulationCraftVersion;
    var $GameVersion;
    var $SimulationArchived;
    var $SimulationTimeArchived;
    
    var $SimulationActors;
    var $SimulationLogs;
    var $CustomProfile;
    
    public function ActorCount()
    {
        if ($this->SimulationActors != null)
        {
            return count($this->SimulationActors);
        }
        
        return 0;
    }
    
    public function RankInSimulation($actorId)
    {        
        for($x = 0; $x < count($this->SimulationActors); $x++)
        {
            if ($actorId == $this->SimulationActors[$x]->SimulationActorId)
            {
                return $x+1;
            }
        }
    }
}
?>
