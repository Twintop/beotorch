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
class Batch {
    var $BatchId;
    var $BatchGUID;
    var $BatchName;
    var $UserId;
    var $IsArchive;
    
    var $Simulations;
    
    public function ActorCount()
    {
        if ($this->Simulations != null)
        {
            $count = 0;
            foreach ($this->Simulations as $sim)
            {
                $count += $sim->ActorCount();
            }
            return $count;
        }
        return 0;
    }
}

?>

