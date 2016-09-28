<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

/**
 * SimulationActor Class
 * Model handles information about an actor in a simulation that has been run
 */
class SimulationActor {
    var $SimulationActorId;
    var $SimulationId;
    var $SimulationGUID;
    var $CharacterId;
    var $CharacterName;
    var $Level;
    var $ItemLevel;
    var $CalcTalent;
    var $SimTalent;
    var $CalcSpec;
    var $CalcClass;
    var $CalcGlyph;
    var $Gender;
    var $GenderName;
    var $ThumbnailURL;
    var $ServerId;
    var $ServerName;
    var $ServerType;
    var $RaceId;
    var $RaceName;
    var $Faction;
    var $FactionName;
    var $FactionColor;
    var $ClassId;
    var $ClassName;
    var $ClassColor;
    var $ArmorySpec;
    var $SpecializationId;
    var $SpecializationName;
    var $SpecializationPosition;
    var $SpecializationRole;
    var $SpecializationPrimaryStat;
    var $SimulationRole;
    var $RegionId;
    var $RegionName;
    var $RegionURL;
    var $RegionPrefix;
    var $RegionAPIUrl;
    var $RegionThumbnailURL;
    var $DPS;
    var $TMI;
    var $DTPS;
    var $HPS;
    var $APS;
    var $CharacterJSON;
    var $IsCustomActor;
    var $CustomActorName;
    
    var $Scaling;
    var $Talents;
}
