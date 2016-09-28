
DELIMITER $$
CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `ArchiveReport` (IN `ComputerId` MEDIUMINT UNSIGNED, IN `ComputerAPIKey` VARCHAR(38), IN `SimulationId` INT UNSIGNED, IN `IPAddress` VARCHAR(45))  NO SQL
BEGIN
	
    SET @ExitOnError = 0;
    
	IF SimulationId = '' THEN
		CALL raise(1356, 'Parameter `SimulationID` is required by SPROC WorkItemClaim');
		SET @ExitOnError = 1;
	END IF; 
    
    IF @ExitOnError = 0 THEN
	
    	IF (ComputerId = '' OR ComputerId Is Null) AND (ComputerAPIKey = '' OR ComputerAPIKey Is Null) THEN
    
			SET ComputerAPIKey = null;
	        SET ComputerId = null;
        
    	ELSEIF (ComputerId = '' OR ComputerId Is Null) THEN
    
			SET ComputerId = (
            	SELECT	c.ComputerId
	        	FROM	Computers c
    	    	WHERE	c.ComputerAPIKey = ComputerAPIKey
        	);
        	
	        IF ComputerId Is Null OR ComputerId < 1 THEN
    	    
        		SET ComputerId = null;
        	
	        END IF;
    
		END IF;
	
    	SET @ResultCode = 0;

		CALL ConnectionLogInsert(@ComputerId, ComputerAPIKey, null, IPAddress);

		IF ComputerId > 0 THEN

			UPDATE	Simulations as sim
            SET		sim.ReportArchived = 1
            		,sim.TimeArchived = NOW()
            WHERE	sim.SimulationId = SimulationId;
            
            SELECT	sim.SimulationId
            		,sim.ReportArchived
                    ,sim.TimeArchived
            FROM 	Simulations as sim
            WHERE	sim.SimulationId = SimulationId;            
            
		END IF;

    END IF;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `BatchGet` (IN `BatchId` INT UNSIGNED)  NO SQL
BEGIN

SELECT	b.*
FROM	Batches as b
WHERE	b.BatchId = BatchId;

SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED ;
SELECT	bs.*
        ,vsd.*
FROM	Batches as b
JOIN	BatchSimulations as bs on bs.BatchId = b.BatchId
JOIN	vSimulationsDetails as vsd on vsd.SimulationId = bs.SimulationId
WHERE	b.BatchId = BatchId;
COMMIT ;

SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED ;
SELECT	b.*
		,bs.*
        ,vsads.*
FROM	Batches as b
JOIN	BatchSimulations as bs on bs.BatchId = b.BatchId
JOIN	vSimulationActorDetailsShort as vsads on vsads.SimulationId = bs.SimulationId
WHERE	b.BatchId = BatchId;
COMMIT ;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `BatchGetByGUID` (IN `BatchGUID` VARCHAR(38))  NO SQL
BEGIN

	SET @BatchId = (
        SELECT	b.BatchId
        FROM	Batches as b
        WHERE	b.BatchGUID = BatchGUID
        );
    
    IF @BatchId > 0 THEN
    
    	CALL BatchGet(@BatchId);
    
    END IF;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `BatchList` (IN `UserId` INT UNSIGNED)  NO SQL
BEGIN

SELECT	*
FROM	Batches as b
WHERE	b.IsArchive = 0;
#WHERE	b.UserId = UserId
#AND		b.IsArchive = 0;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `CharacterGetId` (IN `CharacterName` VARCHAR(20) CHARSET utf8, IN `ServerId` SMALLINT UNSIGNED, OUT `CharacterId` INT UNSIGNED)  NO SQL
BEGIN

	SET @ExitOnError = 0;
    
	IF ServerId = '' THEN
		CALL raise(1356, 'Parameter `ServerId` is required by SPROC SimulationInsert');
		SET @ExitOnError = 1;
	END IF;

	IF CharacterName = '' OR CharacterName Is Null THEN
		CALL raise(1356, 'Parameter `CharacterName` is required by SPROC SimulationInsert');
		SET @ExitOnError = 1;
	END IF;
       
    IF @ExitOnError = 0 THEN
        
    	SET	CharacterId = (
            SELECT	c.CharacterId
            FROM	Characters as c
            WHERE	c.CharacterName LIKE CharacterName
            AND		c.ServerId = ServerId
            LIMIT 1
            );
        
        IF CharacterId = 0 OR CharacterId Is Null THEN
        
        	INSERT INTO	Characters (CharacterName, ServerId)
            VALUES	(CharacterName, ServerId);
    
    		SET CharacterId = LAST_INSERT_ID();
            

		END IF;   

	END IF;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `CharacterList` (IN `UserId` INT)  NO SQL
BEGIN

	SELECT	vsad.CharacterId
    		,vsad.CharacterName
    		,vsad.ServerId
    		,vsad.ServerName
    		,vsad.ServerType
    		,vsad.RegionId
    		,vsad.RegionPrefix
    		,vsad.RegionName
    		,vsad.ClassName            
    FROM	vSimulationActorDetails as vsad
    WHERE	vsad.UserId = UserId
    GROUP BY	vsad.CharacterId
    ORDER BY	vsad.RegionId ASC, vsad.ServerName ASC, vsad.CharacterName ASC, vsad.SimulationId DESC;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `CharacterTempStorageGet` (IN `CharacterName` VARCHAR(20), IN `ServerId` MEDIUMINT UNSIGNED, IN `UserId` INT)  NO SQL
BEGIN

	SET @CharacterId = 0;
    
    CALL CharacterGetId(CharacterName, ServerId, @CharacterId);

	SELECT	cts.CharacterJSON
    FROM	CharacterTempStorage as cts
    WHERE	@CharacterId = cts.CharacterId
    AND		UserId = cts.UserId;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `CharacterTempStorageSave` (IN `CharacterName` VARCHAR(20), IN `ServerId` MEDIUMINT UNSIGNED, IN `UserId` INT UNSIGNED, IN `CharacterJSON` MEDIUMTEXT)  NO SQL
BEGIN

	SET @CharacterId = 0;
    CALL CharacterGetId(CharacterName, ServerId, @CharacterId);

	SET @CTSId = 0;
    SET @CTSId = (
        SELECT	cts.CharacterTempStorageId
        FROM	CharacterTempStorage as cts
        WHERE	UserId = cts.UserId
        AND		@CharacterId = cts.CharacterId
        );
        
    IF @CTSId = 0 OR @CTSId Is Null THEN
    
    	INSERT INTO CharacterTempStorage (CharacterId, UserId, CharacterJSON)
        VALUES (@CharacterId, UserId, CharacterJSON);        
    
    	SELECT LAST_INSERT_ID() as CharacterTempStorageId;
    
    ELSE
    
    	UPDATE	CharacterTempStorage as cts
        SET		cts.CharacterJSON = CharacterJSON
        WHERE	UserId = cts.UserId
        AND		@CharacterId = cts.CharacterId;
        
    	SELECT @CTSId as CharacterTempStorageId;
        
    END IF;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `ConnectionLogInsert` (IN `ComputerId` SMALLINT UNSIGNED, IN `ComputerAPIKey` VARCHAR(38) CHARSET utf8, IN `SimulationLogId` INT UNSIGNED, IN `IPAddress` VARCHAR(45) CHARSET utf8)  NO SQL
BEGIN
    
	IF (ComputerId = '' OR ComputerId Is Null) AND (ComputerAPIKey = '' OR ComputerAPIKey Is Null) THEN
    
		SET ComputerAPIKey = null;
        SET ComputerId = null;
        
    ELSEIF (ComputerId = '' OR ComputerId Is Null) THEN
    
    	SET ComputerId = (
            SELECT c.ComputerId
        	FROM	Computers c
        	WHERE	c.ComputerAPIKey = ComputerAPIKey
            );
        
        IF ComputerId Is Null OR ComputerId < 1 THEN
        
        	SET ComputerId = null;
        
        END IF;
    
	END IF;
        
	IF SimulationLogId = '' THEN
		SET SimulationLogId = null;
	END IF;
    
	IF IPAddress = '' THEN
		SET IPAddress = null;
	END IF;

	INSERT INTO ConnectionLog (ComputerId, SimulationLogId, IPAddress, SuppliedComputerAPIKey)
	VALUES (ComputerId, SimulationLogId, IPAddress, ComputerAPIKey);
END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `CustomSimulationQueue` (IN `BossCount` TINYINT UNSIGNED, IN `FightType` TINYINT UNSIGNED, IN `ExtraText` VARCHAR(255), IN `RaidEvents` TEXT)  NO SQL
BEGIN

SET @BaseProfile = 'priest="BASELINE"
level=110
race=draenei
role=spell
position=back
talents=0000000
artifact=47:139251:139257:139251:0:764:1:765:1:767:3:768:1:769:1:770:1:771:3:772:5:773:3:775:3:776:4:777:3:1347:1
spec=shadow

# This default action priority list is automatically created based on your character.
# It is a attempt to provide you with a action list that is both simple and practicable,
# while resulting in a meaningful and good simulation. It may not result in the absolutely highest possible dps.
# Feel free to edit, adapt and improve it to your own needs.
# SimulationCraft is always looking for updates and improvements to the default action lists.

# Executed before combat begins. Accepts non-harmful actions only.

actions.precombat=flask,type=flask_of_the_whispered_pact
actions.precombat+=/food,type=azshari_salad
actions.precombat+=/augmentation,type=defiled
# Snapshot raid buffed stats before combat begins and pre-potting is done.
actions.precombat+=/snapshot_stats
actions.precombat+=/potion,name=deadly_grace
actions.precombat+=/mind_blast

# Executed every time the actor is available.

actions=potion,name=deadly_grace,if=buff.bloodlust.react|target.time_to_die<=40|buff.voidform.stack>80
actions+=/variable,name=s2mcheck,value=0.85*(45+((raw_haste_pct*100)*(2+(1*talent.reaper_of_souls.enabled)+(2*artifact.mass_hysteria.rank)-(1*talent.sanlayn.enabled))))-(5*nonexecute_actors_pct)
actions+=/call_action_list,name=s2m,if=buff.voidform.up&buff.surrender_to_madness.up
actions+=/call_action_list,name=vf,if=buff.voidform.up
actions+=/call_action_list,name=main

actions.main=surrender_to_madness,if=talent.surrender_to_madness.enabled&target.time_to_die<=variable.s2mcheck
actions.main+=/mindbender,if=talent.mindbender.enabled&!talent.surrender_to_madness.enabled
actions.main+=/mindbender,if=talent.mindbender.enabled&talent.surrender_to_madness.enabled&target.time_to_die>variable.s2mcheck+60
actions.main+=/shadow_word_pain,if=dot.shadow_word_pain.remains<(3+(4%3))*gcd
actions.main+=/vampiric_touch,if=dot.vampiric_touch.remains<(4+(4%3))*gcd
actions.main+=/void_eruption,if=insanity>=85|(talent.auspicious_spirits.enabled&insanity>=(80-shadowy_apparitions_in_flight*4))
actions.main+=/shadow_crash,if=talent.shadow_crash.enabled
actions.main+=/mindbender,if=talent.mindbender.enabled&set_bonus.tier18_2pc
actions.main+=/shadow_word_pain,if=!ticking&talent.legacy_of_the_void.enabled&insanity>=70,cycle_targets=1
actions.main+=/vampiric_touch,if=!ticking&talent.legacy_of_the_void.enabled&insanity>=70,cycle_targets=1
actions.main+=/shadow_word_death,if=!talent.reaper_of_souls.enabled&cooldown.shadow_word_death.charges=2&insanity<=90
actions.main+=/shadow_word_death,if=talent.reaper_of_souls.enabled&cooldown.shadow_word_death.charges=2&insanity<=70
actions.main+=/mind_blast,if=talent.legacy_of_the_void.enabled&(insanity<=81|(insanity<=75.2&talent.fortress_of_the_mind.enabled))
actions.main+=/mind_blast,if=!talent.legacy_of_the_void.enabled|(insanity<=96|(insanity<=95.2&talent.fortress_of_the_mind.enabled))
actions.main+=/shadow_word_pain,if=!ticking&target.time_to_die>10&(active_enemies<5&(talent.auspicious_spirits.enabled|talent.shadowy_insight.enabled)),cycle_targets=1
actions.main+=/vampiric_touch,if=!ticking&target.time_to_die>10&(active_enemies<4|talent.sanlayn.enabled|(talent.auspicious_spirits.enabled&artifact.unleash_the_shadows.rank)),cycle_targets=1
actions.main+=/shadow_word_pain,if=!ticking&target.time_to_die>10&(active_enemies<5&artifact.sphere_of_insanity.rank),cycle_targets=1
actions.main+=/shadow_word_void,if=(insanity<=70&talent.legacy_of_the_void.enabled)|(insanity<=85&!talent.legacy_of_the_void.enabled)
actions.main+=/mind_sear,if=active_enemies>=3,interrupt=1,chain=1
actions.main+=/mind_flay,if=!talent.mind_spike.enabled,interrupt=1,chain=1
actions.main+=/mind_spike,if=talent.mind_spike.enabled
actions.main+=/shadow_word_pain

actions.vf=surrender_to_madness,if=talent.surrender_to_madness.enabled&insanity>=25&(cooldown.void_bolt.up|cooldown.void_torrent.up|cooldown.shadow_word_death.up|buff.shadowy_insight.up)&target.time_to_die<=variable.s2mcheck-(buff.insanity_drain_stacks.stack)
actions.vf+=/shadow_crash,if=talent.shadow_crash.enabled
actions.vf+=/mindbender,if=talent.mindbender.enabled&!talent.surrender_to_madness.enabled
actions.vf+=/mindbender,if=talent.mindbender.enabled&talent.surrender_to_madness.enabled&target.time_to_die>variable.s2mcheck-(buff.insanity_drain_stacks.stack)+30
actions.vf+=/dispersion,if=!buff.power_infusion.up&!buff.berserking.up&!buff.bloodlust.up&artifact.void_torrent.rank&!talent.surrender_to_madness.enabled
actions.vf+=/dispersion,if=!buff.power_infusion.up&!buff.berserking.up&!buff.bloodlust.up&artifact.void_torrent.rank&talent.surrender_to_madness.enabled&target.time_to_die>variable.s2mcheck-(buff.insanity_drain_stacks.stack)+(120-10*artifact.from_the_shadows.rank)
actions.vf+=/power_infusion,if=buff.voidform.stack>=10&buff.insanity_drain_stacks.stack<=30&!talent.surrender_to_madness.enabled
actions.vf+=/power_infusion,if=buff.voidform.stack>=10&talent.surrender_to_madness.enabled&target.time_to_die>variable.s2mcheck-(buff.insanity_drain_stacks.stack)+15
actions.vf+=/berserking,if=buff.voidform.stack>=10&buff.insanity_drain_stacks.stack<=20&!talent.surrender_to_madness.enabled
actions.vf+=/berserking,if=buff.voidform.stack>=10&talent.surrender_to_madness.enabled&target.time_to_die>variable.s2mcheck-(buff.insanity_drain_stacks.stack)+70
actions.vf+=/void_bolt,if=dot.shadow_word_pain.remains<3.5*gcd&dot.vampiric_touch.remains<3.5*gcd&target.time_to_die>10,cycle_targets=1
actions.vf+=/void_bolt,if=dot.shadow_word_pain.remains<3.5*gcd&(talent.auspicious_spirits.enabled|talent.shadowy_insight.enabled)&target.time_to_die>10,cycle_targets=1
actions.vf+=/void_bolt,if=dot.vampiric_touch.remains<3.5*gcd&(talent.sanlayn.enabled|(talent.auspicious_spirits.enabled&artifact.unleash_the_shadows.rank))&target.time_to_die>10,cycle_targets=1
actions.vf+=/void_bolt,if=dot.shadow_word_pain.remains<3.5*gcd&artifact.sphere_of_insanity.rank&target.time_to_die>10,cycle_targets=1
actions.vf+=/void_bolt
actions.vf+=/void_torrent,if=!talent.surrender_to_madness.enabled
actions.vf+=/void_torrent,if=talent.surrender_to_madness.enabled&target.time_to_die>variable.s2mcheck-(buff.insanity_drain_stacks.stack)+60
actions.vf+=/shadow_word_death,if=!talent.reaper_of_souls.enabled&current_insanity_drain*gcd.max>insanity&(insanity-(current_insanity_drain*gcd.max)+10)<100
actions.vf+=/shadow_word_death,if=talent.reaper_of_souls.enabled&current_insanity_drain*gcd.max>insanity&(insanity-(current_insanity_drain*gcd.max)+30)<100
actions.vf+=/wait,sec=action.void_bolt.usable_in,if=action.void_bolt.usable_in<gcd.max*0.25
actions.vf+=/mind_blast
actions.vf+=/wait,sec=action.mind_blast.usable_in,if=action.mind_blast.usable_in<gcd.max*0.25
actions.vf+=/shadow_word_death,if=cooldown.shadow_word_death.charges=2
actions.vf+=/shadowfiend,if=!talent.mindbender.enabled,if=buff.voidform.stack>15
actions.vf+=/shadow_word_void,if=(insanity-(current_insanity_drain*gcd.max)+25)<100
actions.vf+=/shadow_word_pain,if=!ticking&(active_enemies<5|talent.auspicious_spirits.enabled|talent.shadowy_insight.enabled|artifact.sphere_of_insanity.rank)
actions.vf+=/vampiric_touch,if=!ticking&(active_enemies<4|talent.sanlayn.enabled|(talent.auspicious_spirits.enabled&artifact.unleash_the_shadows.rank))
actions.vf+=/shadow_word_pain,if=!ticking&target.time_to_die>10&(active_enemies<5&(talent.auspicious_spirits.enabled|talent.shadowy_insight.enabled)),cycle_targets=1
actions.vf+=/vampiric_touch,if=!ticking&target.time_to_die>10&(active_enemies<4|talent.sanlayn.enabled|(talent.auspicious_spirits.enabled&artifact.unleash_the_shadows.rank)),cycle_targets=1
actions.vf+=/shadow_word_pain,if=!ticking&target.time_to_die>10&(active_enemies<5&artifact.sphere_of_insanity.rank),cycle_targets=1
actions.vf+=/wait,sec=action.void_bolt.usable_in,if=action.void_bolt.usable|action.void_bolt.usable_in<gcd.max*0.8
actions.vf+=/mind_sear,if=active_enemies>=3,interrupt=1
actions.vf+=/mind_flay,if=!talent.mind_spike.enabled,chain=1,interrupt_immediate=1,interrupt_if=action.void_bolt.usable_in<gcd.max*0.8
actions.vf+=/mind_spike,if=talent.mind_spike.enabled
actions.vf+=/shadow_word_pain

actions.s2m=shadow_crash,if=talent.shadow_crash.enabled
actions.s2m+=/mindbender,if=talent.mindbender.enabled
actions.s2m+=/dispersion,if=!buff.power_infusion.up&!buff.berserking.up&!buff.bloodlust.up
actions.s2m+=/power_infusion,if=buff.insanity_drain_stacks.stack>=85
actions.s2m+=/berserking,if=buff.voidform.stack>=90
actions.s2m+=/void_bolt,if=dot.shadow_word_pain.remains<3.5*gcd&dot.vampiric_touch.remains<3.5*gcd&target.time_to_die>10,cycle_targets=1
actions.s2m+=/void_bolt,if=dot.shadow_word_pain.remains<3.5*gcd&(talent.auspicious_spirits.enabled|talent.shadowy_insight.enabled)&target.time_to_die>10,cycle_targets=1
actions.s2m+=/void_bolt,if=dot.vampiric_touch.remains<3.5*gcd&(talent.sanlayn.enabled|(talent.auspicious_spirits.enabled&artifact.unleash_the_shadows.rank))&target.time_to_die>10,cycle_targets=1
actions.s2m+=/void_bolt,if=dot.shadow_word_pain.remains<3.5*gcd&artifact.sphere_of_insanity.rank&target.time_to_die>10,cycle_targets=1
actions.s2m+=/void_bolt
actions.s2m+=/void_torrent
actions.s2m+=/shadow_word_death,if=!talent.reaper_of_souls.enabled&current_insanity_drain*gcd.max>insanity&(insanity-(current_insanity_drain*gcd.max)+30)<100
actions.s2m+=/shadow_word_death,if=talent.reaper_of_souls.enabled&current_insanity_drain*gcd.max>insanity&(insanity-(current_insanity_drain*gcd.max)+90)<100
actions.s2m+=/wait,sec=action.void_bolt.usable_in,if=action.void_bolt.usable_in<gcd.max*0.25
actions.s2m+=/mind_blast
actions.s2m+=/wait,sec=action.mind_blast.usable_in,if=action.mind_blast.usable_in<gcd.max*0.25
actions.s2m+=/shadow_word_death,if=cooldown.shadow_word_death.charges=2
actions.s2m+=/shadowfiend,if=!talent.mindbender.enabled,if=buff.voidform.stack>15
actions.s2m+=/shadow_word_void,if=(insanity-(current_insanity_drain*gcd.max)+75)<100
actions.s2m+=/shadow_word_pain,if=!ticking&(active_enemies<5|talent.auspicious_spirits.enabled|talent.shadowy_insight.enabled|artifact.sphere_of_insanity.rank)
actions.s2m+=/vampiric_touch,if=!ticking&(active_enemies<4|talent.sanlayn.enabled|(talent.auspicious_spirits.enabled&artifact.unleash_the_shadows.rank))
actions.s2m+=/shadow_word_pain,if=!ticking&target.time_to_die>10&(active_enemies<5&(talent.auspicious_spirits.enabled|talent.shadowy_insight.enabled)),cycle_targets=1
actions.s2m+=/vampiric_touch,if=!ticking&target.time_to_die>10&(active_enemies<4|talent.sanlayn.enabled|(talent.auspicious_spirits.enabled&artifact.unleash_the_shadows.rank)),cycle_targets=1
actions.s2m+=/shadow_word_pain,if=!ticking&target.time_to_die>10&(active_enemies<5&artifact.sphere_of_insanity.rank),cycle_targets=1
actions.s2m+=/wait,sec=action.void_bolt.usable_in,if=action.void_bolt.usable|action.void_bolt.usable_in<gcd.max*0.8
actions.s2m+=/mind_sear,if=active_enemies>=3,interrupt=1
actions.s2m+=/mind_flay,if=!talent.mind_spike.enabled,chain=1,interrupt_immediate=1,interrupt_if=action.void_bolt.usable_in<gcd.max*0.8
actions.s2m+=/mind_spike,if=talent.mind_spike.enabled

head=hood_of_darkened_visions,id=139189,bonus_id=1805
neck=blackened_portalstone_necklace,id=139332,bonus_id=1805,enchant=mark_of_the_claw
shoulders=mantle_of_perpetual_bloom,id=139192,bonus_id=1805
back=evergreen_vinewrap_drape,id=139248,bonus_id=1805,enchant=200int
chest=dreamscale_inlaid_vestments,id=138215,bonus_id=1805
wrists=cinch_of_cosmic_insignficance,id=139187,bonus_id=1805
hands=handwraps_of_delusional_power,id=138212,bonus_id=1805
waist=pliable_spider_silk_cinch,id=138217,bonus_id=1805
legs=nazaks_dusty_pantaloons,id=141415
feet=cozy_dryad_hoofsocks,id=139194,bonus_id=1805
finger1=twicewarped_azsharan_signet,id=139238,bonus_id=1805,enchant=200haste
finger2=dreadful_cyclopean_signet,id=139237,bonus_id=1805,enchant=200haste
trinket1=swarming_plaguehive,id=139321,bonus_id=1805
trinket2=twisting_wind,id=139323,bonus_id=1805
main_hand=xalatath_blade_of_the_black_empire,id=128827,bonus_id=740,gem_id=139251/139257/139251,relic_id=1805/1805/1805
off_hand=secrets_of_the_void,id=133958

# Gear Summary
# gear_ilvl=868.31
# gear_stamina=22518
# gear_intellect=24778
# gear_crit_rating=6491
# gear_haste_rating=8895
# gear_mastery_rating=3151
# gear_armor=1711';

SET @BossName = (select SimulationTypeSystemName from SimulationTypes where SimulationTypeId = FightType);

SET @BatchGUID = (SELECT UUID());
INSERT INTO `Batches` (`BatchGUID`, `BatchName`, `UserId`) VALUES
(@BatchGUID, (CONCAT('20160821 - Heroic Emerald Nightmare -- ', BossCount, ' Target ', @BossName, ExtraText)), 15);
SET @BatchId = LAST_INSERT_ID();

SET @SimGUID = (SELECT UUID());
INSERT INTO `Simulations` (`SimulationName`, `SimulationGUID`, `UserId`, `SimulationTypeId`, `BossCount`, `Iterations`, `SimulationLength`, `SimulationLengthVariance`, `ScaleFactors`, `TMIWindow`, `TMIBoss`, `IsHidden`, `CustomProfile`, `SimulationCraftVersion`, `GameVersion`) VALUES
(CONCAT(BossCount, ' Target ', @BossName, ExtraText, ' (1/9)'), @SimGUID, 15, FightType, BossCount, 10000, 450, 0.2, 0, NULL, NULL, 1, (CONCAT(@BaseProfile, '

copy="LotV_PI_SL_VL_ToF"
talents=1001111

copy="MSp_PI_SL_VL_ToF"
talents=1001112

copy="S2M_PI_SL_VL_ToF"
talents=1001113

copy="LotV_PI_SL_VL_FotM"
talents=2001111

copy="MSp_PI_SL_VL_FotM"
talents=2001112

copy="S2M_PI_SL_VL_FotM"
talents=2001113

copy="LotV_PI_SL_VL_SWV"
talents=3001111

copy="MSp_PI_SL_VL_SWV"
talents=3001112

copy="S2M_PI_SL_VL_SWV"
talents=3001113

copy="LotV_SC_SL_VL_ToF"
talents=1001121

copy="MSp_SC_SL_VL_ToF"
talents=1001122

copy="S2M_SC_SL_VL_ToF"
talents=1001123

copy="LotV_SC_SL_VL_FotM"
talents=2001121

copy="MSp_SC_SL_VL_FotM"
talents=2001122

copy="S2M_SC_SL_VL_FotM"
talents=2001123

copy="LotV_SC_SL_VL_SWV"
talents=3001121

copy="MSp_SC_SL_VL_SWV"
talents=3001122

copy="S2M_SC_SL_VL_SWV"
talents=3001123

copy="LotV_Mb_SL_VL_ToF"
talents=1001131

copy="MSp_Mb_SL_VL_ToF"
talents=1001132

copy="S2M_Mb_SL_VL_ToF"
talents=1001133

copy="LotV_Mb_SL_VL_FotM"
talents=2001131

copy="MSp_Mb_SL_VL_FotM"
talents=2001132

copy="S2M_Mb_SL_VL_FotM"
talents=2001133

copy="LotV_Mb_SL_VL_SWV"
talents=3001131

copy="MSp_Mb_SL_VL_SWV"
talents=3001132

copy="S2M_Mb_SL_VL_SWV"
talents=3001133', RaidEvents)), 'live', '7.0.3');
SET @SimulationId = LAST_INSERT_ID();
SET @SimulationLogId = 0;
CALL SimulationLogInsert(@SimulationId, 1, null, @SimulationLogId); 
INSERT INTO `BatchSimulations` (`BatchId`, `SimulationId`) VALUES
(@BatchId, @SimulationId);

SET @SimGUID = (SELECT UUID());
INSERT INTO `Simulations` (`SimulationName`, `SimulationGUID`, `UserId`, `SimulationTypeId`, `BossCount`, `Iterations`, `SimulationLength`, `SimulationLengthVariance`, `ScaleFactors`, `TMIWindow`, `TMIBoss`, `IsHidden`, `CustomProfile`, `SimulationCraftVersion`, `GameVersion`) VALUES
(CONCAT(BossCount, ' Target ', @BossName, ExtraText, ' (2/9)'), @SimGUID, 15, FightType, BossCount, 10000, 450, 0.2, 0, NULL, NULL, 1, (CONCAT(@BaseProfile, '

copy="LotV_PI_AS_VL_ToF"
talents=1001211

copy="MSp_PI_AS_VL_ToF"
talents=1001212

copy="S2M_PI_AS_VL_ToF"
talents=1001213

copy="LotV_PI_AS_VL_FotM"
talents=2001211

copy="MSp_PI_AS_VL_FotM"
talents=2001212

copy="S2M_PI_AS_VL_FotM"
talents=2001213

copy="LotV_PI_AS_VL_SWV"
talents=3001211

copy="MSp_PI_AS_VL_SWV"
talents=3001212

copy="S2M_PI_AS_VL_SWV"
talents=3001213

copy="LotV_SC_AS_VL_ToF"
talents=1001221

copy="MSp_SC_AS_VL_ToF"
talents=1001222

copy="S2M_SC_AS_VL_ToF"
talents=1001223

copy="LotV_SC_AS_VL_FotM"
talents=2001221

copy="MSp_SC_AS_VL_FotM"
talents=2001222

copy="S2M_SC_AS_VL_FotM"
talents=2001223

copy="LotV_SC_AS_VL_SWV"
talents=3001221

copy="MSp_SC_AS_VL_SWV"
talents=3001222

copy="S2M_SC_AS_VL_SWV"
talents=3001223

copy="LotV_Mb_AS_VL_ToF"
talents=1001231

copy="MSp_Mb_AS_VL_ToF"
talents=1001232

copy="S2M_Mb_AS_VL_ToF"
talents=1001233

copy="LotV_Mb_AS_VL_FotM"
talents=2001231

copy="MSp_Mb_AS_VL_FotM"
talents=2001232

copy="S2M_Mb_AS_VL_FotM"
talents=2001233

copy="LotV_Mb_AS_VL_SWV"
talents=3001231

copy="MSp_Mb_AS_VL_SWV"
talents=3001232

copy="S2M_Mb_AS_VL_SWV"
talents=3001233', RaidEvents)), 'live', '7.0.3');
SET @SimulationId = LAST_INSERT_ID();
SET @SimulationLogId = 0;
CALL SimulationLogInsert(@SimulationId, 1, null, @SimulationLogId); 
INSERT INTO `BatchSimulations` (`BatchId`, `SimulationId`) VALUES
(@BatchId, @SimulationId);


SET @SimGUID = (SELECT UUID());
INSERT INTO `Simulations` (`SimulationName`, `SimulationGUID`, `UserId`, `SimulationTypeId`, `BossCount`, `Iterations`, `SimulationLength`, `SimulationLengthVariance`, `ScaleFactors`, `TMIWindow`, `TMIBoss`, `IsHidden`, `CustomProfile`, `SimulationCraftVersion`, `GameVersion`) VALUES
(CONCAT(BossCount, ' Target ', @BossName, ExtraText, ' (3/9)'), @SimGUID, 15, FightType, BossCount, 10000, 450, 0.2, 0, NULL, NULL, 1, (CONCAT(@BaseProfile, '

copy="LotV_PI_SI_VL_ToF"
talents=1001311

copy="MSp_PI_SI_VL_ToF"
talents=1001312

copy="S2M_PI_SI_VL_ToF"
talents=1001313

copy="LotV_PI_SI_VL_FotM"
talents=2001311

copy="MSp_PI_SI_VL_FotM"
talents=2001312

copy="S2M_PI_SI_VL_FotM"
talents=2001313

copy="LotV_PI_SI_VL_SWV"
talents=3001311

copy="MSp_PI_SI_VL_SWV"
talents=3001312

copy="S2M_PI_SI_VL_SWV"
talents=3001313

copy="LotV_SC_SI_VL_ToF"
talents=1001321

copy="MSp_SC_SI_VL_ToF"
talents=1001322

copy="S2M_SC_SI_VL_ToF"
talents=1001323

copy="LotV_SC_SI_VL_FotM"
talents=2001321

copy="MSp_SC_SI_VL_FotM"
talents=2001322

copy="S2M_SC_SI_VL_FotM"
talents=2001323

copy="LotV_SC_SI_VL_SWV"
talents=3001321

copy="MSp_SC_SI_VL_SWV"
talents=3001322

copy="S2M_SC_SI_VL_SWV"
talents=3001323

copy="LotV_Mb_SI_VL_ToF"
talents=1001331

copy="MSp_Mb_SI_VL_ToF"
talents=1001332

copy="S2M_Mb_SI_VL_ToF"
talents=1001333

copy="LotV_Mb_SI_VL_FotM"
talents=2001331

copy="MSp_Mb_SI_VL_FotM"
talents=2001332

copy="S2M_Mb_SI_VL_FotM"
talents=2001333

copy="LotV_Mb_SI_VL_SWV"
talents=3001331

copy="MSp_Mb_SI_VL_SWV"
talents=3001332

copy="S2M_Mb_SI_VL_SWV"
talents=3001333', RaidEvents)), 'live', '7.0.3');
SET @SimulationId = LAST_INSERT_ID();
SET @SimulationLogId = 0;
CALL SimulationLogInsert(@SimulationId, 1, null, @SimulationLogId); 
INSERT INTO `BatchSimulations` (`BatchId`, `SimulationId`) VALUES
(@BatchId, @SimulationId);


SET @SimGUID = (SELECT UUID());
INSERT INTO `Simulations` (`SimulationName`, `SimulationGUID`, `UserId`, `SimulationTypeId`, `BossCount`, `Iterations`, `SimulationLength`, `SimulationLengthVariance`, `ScaleFactors`, `TMIWindow`, `TMIBoss`, `IsHidden`, `CustomProfile`, `SimulationCraftVersion`, `GameVersion`) VALUES
(CONCAT(BossCount, ' Target ', @BossName, ExtraText, ' (4/9)'), @SimGUID, 15, FightType, BossCount, 10000, 450, 0.2, 0, NULL, NULL, 1, (CONCAT(@BaseProfile, '

copy="LotV_PI_SL_RoS_ToF"
talents=1002111

copy="MSp_PI_SL_RoS_ToF"
talents=1002112

copy="S2M_PI_SL_RoS_ToF"
talents=1002113

copy="LotV_PI_SL_RoS_FotM"
talents=2002111

copy="MSp_PI_SL_RoS_FotM"
talents=2002112

copy="S2M_PI_SL_RoS_FotM"
talents=2002113

copy="LotV_PI_SL_RoS_SWV"
talents=3002111

copy="MSp_PI_SL_RoS_SWV"
talents=3002112

copy="S2M_PI_SL_RoS_SWV"
talents=3002113

copy="LotV_SC_SL_RoS_ToF"
talents=1002121

copy="MSp_SC_SL_RoS_ToF"
talents=1002122

copy="S2M_SC_SL_RoS_ToF"
talents=1002123

copy="LotV_SC_SL_RoS_FotM"
talents=2002121

copy="MSp_SC_SL_RoS_FotM"
talents=2002122

copy="S2M_SC_SL_RoS_FotM"
talents=2002123

copy="LotV_SC_SL_RoS_SWV"
talents=3002121

copy="MSp_SC_SL_RoS_SWV"
talents=3002122

copy="S2M_SC_SL_RoS_SWV"
talents=3002123

copy="LotV_Mb_SL_RoS_ToF"
talents=1002131

copy="MSp_Mb_SL_RoS_ToF"
talents=1002132

copy="S2M_Mb_SL_RoS_ToF"
talents=1002133

copy="LotV_Mb_SL_RoS_FotM"
talents=2002131

copy="MSp_Mb_SL_RoS_FotM"
talents=2002132

copy="S2M_Mb_SL_RoS_FotM"
talents=2002133

copy="LotV_Mb_SL_RoS_SWV"
talents=3002131

copy="MSp_Mb_SL_RoS_SWV"
talents=3002132

copy="S2M_Mb_SL_RoS_SWV"
talents=3002133', RaidEvents)), 'live', '7.0.3');
SET @SimulationId = LAST_INSERT_ID();
SET @SimulationLogId = 0;
CALL SimulationLogInsert(@SimulationId, 1, null, @SimulationLogId); 
INSERT INTO `BatchSimulations` (`BatchId`, `SimulationId`) VALUES
(@BatchId, @SimulationId);


SET @SimGUID = (SELECT UUID());
INSERT INTO `Simulations` (`SimulationName`, `SimulationGUID`, `UserId`, `SimulationTypeId`, `BossCount`, `Iterations`, `SimulationLength`, `SimulationLengthVariance`, `ScaleFactors`, `TMIWindow`, `TMIBoss`, `IsHidden`, `CustomProfile`, `SimulationCraftVersion`, `GameVersion`) VALUES
(CONCAT(BossCount, ' Target ', @BossName, ExtraText, ' (5/9)'), @SimGUID, 15, FightType, BossCount, 10000, 450, 0.2, 0, NULL, NULL, 1, (CONCAT(@BaseProfile, '

copy="LotV_PI_AS_RoS_ToF"
talents=1002211

copy="MSp_PI_AS_RoS_ToF"
talents=1002212

copy="S2M_PI_AS_RoS_ToF"
talents=1002213

copy="LotV_PI_AS_RoS_FotM"
talents=2002211

copy="MSp_PI_AS_RoS_FotM"
talents=2002212

copy="S2M_PI_AS_RoS_FotM"
talents=2002213

copy="LotV_PI_AS_RoS_SWV"
talents=3002211

copy="MSp_PI_AS_RoS_SWV"
talents=3002212

copy="S2M_PI_AS_RoS_SWV"
talents=3002213

copy="LotV_SC_AS_RoS_ToF"
talents=1002221

copy="MSp_SC_AS_RoS_ToF"
talents=1002222

copy="S2M_SC_AS_RoS_ToF"
talents=1002223

copy="LotV_SC_AS_RoS_FotM"
talents=2002221

copy="MSp_SC_AS_RoS_FotM"
talents=2002222

copy="S2M_SC_AS_RoS_FotM"
talents=2002223

copy="LotV_SC_AS_RoS_SWV"
talents=3002221

copy="MSp_SC_AS_RoS_SWV"
talents=3002222

copy="S2M_SC_AS_RoS_SWV"
talents=3002223

copy="LotV_Mb_AS_RoS_ToF"
talents=1002231

copy="MSp_Mb_AS_RoS_ToF"
talents=1002232

copy="S2M_Mb_AS_RoS_ToF"
talents=1002233

copy="LotV_Mb_AS_RoS_FotM"
talents=2002231

copy="MSp_Mb_AS_RoS_FotM"
talents=2002232

copy="S2M_Mb_AS_RoS_FotM"
talents=2002233

copy="LotV_Mb_AS_RoS_SWV"
talents=3002231

copy="MSp_Mb_AS_RoS_SWV"
talents=3002232

copy="S2M_Mb_AS_RoS_SWV"
talents=3002233', RaidEvents)), 'live', '7.0.3');
SET @SimulationId = LAST_INSERT_ID();
SET @SimulationLogId = 0;
CALL SimulationLogInsert(@SimulationId, 1, null, @SimulationLogId); 
INSERT INTO `BatchSimulations` (`BatchId`, `SimulationId`) VALUES
(@BatchId, @SimulationId);


SET @SimGUID = (SELECT UUID());
INSERT INTO `Simulations` (`SimulationName`, `SimulationGUID`, `UserId`, `SimulationTypeId`, `BossCount`, `Iterations`, `SimulationLength`, `SimulationLengthVariance`, `ScaleFactors`, `TMIWindow`, `TMIBoss`, `IsHidden`, `CustomProfile`, `SimulationCraftVersion`, `GameVersion`) VALUES
(CONCAT(BossCount, ' Target ', @BossName, ExtraText, ' (6/9)'), @SimGUID, 15, FightType, BossCount, 10000, 450, 0.2, 0, NULL, NULL, 1, (CONCAT(@BaseProfile, '

copy="LotV_PI_SI_RoS_ToF"
talents=1002311

copy="MSp_PI_SI_RoS_ToF"
talents=1002312

copy="S2M_PI_SI_RoS_ToF"
talents=1002313

copy="LotV_PI_SI_RoS_FotM"
talents=2002311

copy="MSp_PI_SI_RoS_FotM"
talents=2002312

copy="S2M_PI_SI_RoS_FotM"
talents=2002313

copy="LotV_PI_SI_RoS_SWV"
talents=3002311

copy="MSp_PI_SI_RoS_SWV"
talents=3002312

copy="S2M_PI_SI_RoS_SWV"
talents=3002313

copy="LotV_SC_SI_RoS_ToF"
talents=1002321

copy="MSp_SC_SI_RoS_ToF"
talents=1002322

copy="S2M_SC_SI_RoS_ToF"
talents=1002323

copy="LotV_SC_SI_RoS_FotM"
talents=2002321

copy="MSp_SC_SI_RoS_FotM"
talents=2002322

copy="S2M_SC_SI_RoS_FotM"
talents=2002323

copy="LotV_SC_SI_RoS_SWV"
talents=3002321

copy="MSp_SC_SI_RoS_SWV"
talents=3002322

copy="S2M_SC_SI_RoS_SWV"
talents=3002323

copy="LotV_Mb_SI_RoS_ToF"
talents=1002331

copy="MSp_Mb_SI_RoS_ToF"
talents=1002332

copy="S2M_Mb_SI_RoS_ToF"
talents=1002333

copy="LotV_Mb_SI_RoS_FotM"
talents=2002331

copy="MSp_Mb_SI_RoS_FotM"
talents=2002332

copy="S2M_Mb_SI_RoS_FotM"
talents=2002333

copy="LotV_Mb_SI_RoS_SWV"
talents=3002331

copy="MSp_Mb_SI_RoS_SWV"
talents=3002332

copy="S2M_Mb_SI_RoS_SWV"
talents=3002333', RaidEvents)), 'live', '7.0.3');
SET @SimulationId = LAST_INSERT_ID();
SET @SimulationLogId = 0;
CALL SimulationLogInsert(@SimulationId, 1, null, @SimulationLogId); 
INSERT INTO `BatchSimulations` (`BatchId`, `SimulationId`) VALUES
(@BatchId, @SimulationId);


SET @SimGUID = (SELECT UUID());
INSERT INTO `Simulations` (`SimulationName`, `SimulationGUID`, `UserId`, `SimulationTypeId`, `BossCount`, `Iterations`, `SimulationLength`, `SimulationLengthVariance`, `ScaleFactors`, `TMIWindow`, `TMIBoss`, `IsHidden`, `CustomProfile`, `SimulationCraftVersion`, `GameVersion`) VALUES
(CONCAT(BossCount, ' Target ', @BossName, ExtraText, ' (7/9)'), @SimGUID, 15, FightType, BossCount, 10000, 450, 0.2, 0, NULL, NULL, 1, (CONCAT(@BaseProfile, '

copy="LotV_PI_SL_VR_ToF"
talents=1003111

copy="MSp_PI_SL_VR_ToF"
talents=1003112

copy="S2M_PI_SL_VR_ToF"
talents=1003113

copy="LotV_PI_SL_VR_FotM"
talents=2003111

copy="MSp_PI_SL_VR_FotM"
talents=2003112

copy="S2M_PI_SL_VR_FotM"
talents=2003113

copy="LotV_PI_SL_VR_SWV"
talents=3003111

copy="MSp_PI_SL_VR_SWV"
talents=3003112

copy="S2M_PI_SL_VR_SWV"
talents=3003113

copy="LotV_SC_SL_VR_ToF"
talents=1003121

copy="MSp_SC_SL_VR_ToF"
talents=1003122

copy="S2M_SC_SL_VR_ToF"
talents=1003123

copy="LotV_SC_SL_VR_FotM"
talents=2003121

copy="MSp_SC_SL_VR_FotM"
talents=2003122

copy="S2M_SC_SL_VR_FotM"
talents=2003123

copy="LotV_SC_SL_VR_SWV"
talents=3003121

copy="MSp_SC_SL_VR_SWV"
talents=3003122

copy="S2M_SC_SL_VR_SWV"
talents=3003123

copy="LotV_Mb_SL_VR_ToF"
talents=1003131

copy="MSp_Mb_SL_VR_ToF"
talents=1003132

copy="S2M_Mb_SL_VR_ToF"
talents=1003133

copy="LotV_Mb_SL_VR_FotM"
talents=2003131

copy="MSp_Mb_SL_VR_FotM"
talents=2003132

copy="S2M_Mb_SL_VR_FotM"
talents=2003133

copy="LotV_Mb_SL_VR_SWV"
talents=3003131

copy="MSp_Mb_SL_VR_SWV"
talents=3003132

copy="S2M_Mb_SL_VR_SWV"
talents=3003133', RaidEvents)), 'live', '7.0.3');
SET @SimulationId = LAST_INSERT_ID();
SET @SimulationLogId = 0;
CALL SimulationLogInsert(@SimulationId, 1, null, @SimulationLogId); 
INSERT INTO `BatchSimulations` (`BatchId`, `SimulationId`) VALUES
(@BatchId, @SimulationId);


SET @SimGUID = (SELECT UUID());
INSERT INTO `Simulations` (`SimulationName`, `SimulationGUID`, `UserId`, `SimulationTypeId`, `BossCount`, `Iterations`, `SimulationLength`, `SimulationLengthVariance`, `ScaleFactors`, `TMIWindow`, `TMIBoss`, `IsHidden`, `CustomProfile`, `SimulationCraftVersion`, `GameVersion`) VALUES
(CONCAT(BossCount, ' Target ', @BossName, ExtraText, ' (8/9)'), @SimGUID, 15, FightType, BossCount, 10000, 450, 0.2, 0, NULL, NULL, 1, (CONCAT(@BaseProfile, '

copy="LotV_PI_AS_VR_ToF"
talents=1003211

copy="MSp_PI_AS_VR_ToF"
talents=1003212

copy="S2M_PI_AS_VR_ToF"
talents=1003213

copy="LotV_PI_AS_VR_FotM"
talents=2003211

copy="MSp_PI_AS_VR_FotM"
talents=2003212

copy="S2M_PI_AS_VR_FotM"
talents=2003213

copy="LotV_PI_AS_VR_SWV"
talents=3003211

copy="MSp_PI_AS_VR_SWV"
talents=3003212

copy="S2M_PI_AS_VR_SWV"
talents=3003213

copy="LotV_SC_AS_VR_ToF"
talents=1003221

copy="MSp_SC_AS_VR_ToF"
talents=1003222

copy="S2M_SC_AS_VR_ToF"
talents=1003223

copy="LotV_SC_AS_VR_FotM"
talents=2003221

copy="MSp_SC_AS_VR_FotM"
talents=2003222

copy="S2M_SC_AS_VR_FotM"
talents=2003223

copy="LotV_SC_AS_VR_SWV"
talents=3003221

copy="MSp_SC_AS_VR_SWV"
talents=3003222

copy="S2M_SC_AS_VR_SWV"
talents=3003223

copy="LotV_Mb_AS_VR_ToF"
talents=1003231

copy="MSp_Mb_AS_VR_ToF"
talents=1003232

copy="S2M_Mb_AS_VR_ToF"
talents=1003233

copy="LotV_Mb_AS_VR_FotM"
talents=2003231

copy="MSp_Mb_AS_VR_FotM"
talents=2003232

copy="S2M_Mb_AS_VR_FotM"
talents=2003233

copy="LotV_Mb_AS_VR_SWV"
talents=3003231

copy="MSp_Mb_AS_VR_SWV"
talents=3003232

copy="S2M_Mb_AS_VR_SWV"
talents=3003233', RaidEvents)), 'live', '7.0.3');
SET @SimulationId = LAST_INSERT_ID();
SET @SimulationLogId = 0;
CALL SimulationLogInsert(@SimulationId, 1, null, @SimulationLogId); 
INSERT INTO `BatchSimulations` (`BatchId`, `SimulationId`) VALUES
(@BatchId, @SimulationId);


SET @SimGUID = (SELECT UUID());
INSERT INTO `Simulations` (`SimulationName`, `SimulationGUID`, `UserId`, `SimulationTypeId`, `BossCount`, `Iterations`, `SimulationLength`, `SimulationLengthVariance`, `ScaleFactors`, `TMIWindow`, `TMIBoss`, `IsHidden`, `CustomProfile`, `SimulationCraftVersion`, `GameVersion`) VALUES
(CONCAT(BossCount, ' Target ', @BossName, ExtraText, ' (9/9)'), @SimGUID, 15, FightType, BossCount, 10000, 450, 0.2, 0, NULL, NULL, 1, (CONCAT(@BaseProfile, '

copy="LotV_PI_SI_VR_ToF"
talents=1003311

copy="MSp_PI_SI_VR_ToF"
talents=1003312

copy="S2M_PI_SI_VR_ToF"
talents=1003313

copy="LotV_PI_SI_VR_FotM"
talents=2003311

copy="MSp_PI_SI_VR_FotM"
talents=2003312

copy="S2M_PI_SI_VR_FotM"
talents=2003313

copy="LotV_PI_SI_VR_SWV"
talents=3003311

copy="MSp_PI_SI_VR_SWV"
talents=3003312

copy="S2M_PI_SI_VR_SWV"
talents=3003313

copy="LotV_SC_SI_VR_ToF"
talents=1003321

copy="MSp_SC_SI_VR_ToF"
talents=1003322

copy="S2M_SC_SI_VR_ToF"
talents=1003323

copy="LotV_SC_SI_VR_FotM"
talents=2003321

copy="MSp_SC_SI_VR_FotM"
talents=2003322

copy="S2M_SC_SI_VR_FotM"
talents=2003323

copy="LotV_SC_SI_VR_SWV"
talents=3003321

copy="MSp_SC_SI_VR_SWV"
talents=3003322

copy="S2M_SC_SI_VR_SWV"
talents=3003323

copy="LotV_Mb_SI_VR_ToF"
talents=1003331

copy="MSp_Mb_SI_VR_ToF"
talents=1003332

copy="S2M_Mb_SI_VR_ToF"
talents=1003333

copy="LotV_Mb_SI_VR_FotM"
talents=2003331

copy="MSp_Mb_SI_VR_FotM"
talents=2003332

copy="S2M_Mb_SI_VR_FotM"
talents=2003333

copy="LotV_Mb_SI_VR_SWV"
talents=3003331

copy="MSp_Mb_SI_VR_SWV"
talents=3003332

copy="S2M_Mb_SI_VR_SWV"
talents=3003333', RaidEvents)), 'live', '7.0.3');
SET @SimulationId = LAST_INSERT_ID();
SET @SimulationLogId = 0;
CALL SimulationLogInsert(@SimulationId, 1, null, @SimulationLogId); 
INSERT INTO `BatchSimulations` (`BatchId`, `SimulationId`) VALUES
(@BatchId, @SimulationId);

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `CustomSimulationQueue2` (IN `BossCount` TINYINT UNSIGNED, IN `FightType` TINYINT UNSIGNED, IN `ExtraText` VARCHAR(255), IN `RaidEvents` TEXT, IN `BatchID` INT UNSIGNED)  NO SQL
BEGIN


SET @BaseProfile = 'priest="BASELINE"
level=110
race=draenei
role=spell
position=back
talents=0000000
artifact=47:139251:139257:139251:0:764:1:765:1:767:3:768:1:769:1:770:1:771:3:772:5:773:3:775:3:776:4:777:3:1347:1
spec=shadow

# This default action priority list is automatically created based on your character.
# It is a attempt to provide you with a action list that is both simple and practicable,
# while resulting in a meaningful and good simulation. It may not result in the absolutely highest possible dps.
# Feel free to edit, adapt and improve it to your own needs.
# SimulationCraft is always looking for updates and improvements to the default action lists.

# Executed before combat begins. Accepts non-harmful actions only.

actions.precombat=flask,type=flask_of_the_whispered_pact
actions.precombat+=/food,type=azshari_salad
actions.precombat+=/augmentation,type=defiled
# Snapshot raid buffed stats before combat begins and pre-potting is done.
actions.precombat+=/snapshot_stats
actions.precombat+=/potion,name=deadly_grace
actions.precombat+=/mind_blast

# Executed every time the actor is available.

actions=potion,name=deadly_grace,if=buff.bloodlust.react|target.time_to_die<=40|buff.voidform.stack>80
actions+=/variable,name=s2mcheck,value=0.85*(45+((raw_haste_pct*100)*(2+(1*talent.reaper_of_souls.enabled)+(2*artifact.mass_hysteria.rank)-(1*talent.sanlayn.enabled))))-(5*nonexecute_actors_pct)
actions+=/call_action_list,name=s2m,if=buff.voidform.up&buff.surrender_to_madness.up
actions+=/call_action_list,name=vf,if=buff.voidform.up
actions+=/call_action_list,name=main

actions.main=surrender_to_madness,if=talent.surrender_to_madness.enabled&target.time_to_die<=variable.s2mcheck
actions.main+=/mindbender,if=talent.mindbender.enabled&!talent.surrender_to_madness.enabled
actions.main+=/mindbender,if=talent.mindbender.enabled&talent.surrender_to_madness.enabled&target.time_to_die>variable.s2mcheck+60
actions.main+=/shadow_word_pain,if=dot.shadow_word_pain.remains<(3+(4%3))*gcd
actions.main+=/vampiric_touch,if=dot.vampiric_touch.remains<(4+(4%3))*gcd
actions.main+=/void_eruption,if=insanity>=85|(talent.auspicious_spirits.enabled&insanity>=(80-shadowy_apparitions_in_flight*4))
actions.main+=/shadow_crash,if=talent.shadow_crash.enabled
actions.main+=/mindbender,if=talent.mindbender.enabled&set_bonus.tier18_2pc
actions.main+=/shadow_word_pain,if=!ticking&talent.legacy_of_the_void.enabled&insanity>=70,cycle_targets=1
actions.main+=/vampiric_touch,if=!ticking&talent.legacy_of_the_void.enabled&insanity>=70,cycle_targets=1
actions.main+=/shadow_word_death,if=!talent.reaper_of_souls.enabled&cooldown.shadow_word_death.charges=2&insanity<=90
actions.main+=/shadow_word_death,if=talent.reaper_of_souls.enabled&cooldown.shadow_word_death.charges=2&insanity<=70
actions.main+=/mind_blast,if=talent.legacy_of_the_void.enabled&(insanity<=81|(insanity<=75.2&talent.fortress_of_the_mind.enabled))
actions.main+=/mind_blast,if=!talent.legacy_of_the_void.enabled|(insanity<=96|(insanity<=95.2&talent.fortress_of_the_mind.enabled))
actions.main+=/shadow_word_pain,if=!ticking&target.time_to_die>10&(active_enemies<5&(talent.auspicious_spirits.enabled|talent.shadowy_insight.enabled)),cycle_targets=1
actions.main+=/vampiric_touch,if=!ticking&target.time_to_die>10&(active_enemies<4|talent.sanlayn.enabled|(talent.auspicious_spirits.enabled&artifact.unleash_the_shadows.rank)),cycle_targets=1
actions.main+=/shadow_word_pain,if=!ticking&target.time_to_die>10&(active_enemies<5&artifact.sphere_of_insanity.rank),cycle_targets=1
actions.main+=/shadow_word_void,if=(insanity<=70&talent.legacy_of_the_void.enabled)|(insanity<=85&!talent.legacy_of_the_void.enabled)
actions.main+=/mind_sear,if=active_enemies>=3,interrupt=1,chain=1
actions.main+=/mind_flay,if=!talent.mind_spike.enabled,interrupt=1,chain=1
actions.main+=/mind_spike,if=talent.mind_spike.enabled
actions.main+=/shadow_word_pain

actions.vf=surrender_to_madness,if=talent.surrender_to_madness.enabled&insanity>=25&(cooldown.void_bolt.up|cooldown.void_torrent.up|cooldown.shadow_word_death.up|buff.shadowy_insight.up)&target.time_to_die<=variable.s2mcheck-(buff.insanity_drain_stacks.stack)
actions.vf+=/shadow_crash,if=talent.shadow_crash.enabled
actions.vf+=/mindbender,if=talent.mindbender.enabled&!talent.surrender_to_madness.enabled
actions.vf+=/mindbender,if=talent.mindbender.enabled&talent.surrender_to_madness.enabled&target.time_to_die>variable.s2mcheck-(buff.insanity_drain_stacks.stack)+30
actions.vf+=/dispersion,if=!buff.power_infusion.up&!buff.berserking.up&!buff.bloodlust.up&artifact.void_torrent.rank&!talent.surrender_to_madness.enabled
actions.vf+=/dispersion,if=!buff.power_infusion.up&!buff.berserking.up&!buff.bloodlust.up&artifact.void_torrent.rank&talent.surrender_to_madness.enabled&target.time_to_die>variable.s2mcheck-(buff.insanity_drain_stacks.stack)+(120-10*artifact.from_the_shadows.rank)
actions.vf+=/power_infusion,if=buff.voidform.stack>=10&buff.insanity_drain_stacks.stack<=30&!talent.surrender_to_madness.enabled
actions.vf+=/power_infusion,if=buff.voidform.stack>=10&talent.surrender_to_madness.enabled&target.time_to_die>variable.s2mcheck-(buff.insanity_drain_stacks.stack)+15
actions.vf+=/berserking,if=buff.voidform.stack>=10&buff.insanity_drain_stacks.stack<=20&!talent.surrender_to_madness.enabled
actions.vf+=/berserking,if=buff.voidform.stack>=10&talent.surrender_to_madness.enabled&target.time_to_die>variable.s2mcheck-(buff.insanity_drain_stacks.stack)+70
actions.vf+=/void_bolt,if=dot.shadow_word_pain.remains<3.5*gcd&dot.vampiric_touch.remains<3.5*gcd&target.time_to_die>10,cycle_targets=1
actions.vf+=/void_bolt,if=dot.shadow_word_pain.remains<3.5*gcd&(talent.auspicious_spirits.enabled|talent.shadowy_insight.enabled)&target.time_to_die>10,cycle_targets=1
actions.vf+=/void_bolt,if=dot.vampiric_touch.remains<3.5*gcd&(talent.sanlayn.enabled|(talent.auspicious_spirits.enabled&artifact.unleash_the_shadows.rank))&target.time_to_die>10,cycle_targets=1
actions.vf+=/void_bolt,if=dot.shadow_word_pain.remains<3.5*gcd&artifact.sphere_of_insanity.rank&target.time_to_die>10,cycle_targets=1
actions.vf+=/void_bolt
actions.vf+=/void_torrent,if=!talent.surrender_to_madness.enabled
actions.vf+=/void_torrent,if=talent.surrender_to_madness.enabled&target.time_to_die>variable.s2mcheck-(buff.insanity_drain_stacks.stack)+60
actions.vf+=/shadow_word_death,if=!talent.reaper_of_souls.enabled&current_insanity_drain*gcd.max>insanity&(insanity-(current_insanity_drain*gcd.max)+10)<100
actions.vf+=/shadow_word_death,if=talent.reaper_of_souls.enabled&current_insanity_drain*gcd.max>insanity&(insanity-(current_insanity_drain*gcd.max)+30)<100
actions.vf+=/wait,sec=action.void_bolt.usable_in,if=action.void_bolt.usable_in<gcd.max*0.25
actions.vf+=/mind_blast
actions.vf+=/wait,sec=action.mind_blast.usable_in,if=action.mind_blast.usable_in<gcd.max*0.25
actions.vf+=/shadow_word_death,if=cooldown.shadow_word_death.charges=2
actions.vf+=/shadowfiend,if=!talent.mindbender.enabled,if=buff.voidform.stack>15
actions.vf+=/shadow_word_void,if=(insanity-(current_insanity_drain*gcd.max)+25)<100
actions.vf+=/shadow_word_pain,if=!ticking&(active_enemies<5|talent.auspicious_spirits.enabled|talent.shadowy_insight.enabled|artifact.sphere_of_insanity.rank)
actions.vf+=/vampiric_touch,if=!ticking&(active_enemies<4|talent.sanlayn.enabled|(talent.auspicious_spirits.enabled&artifact.unleash_the_shadows.rank))
actions.vf+=/shadow_word_pain,if=!ticking&target.time_to_die>10&(active_enemies<5&(talent.auspicious_spirits.enabled|talent.shadowy_insight.enabled)),cycle_targets=1
actions.vf+=/vampiric_touch,if=!ticking&target.time_to_die>10&(active_enemies<4|talent.sanlayn.enabled|(talent.auspicious_spirits.enabled&artifact.unleash_the_shadows.rank)),cycle_targets=1
actions.vf+=/shadow_word_pain,if=!ticking&target.time_to_die>10&(active_enemies<5&artifact.sphere_of_insanity.rank),cycle_targets=1
actions.vf+=/wait,sec=action.void_bolt.usable_in,if=action.void_bolt.usable|action.void_bolt.usable_in<gcd.max*0.8
actions.vf+=/mind_sear,if=active_enemies>=3,interrupt=1
actions.vf+=/mind_flay,if=!talent.mind_spike.enabled,chain=1,interrupt_immediate=1,interrupt_if=action.void_bolt.usable_in<gcd.max*0.8
actions.vf+=/mind_spike,if=talent.mind_spike.enabled
actions.vf+=/shadow_word_pain

actions.s2m=shadow_crash,if=talent.shadow_crash.enabled
actions.s2m+=/mindbender,if=talent.mindbender.enabled
actions.s2m+=/dispersion,if=!buff.power_infusion.up&!buff.berserking.up&!buff.bloodlust.up
actions.s2m+=/power_infusion,if=buff.insanity_drain_stacks.stack>=85
actions.s2m+=/berserking,if=buff.voidform.stack>=90
actions.s2m+=/void_bolt,if=dot.shadow_word_pain.remains<3.5*gcd&dot.vampiric_touch.remains<3.5*gcd&target.time_to_die>10,cycle_targets=1
actions.s2m+=/void_bolt,if=dot.shadow_word_pain.remains<3.5*gcd&(talent.auspicious_spirits.enabled|talent.shadowy_insight.enabled)&target.time_to_die>10,cycle_targets=1
actions.s2m+=/void_bolt,if=dot.vampiric_touch.remains<3.5*gcd&(talent.sanlayn.enabled|(talent.auspicious_spirits.enabled&artifact.unleash_the_shadows.rank))&target.time_to_die>10,cycle_targets=1
actions.s2m+=/void_bolt,if=dot.shadow_word_pain.remains<3.5*gcd&artifact.sphere_of_insanity.rank&target.time_to_die>10,cycle_targets=1
actions.s2m+=/void_bolt
actions.s2m+=/void_torrent
actions.s2m+=/shadow_word_death,if=!talent.reaper_of_souls.enabled&current_insanity_drain*gcd.max>insanity&(insanity-(current_insanity_drain*gcd.max)+30)<100
actions.s2m+=/shadow_word_death,if=talent.reaper_of_souls.enabled&current_insanity_drain*gcd.max>insanity&(insanity-(current_insanity_drain*gcd.max)+90)<100
actions.s2m+=/wait,sec=action.void_bolt.usable_in,if=action.void_bolt.usable_in<gcd.max*0.25
actions.s2m+=/mind_blast
actions.s2m+=/wait,sec=action.mind_blast.usable_in,if=action.mind_blast.usable_in<gcd.max*0.25
actions.s2m+=/shadow_word_death,if=cooldown.shadow_word_death.charges=2
actions.s2m+=/shadowfiend,if=!talent.mindbender.enabled,if=buff.voidform.stack>15
actions.s2m+=/shadow_word_void,if=(insanity-(current_insanity_drain*gcd.max)+75)<100
actions.s2m+=/shadow_word_pain,if=!ticking&(active_enemies<5|talent.auspicious_spirits.enabled|talent.shadowy_insight.enabled|artifact.sphere_of_insanity.rank)
actions.s2m+=/vampiric_touch,if=!ticking&(active_enemies<4|talent.sanlayn.enabled|(talent.auspicious_spirits.enabled&artifact.unleash_the_shadows.rank))
actions.s2m+=/shadow_word_pain,if=!ticking&target.time_to_die>10&(active_enemies<5&(talent.auspicious_spirits.enabled|talent.shadowy_insight.enabled)),cycle_targets=1
actions.s2m+=/vampiric_touch,if=!ticking&target.time_to_die>10&(active_enemies<4|talent.sanlayn.enabled|(talent.auspicious_spirits.enabled&artifact.unleash_the_shadows.rank)),cycle_targets=1
actions.s2m+=/shadow_word_pain,if=!ticking&target.time_to_die>10&(active_enemies<5&artifact.sphere_of_insanity.rank),cycle_targets=1
actions.s2m+=/wait,sec=action.void_bolt.usable_in,if=action.void_bolt.usable|action.void_bolt.usable_in<gcd.max*0.8
actions.s2m+=/mind_sear,if=active_enemies>=3,interrupt=1
actions.s2m+=/mind_flay,if=!talent.mind_spike.enabled,chain=1,interrupt_immediate=1,interrupt_if=action.void_bolt.usable_in<gcd.max*0.8
actions.s2m+=/mind_spike,if=talent.mind_spike.enabled

head=hood_of_darkened_visions,id=139189,bonus_id=1805
neck=blackened_portalstone_necklace,id=139332,bonus_id=1805,enchant=mark_of_the_claw
shoulders=mantle_of_perpetual_bloom,id=139192,bonus_id=1805
back=evergreen_vinewrap_drape,id=139248,bonus_id=1805,enchant=200int
chest=dreamscale_inlaid_vestments,id=138215,bonus_id=1805
wrists=cinch_of_cosmic_insignficance,id=139187,bonus_id=1805
hands=handwraps_of_delusional_power,id=138212,bonus_id=1805
waist=pliable_spider_silk_cinch,id=138217,bonus_id=1805
legs=nazaks_dusty_pantaloons,id=141415
feet=cozy_dryad_hoofsocks,id=139194,bonus_id=1805
finger1=twicewarped_azsharan_signet,id=139238,bonus_id=1805,enchant=200haste
finger2=dreadful_cyclopean_signet,id=139237,bonus_id=1805,enchant=200haste
trinket1=swarming_plaguehive,id=139321,bonus_id=1805
trinket2=twisting_wind,id=139323,bonus_id=1805
main_hand=xalatath_blade_of_the_black_empire,id=128827,bonus_id=740,gem_id=139251/139257/139251,relic_id=1805/1805/1805
off_hand=secrets_of_the_void,id=133958

# Gear Summary
# gear_ilvl=868.31
# gear_stamina=22518
# gear_intellect=24778
# gear_crit_rating=6491
# gear_haste_rating=8895
# gear_mastery_rating=3151
# gear_armor=1711';

SET @BossName = (select SimulationTypeSystemName from SimulationTypes where SimulationTypeId = FightType);

SET @SimGUID = (SELECT UUID());
INSERT INTO `Simulations` (`SimulationName`, `SimulationGUID`, `UserId`, `SimulationTypeId`, `BossCount`, `Iterations`, `SimulationLength`, `SimulationLengthVariance`, `ScaleFactors`, `TMIWindow`, `TMIBoss`, `IsHidden`, `CustomProfile`, `SimulationCraftVersion`, `GameVersion`) VALUES
(CONCAT(BossCount, ' Target ', @BossName, ExtraText), @SimGUID, 15, FightType, BossCount, 25000, 450, 0.2, 1, NULL, NULL, 1, (CONCAT(@BaseProfile, RaidEvents)), 'live', '7.0.3');
SET @SimulationId = LAST_INSERT_ID();
SET @SimulationLogId = 0;
CALL SimulationLogInsert(@SimulationId, 1, null, @SimulationLogId); 
INSERT INTO `BatchSimulations` (`BatchId`, `SimulationId`) VALUES
(BatchId, @SimulationId);

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `LoginAttemptInsert` (IN `UserId` INT UNSIGNED, IN `ResultCode` TINYINT, IN `IPAddress` VARCHAR(45) CHARSET utf8)  NO SQL
BEGIN

	SET @ExitOnError = 0;

	IF UserId = '' THEN
		SET UserId = null;
        SET ResultCode = -1;
	END IF;
    
	IF ResultCode = '' THEN
		CALL raise(1356, 'Parameter `ResultCode` is required by SPROC LoginAttemptInsert');
        SET @ExitOnError = 1;
	END IF;
    
    IF @ExitOnError = 0 THEN
    	
    	INSERT INTO	LoginAttempts (UserId, ResultCode, IPAddress)
        VALUES (UserId, ResultCode, IPAddress);
    	
    END IF;
    
END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `LoginAttemptList` (IN `UserId` INT UNSIGNED, IN `ResultCode` TINYINT UNSIGNED, IN `TimeOffset` INT)  NO SQL
BEGIN

	IF UserId = '' THEN
    
		SET UserId = null;
        
	END IF;

	IF ResultCode = '' THEN
    
		SET ResultCode = null;
        
	END IF;

	IF TimeOffset = '' THEN
    
		SET TimeOffset = null;
        
	END IF;

	SELECT	* 
	FROM	LoginAttempts as la
	WHERE	CASE WHEN UserId Is Null THEN TRUE ELSE la.UserId = UserId END
	AND		CASE WHEN ResultCode Is Null THEN TRUE ELSE la.ResultCode = ResultCode END
	AND		CASE WHEN TimeOffset Is Null THEN TRUE ELSE la.Time > DATE_ADD(NOW(), INTERVAL TimeOffset MINUTE) END;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `NodeGitRevisionCheck` (IN `ComputerId` MEDIUMINT(5) UNSIGNED, IN `ComputerAPIKey` VARCHAR(38), IN `IPAddress` VARCHAR(45), IN `GitRevision` VARCHAR(40))  NO SQL
BEGIN
        
	IF (ComputerId = '' OR ComputerId Is Null) AND (ComputerAPIKey = '' OR ComputerAPIKey Is Null) THEN
    
		SET ComputerAPIKey = null;
        SET ComputerId = null;
        
    ELSEIF (ComputerId = '' OR ComputerId Is Null) THEN
    
		SET ComputerId = (
            SELECT	c.ComputerId
        	FROM	Computers c
        	WHERE	c.ComputerAPIKey = ComputerAPIKey
        );
       
        IF ComputerId Is Null OR ComputerId < 1 THEN
        
        	SET ComputerId = null;
        
        END IF;
    
	END IF;

	CALL ConnectionLogInsert(ComputerId, ComputerAPIKey, null, IPAddress);
    
	IF ComputerId > 0 THEN

		SET @LatestGitRevision = (
            SELECT	cv.Value
        	FROM	ConfigurationValues cv
        	WHERE	cv.Name = 'GitRevision'
        );

		IF @LatestGitRevision = GitRevision THEN
        
        	SELECT	1 as Result;
        
        ELSE
        
        	SELECT	@LatestGitRevision as Result;
        
        END IF;

	END IF;    

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `QueuedSimulationCount` (IN `UserId` INT UNSIGNED)  NO SQL
BEGIN
    
	IF UserId = '' THEN
    
		SET UserId = null;
        
	END IF;
    
	SELECT	COUNT(DISTINCT(vsd.SimulationId)) as TotalQueue
    		,SUM(GREATEST(1, CEILING((vsd.BossCount * vsd.ActorCount * (1 + (vsd.ScaleFactors * 4)) * (vsd.Iterations / 10000))))) as WeightedQueue
	FROM	vSimulationsDetails as vsd
	JOIN	Users as u on vsd.UserId = u.UserId
    WHERE	CASE WHEN UserId Is Null THEN TRUE ELSE u.UserId = UserId END
	AND		(	vsd.SimulationStatusId = 1
           	);


END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `RegionGet` (IN `RegionId` TINYINT UNSIGNED, IN `ServerId` SMALLINT UNSIGNED)  NO SQL
BEGIN

    SET @ExitOnError = 0;
    
	IF RegionId = 0 AND ServerId = 0 THEN

		CALL raise(1356, 'One of the parameters `RegionId` or `ServerId` is required by SPROC RegionGet');
		SET @ExitOnError = 1;    
    
	ELSEIF RegionId > 0 AND ServerId > 0 THEN
		
        CALL raise(1356, 'Only one of the parameters `RegionId` or `ServerId` is required by SPROC RegionGet');
		SET @ExitOnError = 1;

    END IF;

	IF @ExitOnError = 0 THEN

		IF RegionId > 0 THEN

            SELECT	reg.RegionId
                    ,reg.RegionName
                    ,reg.RegionPrefix
                    ,reg.RegionURL
                    ,reg.RegionAPIUrl
                    ,reg.RegionThumbnailURL
            FROM	Regions as reg
            WHERE	RegionId = reg.RegionId
            LIMIT 1;

		ELSE

            SELECT	reg.RegionId
                    ,reg.RegionName
                    ,reg.RegionPrefix
                    ,reg.RegionURL
            		,reg.RegionAPIUrl
            FROM	Servers as srv
            JOIN	Regions as reg on reg.RegionId = srv.RegionId
            WHERE	ServerId = srv.ServerId
            LIMIT 1;
        
        END IF;

	END IF;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `RegionList` ()  NO SQL
BEGIN

	SELECT	reg.RegionId
            ,reg.RegionName
            ,reg.RegionPrefix
            ,reg.RegionURL
            ,reg.RegionAPIUrl
            ,reg.RegionThumbnailURL
	FROM	Regions as reg
    ORDER BY	reg.RegionId ASC;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `ReportsToArchiveList` (IN `ComputerID` MEDIUMINT UNSIGNED, IN `ComputerAPIKey` VARCHAR(38), IN `IPAddress` VARCHAR(45))  NO SQL
BEGIN
	
    SET @ExitOnError = 0;
    
    IF @ExitOnError = 0 THEN
	
    	IF (ComputerId = '' OR ComputerId Is Null) AND (ComputerAPIKey = '' OR ComputerAPIKey Is Null) THEN
    
			SET ComputerAPIKey = null;
	        SET ComputerId = null;
        
    	ELSEIF (ComputerId = '' OR ComputerId Is Null) THEN
    
			SET ComputerId = (
            	SELECT	c.ComputerId
	        	FROM	Computers c
    	    	WHERE	c.ComputerAPIKey = ComputerAPIKey
        	);
        	
	        IF ComputerId Is Null OR ComputerId < 1 THEN
    	    
        		SET ComputerId = null;
        	
	        END IF;
    
		END IF;
	
    	SET @ResultCode = 0;

		CALL ConnectionLogInsert(@ComputerId, ComputerAPIKey, null, IPAddress);

		IF ComputerId > 0 THEN

			SELECT	sim.SimulationId
					,u.UserId
			        ,u.Email
			        ,sim.SimulationGUID
			        ,sim.TimeQueued
			        ,NOW() as TimeNow
			        ,TIMESTAMPDIFF(DAY,sim.TimeQueued,NOW()) as DateDiff
        			,ul.DaysBeforeCleanup
			FROM	Simulations as sim
			JOIN	Users as u on sim.UserId = u.UserId
			JOIN	UserLevels as ul on ul.UserLevelId = u.UserLevelId
			WHERE	(TIMESTAMPDIFF(DAY,sim.TimeQueued,NOW()) > ul.DaysBeforeCleanup
					AND		sim.ReportArchived = 0)
			OR		sim.ReportArchived = -1;

		END IF;

    END IF;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `ServerGet` (IN `ServerId` SMALLINT UNSIGNED)  NO SQL
BEGIN

	SELECT	srv.ServerId
    		,srv.ServerName
            ,srv.ServerType
            ,loc.LocaleId
            ,loc.Locale
            ,loc.LocaleName
            ,reg.RegionId
            ,reg.RegionName
            ,reg.RegionPrefix
            ,reg.RegionURL
            ,reg.RegionAPIUrl
	FROM	Servers as srv
    JOIN	Locales as loc on srv.Locale = loc.LocaleId
    JOIN	Regions as reg on srv.RegionId = reg.RegionId
    WHERE	srv.ServerId = ServerId;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `ServerList` (IN `RegionId` TINYINT UNSIGNED)  NO SQL
BEGIN

	SELECT	srv.ServerId
    		,srv.ServerName
            ,srv.ServerType
            ,loc.LocaleId
            ,loc.Locale
            ,loc.LocaleName
            ,reg.RegionId
            ,reg.RegionName
            ,reg.RegionPrefix
            ,reg.RegionURL
            ,reg.RegionAPIUrl
	FROM	Servers as srv
    JOIN	Locales as loc on srv.Locale = loc.LocaleId
    JOIN	Regions as reg on srv.RegionId = reg.RegionId
    WHERE	CASE WHEN RegionId Is Null THEN TRUE ELSE RegionId = reg.RegionId END
    AND		reg.RegionId <> 5
    ORDER BY	reg.RegionId ASC, srv.ServerName ASC;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SimulationActorInsert` (IN `SimulationId` INT UNSIGNED, IN `ServerId` SMALLINT UNSIGNED, IN `CharacterName` VARCHAR(20), IN `Level` TINYINT UNSIGNED, IN `ClassId` TINYINT UNSIGNED, IN `RaceId` TINYINT UNSIGNED, IN `Gender` TINYINT UNSIGNED, IN `ThumbnailURL` VARCHAR(255), IN `CharacterJSON` TEXT, IN `SpecializationName` VARCHAR(30), IN `CalcTalent` VARCHAR(7), IN `CalcGlyph` VARCHAR(6), IN `ItemLevel` SMALLINT UNSIGNED, IN `ArmorySpec` VARCHAR(8), IN `SimulationRole` VARCHAR(4), IN `SimTalent` VARCHAR(7), IN `Talent1Id` INT UNSIGNED, IN `Talent2Id` INT UNSIGNED, IN `Talent3Id` INT UNSIGNED, IN `Talent4Id` INT UNSIGNED, IN `Talent5Id` INT UNSIGNED, IN `Talent6Id` INT UNSIGNED, IN `Talent7Id` INT UNSIGNED, IN `SpecializationId` SMALLINT UNSIGNED, IN `IsCustomActor` TINYINT(1) UNSIGNED, IN `CustomActorName` VARCHAR(255))  NO SQL
BEGIN

	SET @ExitOnError = 0;

	IF (ServerId = '' OR ServerId Is Null) AND IsCustomActor = 0 THEN
		CALL raise(1356, 'Parameter `ServerId` is required by SPROC SimulationActorInsert');
		SET @ExitOnError = 1;
	END IF;

	IF (CharacterName = '' OR CharacterName Is Null) AND IsCustomActor = 0 THEN
		CALL raise(1356, 'Parameter `CharacterName` is required by SPROC SimulationActorInsert');
		SET @ExitOnError = 1;
	END IF;

	IF (CharacterJSON = '' OR CharacterJSON Is Null) AND IsCustomActor = 0 THEN
		CALL raise(1356, 'Parameter `CharacterJSON` is required by SPROC SimulationActorInsert');
		SET @ExitOnError = 1;
	END IF;

	IF (SimulationRole = '' OR SimulationRole Is Null) AND IsCustomActor = 0 THEN
		SET SimulationRole = 'DPS';
	END IF;
    
    IF @ExitOnError = 0 THEN
    	
        SET @CharacterId = NULL;
        
        IF SpecializationName <> '' OR SpecializationName Is NOT Null THEN

            SET @SpecializationId = (
                SELECT	s.SpecializationId
                FROM	Specializations as s
                WHERE	s.ClassId = ClassId
                AND		s.CalcSpec = SpecializationName
            );
        
        ELSE
        
        	SET @SpecializationId = SpecializationId;
        
        END IF;
       
        IF IsCustomActor = 0 THEN
        
        	CALL CharacterGetId(CharacterName, ServerId, @CharacterId);
            
        END IF;
        
        INSERT INTO SimulationActors (SimulationId, CharacterId, ClassId, SpecializationId, RaceId, Gender, Level, ItemLevel, ThumbnailURL, CharacterJSON, CalcTalent, CalcGlyph, ArmorySpec, SimulationRole, SimTalent, Talent1Id, Talent2Id, Talent3Id, Talent4Id, Talent5Id, Talent6Id, Talent7Id, IsCustomActor, CustomActorName)
        VALUES (SimulationId, @CharacterId, ClassId, @SpecializationId, RaceId, Gender, Level, ItemLevel, ThumbnailURL, CharacterJSON, CalcTalent, CalcGlyph, ArmorySpec, SimulationRole, SimTalent, Talent1Id, Talent2Id, Talent3Id, Talent4Id, Talent5Id, Talent6Id, Talent7Id, IsCustomActor, CustomActorName);
	
    	SET @SimulationActorId = LAST_INSERT_ID();
        
        SELECT @SimulationActorId as SimulationActorId;
    
    END IF;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SimulationArchive` (IN `SimulationGUID` VARCHAR(38), IN `UserId` INT UNSIGNED)  NO SQL
BEGIN

    SET @SimulationId = (
        SELECT	sim.SimulationId
        FROM	Simulations as sim
        WHERE	sim.SimulationGUID = SimulationGUID
        );
    
    SET @SimulationOwnerId = (
        SELECT	sim.UserId
        FROM	Simulations as sim
        WHERE	sim.SimulationId = @SimulationId
        );
    
    SET @IsArchived = (
        SELECT	sim.SimulationArchived
        FROM	Simulations as sim
        WHERE	sim.SimulationId = @SimulationId
        );
    
    SET @Status = (
        SELECT	vsd.SimulationStatusId
        FROM	vSimulationsDetails as vsd
        WHERE	vsd.SimulationId = @SimulationId
        );
    
    SET @UserLevel = (
        SELECT	u.UserLevelId
        FROM	Users as u
        JOIN	UserLevels as ul on u.UserLevelId = ul.UserLevelId
        WHERE	u.UserId = UserId
        );
    
    IF @IsArchived = 0 AND @Status >= 3 AND (@SimulationOwnerId = UserId OR @UserLevel = 9) THEN
    
    	UPDATE	Simulations as sim
        SET		sim.ReportArchived = 1
            	,sim.TimeArchived = NOW()
                ,sim.SimulationArchived = 1
            	,sim.SimulationTimeArchived = NOW()
        WHERE	sim.SimulationId = @SimulationId;
    
        SELECT	sim.ReportArchived as Response
        		,sim.TimeArchived as TimeArchived
    	FROM	Simulations as sim
        WHERE	sim.SimulationId = @SimulationId;
    
    ELSE
    
    	SELECT -1 as Response;
    
    END IF;
   
END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SimulationCountsGet` (IN `UserId` INT UNSIGNED)  NO SQL
BEGIN


	IF UserId Is Not Null Then

        SELECT	                u.UserId
                ,u.Email
                ,ul.UserLevelId
                ,ul.UserLevelTitle
        FROM	Users as u
        JOIN	UserLevels as ul on u.UserLevelId = ul.UserLevelId
        WHERE	u.UserId = UserId;
                                                
        SELECT	                vsad.CharacterId
                ,vsad.CharacterName
                ,vsad.ServerId
                ,vsad.ServerName
                ,vsad.ServerType
                ,vsad.RegionId
                ,vsad.RegionName
                ,vsad.RegionPrefix
                ,vsad.ClassId
                ,vsad.ClassName
        FROM	vSimulationActorDetails as vsad
        WHERE	CASE WHEN UserId Is Null THEN 1 = 1 ELSE vsad.UserId = UserId END
        GROUP BY CharacterId
        ORDER BY RegionId ASC, ServerName ASC, CharacterName ASC;

	END IF;
    
    
    

	SELECT	            vsad.ClassId
            ,vsad.ClassName
	FROM	vSimulationActorDetails as vsad
	WHERE	CASE WHEN UserId Is Null THEN 1 = 1 ELSE vsad.UserId = UserId END
    AND		vsad.ClassId < 99
	GROUP BY ClassId
	ORDER BY ClassName ASC;

	SELECT	            vsad.ClassId
            ,vsad.ClassName
            ,vsad.SpecializationId
            ,vsad.SpecializationName
	FROM	vSimulationActorDetails as vsad
	WHERE	CASE WHEN UserId Is Null THEN 1 = 1 ELSE vsad.UserId = UserId END
    AND		vsad.SpecializationId < 9999
	GROUP BY SpecializationId
	ORDER BY ClassName ASC, SpecializationName ASC;

	SELECT				vsad.ServerId
            ,vsad.ServerName
            ,vsad.ServerType
            ,vsad.RegionId
            ,vsad.RegionName
            ,vsad.RegionPrefix
	FROM	vSimulationActorDetails as vsad
	WHERE	CASE WHEN UserId Is Null THEN 1 = 1 ELSE vsad.UserId = UserId END
	GROUP BY ServerId
	ORDER BY RegionId ASC, ServerName ASC;

	SELECT				vsd.SimulationTypeId
            ,vsd.SimulationTypeDescription
            ,vsd.SimulationTypeFriendlyName
            ,vsd.SimulationTypeSystemName
	FROM	vSimulationsDetails as vsd
	WHERE	CASE WHEN UserId Is Null THEN 1 = 1 ELSE vsd.UserId = UserId END
	GROUP BY SimulationTypeId
	ORDER BY SimulationTypeId ASC;

	SELECT				vsd.Iterations
	FROM	vSimulationsDetails as vsd
	WHERE	CASE WHEN UserId Is Null THEN 1 = 1 ELSE vsd.UserId = UserId END
    AND		vsd.Iterations >= 1000
	GROUP BY vsd.Iterations
	ORDER BY vsd.Iterations ASC;

	SELECT				vsd.SimulationStatusId
            ,vsd.StatusName
            ,vsd.StatusDescription
	FROM	vSimulationsDetails as vsd
	WHERE	CASE WHEN UserId Is Null THEN 1 = 1 ELSE vsd.UserId = UserId END
	GROUP BY vsd.SimulationStatusId
	ORDER BY vsd.SimulationStatusId ASC;

	SELECT				vsad.ItemLevel
	FROM	vSimulationActorDetails as vsad
	WHERE	CASE WHEN UserId Is Null THEN 1 = 1 ELSE vsad.UserId = UserId END
    AND		vsad.ItemLevel >= 640
	GROUP BY vsad.ItemLevel
	ORDER BY vsad.ItemLevel ASC;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SimulationGet` (IN `SimulationId` INT UNSIGNED, IN `GetCharacterJSON` TINYINT(1) UNSIGNED, IN `GetSimulationLog` TINYINT(1) UNSIGNED)  NO SQL
BEGIN

	SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED ;
    SELECT	vsd.*
       		#,qp.QueuePosition
            ,CASE GetSimulationLog WHEN 0 THEN Null ELSE SimulationRawLog END as SimulationRawLog
       		,(SELECT	COUNT(*)
              FROM	vSimulationsDetails as v 
              WHERE	v.SimulationStatusId = 1) as TotalQueue
	FROM	vSimulationsDetails as vsd
    #LEFT JOIN	(SELECT	t.SimulationId
    #			,(SELECT	COUNT(*)
    #				FROM	vSimulationsDetails as x
    #     				WHERE	x.SimulationId <= t.SimulationId
    #                    AND		(		x.SimulationStatusId = 1)) AS QueuePosition
	#			FROM	vSimulationsDetails as t
    #            WHERE	t.SimulationStatusId = 1) as qp on qp.SimulationId = vsd.SimulationId
    JOIN	Simulations as sim on vsd.SimulationId = sim.SimulationId
	WHERE	vsd.SimulationId = SimulationId;
    COMMIT ;
    
    SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED ;
    SELECT	vsad.*
            ,CASE GetCharacterJSON WHEN 0 THEN Null ELSE sa.CharacterJSON END as CharacterJSON
	FROM	vSimulationActorDetails as vsad
    JOIN	SimulationActors as sa on vsad.SimulationActorId = sa.SimulationActorId
	WHERE	vsad.SimulationId = SimulationId
    ORDER BY	vsad.CharacterName ASC, vsad.CustomActorName ASC;
    COMMIT ;

	SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED ;
	SELECT	sl.SimulationLogId
			,sl.SimulationLogTime
            ,sl.IsArchive
	        ,ss.SimulationStatusId
        	,ss.StatusName
    	    ,ss.StatusDescription
	        ,c.ComputerId
        	,c.ComputerName
    	    ,c.ComputerDescription
	FROM	SimulationLog as sl
	JOIN	SimulationStatus as ss on sl.SimulationStatusId = ss.SimulationStatusId
	LEFT JOIN	Computers as c on sl.ComputerId = c.ComputerId
	WHERE	sl.SimulationId = SimulationId
	ORDER BY	sl.SimulationLogTime ASC;
    COMMIT ;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SimulationGetByGUID` (IN `SimulationGUID` VARCHAR(38), IN `GetCharacterJSON` TINYINT UNSIGNED, IN `GetSimulationLog` TINYINT UNSIGNED)  NO SQL
BEGIN

	SET @SimulationId = (
        SELECT	sim.SimulationId
        FROM	Simulations as sim
        WHERE	sim.SimulationGUID = SimulationGUID
        );
    
    IF @SimulationId > 0 THEN
    
    	CALL SimulationGet(@SimulationId, GetCharacterJSON, GetSimulationLog);
    
    END IF;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SimulationGetByGUID_New` (IN `SimulationGUID` VARCHAR(38), IN `GetCharacterJSON` TINYINT UNSIGNED, IN `GetSimulationLog` TINYINT UNSIGNED)  NO SQL
BEGIN

	SET @SimulationId = (
        SELECT	sim.SimulationId
        FROM	Simulations as sim
        WHERE	sim.SimulationGUID = SimulationGUID
        );
    
    IF @SimulationId > 0 THEN
    
    	CALL SimulationGet_New(@SimulationId, GetCharacterJSON, GetSimulationLog);
    
    END IF;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SimulationGet_New` (IN `SimulationId` INT UNSIGNED, IN `GetCharacterJSON` TINYINT(1) UNSIGNED, IN `GetSimulationLog` TINYINT(1) UNSIGNED)  NO SQL
BEGIN

	SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED ;
    SELECT	vsd.*
       		#,qp.QueuePosition
            ,CASE GetSimulationLog WHEN 0 THEN Null ELSE SimulationRawLog END as SimulationRawLog
       		,(SELECT	COUNT(*)
              FROM	vSimulationsDetails as v 
              WHERE	v.SimulationStatusId = 1) as TotalQueue
	FROM	vSimulationsDetails as vsd
    #LEFT JOIN	(SELECT	t.SimulationId
    #			,(SELECT	COUNT(*)
    #				FROM	vSimulationsDetails as x
    #     				WHERE	x.SimulationId <= t.SimulationId
    #                    AND		(		x.SimulationStatusId = 1)) AS QueuePosition
	#			FROM	vSimulationsDetails as t
    #            WHERE	t.SimulationStatusId = 1) as qp on qp.SimulationId = vsd.SimulationId
    JOIN	Simulations as sim on vsd.SimulationId = sim.SimulationId
	WHERE	vsd.SimulationId = SimulationId;
    COMMIT ;
    
    SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED ;
    SELECT	vsad.*
            ,CASE GetCharacterJSON WHEN 0 THEN Null ELSE sa.CharacterJSON END as CharacterJSON
	FROM	vSimulationActorDetails as vsad
    JOIN	SimulationActors as sa on vsad.SimulationActorId = sa.SimulationActorId
	WHERE	vsad.SimulationId = SimulationId
    ORDER BY	vsad.CharacterName ASC, vsad.CustomActorName ASC;
    COMMIT ;

	SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED ;
	SELECT	sl.SimulationLogId
			,sl.SimulationLogTime
            ,sl.IsArchive
	        ,ss.SimulationStatusId
        	,ss.StatusName
    	    ,ss.StatusDescription
	        ,c.ComputerId
        	,c.ComputerName
    	    ,c.ComputerDescription
	FROM	SimulationLog as sl
	JOIN	SimulationStatus as ss on sl.SimulationStatusId = ss.SimulationStatusId
	LEFT JOIN	Computers as c on sl.ComputerId = c.ComputerId
	WHERE	sl.SimulationId = SimulationId
	ORDER BY	sl.SimulationLogTime ASC;
    COMMIT ;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SimulationInsert` (IN `UserId` INT UNSIGNED, IN `Iterations` INT UNSIGNED, IN `SimulationTypeId` TINYINT UNSIGNED, IN `ScaleFactors` TINYINT(1) UNSIGNED, IN `SimulationLength` SMALLINT UNSIGNED, IN `SimulationLengthVariance` FLOAT UNSIGNED, IN `BossCount` TINYINT UNSIGNED, IN `SimulationName` VARCHAR(255), IN `TMIWindow` TINYINT UNSIGNED, IN `TMIBoss` VARCHAR(4), IN `IsHidden` TINYINT(1) UNSIGNED, IN `CustomProfile` MEDIUMTEXT, IN `SimulationCraftVersion` VARCHAR(4))  NO SQL
BEGIN

	SET @SimulationId = 0;

	SET @ExitOnError = 0;

	IF UserId = '' THEN
		CALL raise(1356, 'Parameter `UserId` is required by SPROC SimulationInsert');
		SET @ExitOnError = 1;
	END IF;

	IF Iterations = '' THEN
		SET Iterations = 5000;
	END IF;

	IF SimulationLength = '' THEN
		SET SimulationLength = 450;
	END IF;

	IF SimulationLengthVariance > 1 THEN
		SET SimulationLengthVariance = 0.2;
	END IF;

	IF SimulationTypeId = '' THEN
		SET SimulationTypeId = 1;
	END IF;

	IF BossCount = 0 THEN
		SET BossCount = 1;
	END IF;
    
    IF SimulationCraftVersion = '' THEN
    	SET SimulationCraftVersion = 'live';
    END IF;
    
    SET @GameVersion = '7.0.3';
    
    IF SimulationCraftVersion = 'beta' THEN
    	SET @GameVersion = '7.0.3';
    END IF;
    
    
    
    IF @ExitOnError = 0 THEN
    	        
        SET @SimGUID = (SELECT UUID());
        
    	INSERT INTO	Simulations (UserId, SimulationName, SimulationGUID, Iterations, SimulationTypeId, BossCount, ScaleFactors, SimulationLength, SimulationLengthVariance, TMIWindow, TMIBoss, IsHidden, CustomProfile, SimulationCraftVersion, GameVersion)
        VALUES (UserId, SimulationName, @SimGUID, Iterations, SimulationTypeId, BossCount, ScaleFactors, SimulationLength, SimulationLengthVariance, TMIWindow, TMIBoss, IsHidden, CustomProfile, SimulationCraftVersion, @GameVersion);
        
    	SET @SimulationId = LAST_INSERT_ID();
        
        SET @SimulationLogId = 0;
        
        CALL SimulationLogInsert(@SimulationId, 1, null, @SimulationLogId); 
    	    
    END IF;
    
    SELECT @SimulationId as SimulationId;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SimulationList` (IN `UserId` INT UNSIGNED, IN `CharacterId` INT UNSIGNED, IN `SimulationStatusId` TINYINT UNSIGNED, IN `SimulationTypeId` TINYINT UNSIGNED, IN `QueuedAfterTimestamp` TIMESTAMP, IN `QueuedBeforeTimestamp` TIMESTAMP, IN `CompletedAfterTimestamp` TIMESTAMP, IN `CompletedBeforeTimestamp` TIMESTAMP, IN `HowManyRows` INT UNSIGNED, IN `ClassId` TINYINT UNSIGNED, IN `SpecializationId` SMALLINT UNSIGNED, IN `ScaleFactors` VARCHAR(5), IN `ReportArchived` VARCHAR(5), IN `IterationsLow` MEDIUMINT UNSIGNED, IN `IterationsHigh` MEDIUMINT UNSIGNED, IN `ServerId` MEDIUMINT UNSIGNED, IN `ItemLevelLow` SMALLINT UNSIGNED, IN `ItemLevelHigh` SMALLINT UNSIGNED, IN `BossCountLow` TINYINT UNSIGNED, IN `BossCountHigh` TINYINT UNSIGNED, IN `IsHiddenReport` TINYINT(1) UNSIGNED, IN `SimulationArchived` TINYINT(1) UNSIGNED)  NO SQL
BEGIN
#	SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED;

	IF UserId = '' THEN
    
		SET UserId = null;
        
	END IF;

	IF CharacterId = '' THEN
    
		SET CharacterId = null;
        
	END IF;

	IF SimulationStatusId > 6 THEN
    
		SET SimulationStatusId = null;
    
    ELSEIF SimulationStatusId = '' THEN
    	
        SET SimulationStatusId = null;
    
	END IF;

	IF SimulationTypeId = '' THEN
    
		SET SimulationTypeId = null;
        
	END IF;

	IF QueuedAfterTimestamp = '' THEN
    
		SET QueuedAfterTimestamp = null;
        
	END IF;

	IF QueuedBeforeTimestamp = '' THEN
    
		SET QueuedBeforeTimestamp = null;
        
	END IF;

	IF CompletedAfterTimestamp = '' THEN
    
		SET CompletedAfterTimestamp = null;
        
	END IF;

	IF CompletedBeforeTimestamp = '' THEN
    
		SET CompletedBeforeTimestamp = null;
        
	END IF;

	IF ClassId = '' THEN
    
		SET ClassId = null;
        
	END IF;

	IF SpecializationId = '' THEN
    
		SET SpecializationId = null;
        
	END IF;

	IF ScaleFactors = '' THEN
    
		SET ScaleFactors = null;
        
	END IF;

	IF ReportArchived = '' THEN
    
		SET ReportArchived = null;
        
	END IF;

	IF IterationsLow = '' THEN
    
		SET IterationsLow = null;
        
	END IF;

	IF IterationsHigh = '' THEN
    
		SET IterationsHigh = null;
        
	END IF;

	IF ServerId = '' THEN
    
		SET ServerId = null;
        
	END IF;

	IF ItemLevelLow = '' THEN
    
		SET ItemLevelLow = null;
        
	END IF;

	IF ItemLevelHigh = '' THEN
    
		SET ItemLevelHigh = null;
        
	END IF;

	IF BossCountLow = '' THEN
    
		SET BossCountLow = null;
        
	END IF;

	IF BossCountHigh = '' THEN
    
		SET BossCountHigh = null;
        
	END IF;
    
	SET @HasRecords = 1;

	IF @HasRecords > 0 THEN
       
       SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED ;
		select `u`.`UserId` AS `UserId`, 
			`u`.`Email` AS `Email`, 
			`sim`.`SimulationId` AS `SimulationId`, 
			`sim`.`SimulationName` AS `SimulationName`, 
			`sim`.`SimulationGUID` AS `SimulationGUID`, 
			(select count(0) from (`beotorch`.`SimulationActors` `sa1` join `beotorch`.`Simulations` `sim1` on(( `sim1`.`SimulationId` = `sa1`.`SimulationId` ))) where ( `sim1`.`SimulationId` = `sim`.`SimulationId` )) AS `ActorCount`, 
			`sim`.`BossCount` AS `BossCount`, 
			`sim`.`Iterations` AS `Iterations`, 
			`sim`.`SimulationLength` AS `SimulationLength`, 
			`sim`.`SimulationLengthVariance` AS `SimulationLengthVariance`, 
			`sim`.`TimeQueued` AS `TimeQueued`, 
			`sim`.`TimeCompleted` AS `TimeCompleted`, 
			`st`.`SimulationTypeId` AS `SimulationTypeId`, 
			`st`.`SimulationTypeSystemName` AS `SimulationTypeSystemName`, 
			`st`.`SimulationTypeFriendlyName` AS `SimulationTypeFriendlyName`, 
			`st`.`SimulationTypeDescription` AS `SimulationTypeDescription`, 
			`sl`.`SimulationLogId` AS `SimulationLogId`, 
			`sl`.`SimulationStatusId` AS `SimulationStatusId`, 
			`ss`.`StatusName` AS `StatusName`, 
			`ss`.`StatusDescription` AS `StatusDescription`, 
			`sl`.`SimulationLogTime` AS `SimulationLogTime`, 
			`sim`.`TMIWindow` AS `TMIWindow`, 
			`sim`.`TMIBoss` AS `TMIBoss`, 
			`sim`.`ScaleFactors` AS `ScaleFactors`, 
			`sim`.`ReportArchived` AS `ReportArchived`, 
			`sim`.`TimeArchived` AS `TimeArchived`, 
			`sim`.`IsHidden` AS `IsHidden`, 
			`sim`.`CustomProfile` AS `CustomProfile`, 
			`sim`.`SimulationCraftVersion` AS `SimulationCraftVersion`, 
			`sim`.`GameVersion` AS `GameVersion`, 
			`sim`.`SimulationArchived` AS `SimulationArchived`, 
			`sim`.`SimulationTimeArchived` AS `SimulationTimeArchived`,
			`sa`.`SimulationActorId` AS `SimulationActorId`,
			`c`.`CharacterId` AS `CharacterId`,
			`c`.`CharacterName` AS `CharacterName`,
			`sim`.`BossCount` AS `BossCount`,
            `sa`.`Level` AS `Level`,
            `sa`.`ItemLevel` AS `ItemLevel`,
            `sa`.`CalcTalent` AS `CalcTalent`,
            `sa`.`SimTalent` AS `SimTalent`,
            `sa`.`Talent1Id` AS `Talent1Id`,
            `tal1`.`TalentName` AS `Talent1Name`,
            `tal1`.`TalentIcon` AS `Talent1Icon`,
            `tal1`.`SpellId` AS `Talent1SpellId`,
            `tal1`.`TalentRow` AS `Talent1Row`,
            `tal1`.`TalentColumn` AS `Talent1Column`,
            `sa`.`Talent2Id` AS `Talent2Id`,
            `tal2`.`TalentName` AS `Talent2Name`,
            `tal2`.`TalentIcon` AS `Talent2Icon`,
            `tal2`.`SpellId` AS `Talent2SpellId`,
            `tal2`.`TalentRow` AS `Talent2Row`,
            `tal2`.`TalentColumn` AS `Talent2Column`,
            `sa`.`Talent3Id` AS `Talent3Id`,
            `tal3`.`TalentName` AS `Talent3Name`,
            `tal3`.`TalentIcon` AS `Talent3Icon`,
            `tal3`.`SpellId` AS `Talent3SpellId`,
            `tal3`.`TalentRow` AS `Talent3Row`,
            `tal3`.`TalentColumn` AS `Talent3Column`,
            `sa`.`Talent4Id` AS `Talent4Id`,
            `tal4`.`TalentName` AS `Talent4Name`,
            `tal4`.`TalentIcon` AS `Talent4Icon`,
            `tal4`.`SpellId` AS `Talent4SpellId`,
            `tal4`.`TalentRow` AS `Talent4Row`,
            `tal4`.`TalentColumn` AS `Talent4Column`,
            `sa`.`Talent5Id` AS `Talent5Id`,
            `tal5`.`TalentName` AS `Talent5Name`,
            `tal5`.`TalentIcon` AS `Talent5Icon`,
            `tal5`.`SpellId` AS `Talent5SpellId`,
            `tal5`.`TalentRow` AS `Talent5Row`,
            `tal5`.`TalentColumn` AS `Talent5Column`,
            `sa`.`Talent6Id` AS `Talent6Id`,
            `tal6`.`TalentName` AS `Talent6Name`,
            `tal6`.`TalentIcon` AS `Talent6Icon`,
            `tal6`.`SpellId` AS `Talent6SpellId`,
            `tal6`.`TalentRow` AS `Talent6Row`,
            `tal6`.`TalentColumn` AS `Talent6Column`,
            `sa`.`Talent7Id` AS `Talent7Id`,
            `tal7`.`TalentName` AS `Talent7Name`,
            `tal7`.`TalentIcon` AS `Talent7Icon`,
            `tal7`.`SpellId` AS `Talent7SpellId`,
            `tal7`.`TalentRow` AS `Talent7Row`,
            `tal7`.`TalentColumn` AS `Talent7Column`,
            `spec`.`CalcSpec` AS `CalcSpec`,
            `cls`.`CalcClass` AS `CalcClass`,
            `sa`.`CalcGlyph` AS `CalcGlyph`,
            `sa`.`Gender` AS `Gender`,
			(case `sa`.`Gender` 
				when 0 then 'Male' 
				else 'Female' 
			end) AS `GenderName`,
			`sa`.`ThumbnailURL` AS `ThumbnailURL`,
			`svr`.`ServerId` AS `ServerId`,
			`svr`.`ServerName` AS `ServerName`,
			`svr`.`ServerType` AS `ServerType`,
			`rcs`.`RaceId` AS `RaceId`,
			`rcs`.`RaceName` AS `RaceName`,
			`rcs`.`Faction` AS `Faction`,
			(case `rcs`.`Faction` 
				when 0 then 'Alliance' 
				when 1 then 'Horde' 
				when 2 then 'Neutral' 
				else '(Unknown)' 
			end) AS `FactionName`,
			(case `rcs`.`Faction` 
				when 0 then '0078FF' 
				when 1 then 'B30000' 
				when 2 then 'Neutral' 
				else '555555' 
			end) AS `FactionColor`,
			`cls`.`ClassId` AS `ClassId`,
			`cls`.`ClassName` AS `ClassName`,
			`cls`.`ClassColor` AS `ClassColor`,
			`sa`.`ArmorySpec` AS `ArmorySpec`,
			`spec`.`SpecializationId` AS `SpecializationId`,
			`spec`.`SpecializationName` AS `SpecializationName`,
			`spec`.`SpecializationPosition` AS `SpecializationPosition`,
			`spec`.`SpecializationRole` AS `SpecializationRole`,
			`spec`.`SpecializationPrimaryStat` AS `SpecializationPrimaryStat`,
			`sa`.`SimulationRole` AS `SimulationRole`,
			`r`.`RegionId` AS `RegionId`,
			`r`.`RegionName` AS `RegionName`,
			`r`.`RegionURL` AS `RegionURL`,
			`r`.`RegionPrefix` AS `RegionPrefix`,
			`r`.`RegionAPIUrl` AS `RegionAPIUrl`,
			`r`.`RegionThumbnailURL` AS `RegionThumbnailURL`,
			MAX(`sa`.`DPS`) AS `DPS`,
			MAX(`sa`.`TMI`) AS `TMI`,
			MAX(`sa`.`DTPS`) AS `DTPS`,
			MAX(`sa`.`HPS`) AS `HPS`,
			MAX(`sa`.`APS`) AS `APS`,
			`sim`.`TMIWindow` AS `TMIWindow`,
			`sim`.`TMIBoss` AS `TMIBoss`,
			`sim`.`ScaleFactors` AS `ScaleFactors`,
			`sim`.`ReportArchived` AS `ReportArchived`,
			`sim`.`TimeArchived` AS `TimeArchived`,
			`sim`.`IsHidden` AS `IsHidden`,
			`sa`.`IsCustomActor` AS `IsCustomActor`,
			`sa`.`CustomActorName` AS `CustomActorName`,
			`sim`.`SimulationCraftVersion` AS `SimulationCraftVersion`,
			`sim`.`GameVersion` AS `GameVersion`,
			`sim`.`SimulationArchived` AS `SimulationArchived`,
			`sim`.`SimulationTimeArchived` AS `SimulationTimeArchived`
            #,
			#`q`.QueuePosition,
            #(SELECT COUNT(*) FROM vSimulationQueue) as TotalQueue
		from `beotorch`.`Simulations` `sim` 
		join `beotorch`.`Users` `u` on `sim`.`UserId` = `u`.`UserId`
		join `beotorch`.`SimulationTypes` `st` on `sim`.`SimulationTypeId` = `st`.`SimulationTypeId`
		join `beotorch`.`SimulationLog` `sl` on `sim`.`SimulationId` = `sl`.`SimulationId`
		join `beotorch`.`SimulationStatus` `ss` on `sl`.`SimulationStatusId` = `ss`.`SimulationStatusId`
		join `beotorch`.`SimulationActors` `sa` on `sim`.`SimulationId` = `sa`.`SimulationId`
		#left join `beotorch`.`vSimulationQueue` as `q` on `q`.SimulationId = `sim`.`SimulationId`
		left join `beotorch`.`Characters` `c` on `sa`.`CharacterId` = `c`.`CharacterId`
		left join `beotorch`.`Servers` `svr` on `c`.`ServerId` = `svr`.`ServerId`
		left join `beotorch`.`Regions` `r` on `svr`.`RegionId` = `r`.`RegionId`
		join `beotorch`.`Classes` `cls` on `sa`.`ClassId` = `cls`.`ClassId` join `beotorch`.`Specializations` `spec` on `sa`.`SpecializationId` = `spec`.`SpecializationId`
		left join `beotorch`.`Races` `rcs` on `sa`.`RaceId` = `rcs`.`RaceId`
        left join `beotorch`.`Talents` `tal1` on `sa`.`Talent1Id` = `tal1`.`TalentId`
        left join `beotorch`.`Talents` `tal2` on `sa`.`Talent2Id` = `tal2`.`TalentId`
        left join `beotorch`.`Talents` `tal3` on `sa`.`Talent3Id` = `tal3`.`TalentId`
        left join `beotorch`.`Talents` `tal4` on `sa`.`Talent4Id` = `tal4`.`TalentId`
        left join `beotorch`.`Talents` `tal5` on `sa`.`Talent5Id` = `tal5`.`TalentId`
        left join `beotorch`.`Talents` `tal6` on `sa`.`Talent6Id` = `tal6`.`TalentId`
        left join `beotorch`.`Talents` `tal7` on `sa`.`Talent7Id` = `tal7`.`TalentId`
        WHERE	sl.IsArchive = 0
        AND		CASE WHEN UserId Is Null THEN TRUE ELSE u.UserId = UserId END
		AND     CASE WHEN SimulationStatusId Is Null THEN TRUE ELSE SimulationStatusId = sl.SimulationStatusId END
		AND		CASE WHEN SimulationTypeId Is Null THEN TRUE ELSE st.SimulationTypeId = SimulationTypeId END
		AND		CASE WHEN ScaleFactors = "yes" THEN sim.ScaleFactors = 1 WHEN ScaleFactors = "no" THEN sim.ScaleFactors = 0 ELSE TRUE END
		AND		CASE WHEN ReportArchived = "yes" THEN sim.ReportArchived = 0 WHEN ReportArchived = "no" THEN sim.ReportArchived = 1 ELSE TRUE END
        AND		CASE WHEN IterationsLow Is Null THEN TRUE ELSE sim.Iterations >= IterationsLow END
        AND		CASE WHEN IterationsHigh Is Null THEN TRUE ELSE sim.Iterations <= IterationsHigh END
        AND		CASE WHEN BossCountLow Is Null THEN TRUE ELSE sim.BossCount >= BossCountLow END
        AND		CASE WHEN BossCountHigh Is Null THEN TRUE ELSE sim.BossCount <= BossCountHigh END
		AND		CASE WHEN QueuedAfterTimestamp Is Null THEN TRUE ELSE sim.TimeQueued >= QueuedAfterTimestamp END
		AND		CASE WHEN QueuedBeforeTimestamp Is Null THEN TRUE ELSE sim.TimeQueued <= QueuedBeforeTimestamp END
		AND		CASE WHEN CompletedAfterTimestamp Is Null THEN TRUE ELSE sim.TimeCompleted >= CompletedAfterTimestamp END
		AND		CASE WHEN CompletedBeforeTimestamp Is Null THEN TRUE ELSE sim.TimeCompleted >= CompletedBeforeTimestamp END
		AND		CASE WHEN CharacterId Is Null THEN TRUE ELSE sa.CharacterId = CharacterId END
        AND		CASE WHEN ClassId Is Null THEN TRUE ELSE sa.ClassId = ClassId END
		AND		CASE WHEN SpecializationId Is Null THEN TRUE ELSE sa.SpecializationId = SpecializationId END
        AND		CASE WHEN ItemLevelLow Is Null THEN TRUE ELSE sa.ItemLevel >= ItemLevelLow END
        AND		CASE WHEN ItemLevelHigh Is Null THEN TRUE ELSE sa.ItemLevel <= ItemLevelHigh END
        AND		CASE WHEN ServerId Is Null THEN TRUE ELSE svr.ServerId = ServerId END
        AND		CASE WHEN IsHiddenReport = 0 THEN sim.IsHidden = 0 WHEN IsHiddenReport = 1 THEN sim.IsHidden = 1 ELSE TRUE END
        AND		CASE WHEN SimulationArchived = 0 THEN sim.SimulationArchived = 0 WHEN SimulationArchived = 1 THEN sim.SimulationArchived = 1 ELSE TRUE END
		GROUP BY sim.SimulationId
		order by `sl`.`SimulationLogId` desc 
        LIMIT	HowManyRows
;
		COMMIT ;


	END IF;
#	SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SimulationList2` (IN `UserId` INT UNSIGNED, IN `CharacterId` INT UNSIGNED, IN `SimulationStatusId` TINYINT UNSIGNED, IN `SimulationTypeId` TINYINT UNSIGNED, IN `QueuedAfterTimestamp` TIMESTAMP, IN `QueuedBeforeTimestamp` TIMESTAMP, IN `CompletedAfterTimestamp` TIMESTAMP, IN `CompletedBeforeTimestamp` TIMESTAMP, IN `HowManyRows` INT UNSIGNED, IN `ClassId` TINYINT UNSIGNED, IN `SpecializationId` SMALLINT UNSIGNED, IN `ScaleFactors` VARCHAR(5), IN `ReportArchived` VARCHAR(5), IN `IterationsLow` MEDIUMINT UNSIGNED, IN `IterationsHigh` MEDIUMINT UNSIGNED, IN `ServerId` MEDIUMINT UNSIGNED, IN `ItemLevelLow` SMALLINT UNSIGNED, IN `ItemLevelHigh` SMALLINT UNSIGNED, IN `BossCountLow` TINYINT UNSIGNED, IN `BossCountHigh` TINYINT UNSIGNED, IN `IsHiddenReport` TINYINT(1) UNSIGNED, IN `SimulationArchived` TINYINT(1) UNSIGNED)  NO SQL
BEGIN

	IF UserId = '' THEN
    
		SET UserId = null;
        
	END IF;

	IF CharacterId = '' THEN
    
		SET CharacterId = null;
        
	END IF;

	IF SimulationStatusId > 6 THEN
    
		SET SimulationStatusId = null;
    
    ELSEIF SimulationStatusId = '' THEN
    	
        SET SimulationStatusId = null;
    
	END IF;

	IF SimulationTypeId = '' THEN
    
		SET SimulationTypeId = null;
        
	END IF;

	IF QueuedAfterTimestamp = '' THEN
    
		SET QueuedAfterTimestamp = null;
        
	END IF;

	IF QueuedBeforeTimestamp = '' THEN
    
		SET QueuedBeforeTimestamp = null;
        
	END IF;

	IF CompletedAfterTimestamp = '' THEN
    
		SET CompletedAfterTimestamp = null;
        
	END IF;

	IF CompletedBeforeTimestamp = '' THEN
    
		SET CompletedBeforeTimestamp = null;
        
	END IF;

	IF ClassId = '' THEN
    
		SET ClassId = null;
        
	END IF;

	IF SpecializationId = '' THEN
    
		SET SpecializationId = null;
        
	END IF;

	IF ScaleFactors = '' THEN
    
		SET ScaleFactors = null;
        
	END IF;

	IF ReportArchived = '' THEN
    
		SET ReportArchived = null;
        
	END IF;

	IF IterationsLow = '' THEN
    
		SET IterationsLow = null;
        
	END IF;

	IF IterationsHigh = '' THEN
    
		SET IterationsHigh = null;
        
	END IF;

	IF ServerId = '' THEN
    
		SET ServerId = null;
        
	END IF;

	IF ItemLevelLow = '' THEN
    
		SET ItemLevelLow = null;
        
	END IF;

	IF ItemLevelHigh = '' THEN
    
		SET ItemLevelHigh = null;
        
	END IF;

	IF BossCountLow = '' THEN
    
		SET BossCountLow = null;
        
	END IF;

	IF BossCountHigh = '' THEN
    
		SET BossCountHigh = null;
        
	END IF;
    
	SET @HasRecords = 1;

	IF @HasRecords > 0 THEN
       
        CREATE TEMPORARY TABLE `tmpSimulations` ENGINE=InnoDB as (
            SELECT 	*
            FROM	Simulations as sim
            WHERE	CASE WHEN UserId Is Null THEN TRUE ELSE u.UserId = UserId END
            AND		CASE WHEN IsHiddenReport = 0 THEN sim.IsHidden = 0 WHEN IsHiddenReport = 1 THEN sim.IsHidden = 1 ELSE TRUE END
            AND		CASE WHEN SimulationArchived = 0 THEN sim.SimulationArchived = 0 WHEN SimulationArchived = 1 THEN sim.SimulationArchived = 1 ELSE TRUE END
            AND		CASE WHEN ScaleFactors = "yes" THEN sim.ScaleFactors = 1 WHEN ScaleFactors = "no" THEN sim.ScaleFactors = 0 ELSE TRUE END
            AND		CASE WHEN ReportArchived = "yes" THEN sim.ReportArchived = 0 WHEN ReportArchived = "no" THEN sim.ReportArchived = 1 ELSE TRUE END
            AND		CASE WHEN IterationsLow Is Null THEN TRUE ELSE sim.Iterations >= IterationsLow END
            AND		CASE WHEN IterationsHigh Is Null THEN TRUE ELSE sim.Iterations <= IterationsHigh END
            AND		CASE WHEN BossCountLow Is Null THEN TRUE ELSE sim.BossCount >= BossCountLow END
            AND		CASE WHEN BossCountHigh Is Null THEN TRUE ELSE sim.BossCount <= BossCountHigh END
            AND		CASE WHEN QueuedAfterTimestamp Is Null THEN TRUE ELSE sim.TimeQueued >= QueuedAfterTimestamp END
            AND		CASE WHEN QueuedBeforeTimestamp Is Null THEN TRUE ELSE sim.TimeQueued <= QueuedBeforeTimestamp END
            AND		CASE WHEN CompletedAfterTimestamp Is Null THEN TRUE ELSE sim.TimeCompleted >= CompletedAfterTimestamp END
            AND		CASE WHEN CompletedBeforeTimestamp Is Null THEN TRUE ELSE sim.TimeCompleted >= CompletedBeforeTimestamp END        
        );
       
		select `u`.`UserId` AS `UserId`, 
			`u`.`Email` AS `Email`, 
			`sim`.`SimulationId` AS `SimulationId`, 
			`sim`.`SimulationName` AS `SimulationName`, 
			`sim`.`SimulationGUID` AS `SimulationGUID`, 
			(select count(0) from (`beotorch`.`SimulationActors` `sa1` join `beotorch`.`Simulations` `sim1` on(( `sim1`.`SimulationId` = `sa1`.`SimulationId` ))) where ( `sim1`.`SimulationId` = `sim`.`SimulationId` )) AS `ActorCount`, 
			`sim`.`BossCount` AS `BossCount`, 
			`sim`.`Iterations` AS `Iterations`, 
			`sim`.`SimulationLength` AS `SimulationLength`, 
			`sim`.`SimulationLengthVariance` AS `SimulationLengthVariance`, 
			`sim`.`TimeQueued` AS `TimeQueued`, 
			`sim`.`TimeCompleted` AS `TimeCompleted`, 
			`st`.`SimulationTypeId` AS `SimulationTypeId`, 
			`st`.`SimulationTypeSystemName` AS `SimulationTypeSystemName`, 
			`st`.`SimulationTypeFriendlyName` AS `SimulationTypeFriendlyName`, 
			`st`.`SimulationTypeDescription` AS `SimulationTypeDescription`, 
			`sl`.`SimulationLogId` AS `SimulationLogId`, 
			`sl`.`SimulationStatusId` AS `SimulationStatusId`, 
			`ss`.`StatusName` AS `StatusName`, 
			`ss`.`StatusDescription` AS `StatusDescription`, 
			`sl`.`SimulationLogTime` AS `SimulationLogTime`, 
			`sim`.`TMIWindow` AS `TMIWindow`, 
			`sim`.`TMIBoss` AS `TMIBoss`, 
			`sim`.`ScaleFactors` AS `ScaleFactors`, 
			`sim`.`ReportArchived` AS `ReportArchived`, 
			`sim`.`TimeArchived` AS `TimeArchived`, 
			`sim`.`IsHidden` AS `IsHidden`, 
			`sim`.`CustomProfile` AS `CustomProfile`, 
			`sim`.`SimulationCraftVersion` AS `SimulationCraftVersion`, 
			`sim`.`GameVersion` AS `GameVersion`, 
			`sim`.`SimulationArchived` AS `SimulationArchived`, 
			`sim`.`SimulationTimeArchived` AS `SimulationTimeArchived`,
			`sa`.`SimulationActorId` AS `SimulationActorId`,
			`c`.`CharacterId` AS `CharacterId`,
			`c`.`CharacterName` AS `CharacterName`,
			`sim`.`BossCount` AS `BossCount`,
            `sa`.`Level` AS `Level`,
            `sa`.`ItemLevel` AS `ItemLevel`,
            `sa`.`CalcTalent` AS `CalcTalent`,
            `sa`.`SimTalent` AS `SimTalent`,
            `sa`.`Talent1Id` AS `Talent1Id`,
            `tal1`.`TalentName` AS `Talent1Name`,
            `tal1`.`TalentIcon` AS `Talent1Icon`,
            `tal1`.`SpellId` AS `Talent1SpellId`,
            `tal1`.`TalentRow` AS `Talent1Row`,
            `tal1`.`TalentColumn` AS `Talent1Column`,
            `sa`.`Talent2Id` AS `Talent2Id`,
            `tal2`.`TalentName` AS `Talent2Name`,
            `tal2`.`TalentIcon` AS `Talent2Icon`,
            `tal2`.`SpellId` AS `Talent2SpellId`,
            `tal2`.`TalentRow` AS `Talent2Row`,
            `tal2`.`TalentColumn` AS `Talent2Column`,
            `sa`.`Talent3Id` AS `Talent3Id`,
            `tal3`.`TalentName` AS `Talent3Name`,
            `tal3`.`TalentIcon` AS `Talent3Icon`,
            `tal3`.`SpellId` AS `Talent3SpellId`,
            `tal3`.`TalentRow` AS `Talent3Row`,
            `tal3`.`TalentColumn` AS `Talent3Column`,
            `sa`.`Talent4Id` AS `Talent4Id`,
            `tal4`.`TalentName` AS `Talent4Name`,
            `tal4`.`TalentIcon` AS `Talent4Icon`,
            `tal4`.`SpellId` AS `Talent4SpellId`,
            `tal4`.`TalentRow` AS `Talent4Row`,
            `tal4`.`TalentColumn` AS `Talent4Column`,
            `sa`.`Talent5Id` AS `Talent5Id`,
            `tal5`.`TalentName` AS `Talent5Name`,
            `tal5`.`TalentIcon` AS `Talent5Icon`,
            `tal5`.`SpellId` AS `Talent5SpellId`,
            `tal5`.`TalentRow` AS `Talent5Row`,
            `tal5`.`TalentColumn` AS `Talent5Column`,
            `sa`.`Talent6Id` AS `Talent6Id`,
            `tal6`.`TalentName` AS `Talent6Name`,
            `tal6`.`TalentIcon` AS `Talent6Icon`,
            `tal6`.`SpellId` AS `Talent6SpellId`,
            `tal6`.`TalentRow` AS `Talent6Row`,
            `tal6`.`TalentColumn` AS `Talent6Column`,
            `sa`.`Talent7Id` AS `Talent7Id`,
            `tal7`.`TalentName` AS `Talent7Name`,
            `tal7`.`TalentIcon` AS `Talent7Icon`,
            `tal7`.`SpellId` AS `Talent7SpellId`,
            `tal7`.`TalentRow` AS `Talent7Row`,
            `tal7`.`TalentColumn` AS `Talent7Column`,
            `spec`.`CalcSpec` AS `CalcSpec`,
            `cls`.`CalcClass` AS `CalcClass`,
            `sa`.`CalcGlyph` AS `CalcGlyph`,
            `sa`.`Gender` AS `Gender`,
			(case `sa`.`Gender` 
				when 0 then 'Male' 
				else 'Female' 
			end) AS `GenderName`,
			`sa`.`ThumbnailURL` AS `ThumbnailURL`,
			`svr`.`ServerId` AS `ServerId`,
			`svr`.`ServerName` AS `ServerName`,
			`svr`.`ServerType` AS `ServerType`,
			`rcs`.`RaceId` AS `RaceId`,
			`rcs`.`RaceName` AS `RaceName`,
			`rcs`.`Faction` AS `Faction`,
			(case `rcs`.`Faction` 
				when 0 then 'Alliance' 
				when 1 then 'Horde' 
				when 2 then 'Neutral' 
				else '(Unknown)' 
			end) AS `FactionName`,
			(case `rcs`.`Faction` 
				when 0 then '0078FF' 
				when 1 then 'B30000' 
				when 2 then 'Neutral' 
				else '555555' 
			end) AS `FactionColor`,
			`cls`.`ClassId` AS `ClassId`,
			`cls`.`ClassName` AS `ClassName`,
			`cls`.`ClassColor` AS `ClassColor`,
			`sa`.`ArmorySpec` AS `ArmorySpec`,
			`spec`.`SpecializationId` AS `SpecializationId`,
			`spec`.`SpecializationName` AS `SpecializationName`,
			`spec`.`SpecializationPosition` AS `SpecializationPosition`,
			`spec`.`SpecializationRole` AS `SpecializationRole`,
			`spec`.`SpecializationPrimaryStat` AS `SpecializationPrimaryStat`,
			`sa`.`SimulationRole` AS `SimulationRole`,
			`r`.`RegionId` AS `RegionId`,
			`r`.`RegionName` AS `RegionName`,
			`r`.`RegionURL` AS `RegionURL`,
			`r`.`RegionPrefix` AS `RegionPrefix`,
			`r`.`RegionAPIUrl` AS `RegionAPIUrl`,
			`r`.`RegionThumbnailURL` AS `RegionThumbnailURL`,
			MAX(`sa`.`DPS`) AS `DPS`,
			MAX(`sa`.`TMI`) AS `TMI`,
			MAX(`sa`.`DTPS`) AS `DTPS`,
			MAX(`sa`.`HPS`) AS `HPS`,
			MAX(`sa`.`APS`) AS `APS`,
			`sim`.`TMIWindow` AS `TMIWindow`,
			`sim`.`TMIBoss` AS `TMIBoss`,
			`sim`.`ScaleFactors` AS `ScaleFactors`,
			`sim`.`ReportArchived` AS `ReportArchived`,
			`sim`.`TimeArchived` AS `TimeArchived`,
			`sim`.`IsHidden` AS `IsHidden`,
			`sa`.`IsCustomActor` AS `IsCustomActor`,
			`sa`.`CustomActorName` AS `CustomActorName`,
			`sim`.`SimulationCraftVersion` AS `SimulationCraftVersion`,
			`sim`.`GameVersion` AS `GameVersion`,
			`sim`.`SimulationArchived` AS `SimulationArchived`,
			`sim`.`SimulationTimeArchived` AS `SimulationTimeArchived`,
			`q`.QueuePosition,
            (SELECT COUNT(*) FROM vSimulationQueue) as TotalQueue
		from tmpSimulations `sim` 
		join `beotorch`.`Users` `u` on `sim`.`UserId` = `u`.`UserId`
		join `beotorch`.`SimulationTypes` `st` on `sim`.`SimulationTypeId` = `st`.`SimulationTypeId`
		join `beotorch`.`SimulationLog` `sl` on `sim`.`SimulationId` = `sl`.`SimulationId`
		join `beotorch`.`SimulationStatus` `ss` on `sl`.`SimulationStatusId` = `ss`.`SimulationStatusId`
		join `beotorch`.`SimulationActors` `sa` on `sim`.`SimulationId` = `sa`.`SimulationId`
		left join `beotorch`.`vSimulationQueue` as `q` on `q`.SimulationId = `sim`.`SimulationId`
		left join `beotorch`.`Characters` `c` on `sa`.`CharacterId` = `c`.`CharacterId`
		left join `beotorch`.`Servers` `svr` on `c`.`ServerId` = `svr`.`ServerId`
		left join `beotorch`.`Regions` `r` on `svr`.`RegionId` = `r`.`RegionId`
		join `beotorch`.`Classes` `cls` on `sa`.`ClassId` = `cls`.`ClassId` join `beotorch`.`Specializations` `spec` on `sa`.`SpecializationId` = `spec`.`SpecializationId`
		left join `beotorch`.`Races` `rcs` on `sa`.`RaceId` = `rcs`.`RaceId`
        left join `beotorch`.`Talents` `tal1` on `sa`.`Talent1Id` = `tal1`.`TalentId`
        left join `beotorch`.`Talents` `tal2` on `sa`.`Talent2Id` = `tal2`.`TalentId`
        left join `beotorch`.`Talents` `tal3` on `sa`.`Talent3Id` = `tal3`.`TalentId`
        left join `beotorch`.`Talents` `tal4` on `sa`.`Talent4Id` = `tal4`.`TalentId`
        left join `beotorch`.`Talents` `tal5` on `sa`.`Talent5Id` = `tal5`.`TalentId`
        left join `beotorch`.`Talents` `tal6` on `sa`.`Talent6Id` = `tal6`.`TalentId`
        left join `beotorch`.`Talents` `tal7` on `sa`.`Talent7Id` = `tal7`.`TalentId`
        WHERE	sl.IsArchive = 0
		AND     CASE WHEN SimulationStatusId Is Null THEN TRUE ELSE SimulationStatusId = sl.SimulationStatusId END
		AND		CASE WHEN SimulationTypeId Is Null THEN TRUE ELSE st.SimulationTypeId = SimulationTypeId END
		AND		CASE WHEN CharacterId Is Null THEN TRUE ELSE sa.CharacterId = CharacterId END
        AND		CASE WHEN ClassId Is Null THEN TRUE ELSE sa.ClassId = ClassId END
		AND		CASE WHEN SpecializationId Is Null THEN TRUE ELSE sa.SpecializationId = SpecializationId END
        AND		CASE WHEN ItemLevelLow Is Null THEN TRUE ELSE sa.ItemLevel >= ItemLevelLow END
        AND		CASE WHEN ItemLevelHigh Is Null THEN TRUE ELSE sa.ItemLevel <= ItemLevelHigh END
        AND		CASE WHEN ServerId Is Null THEN TRUE ELSE svr.ServerId = ServerId END
		GROUP BY sim.SimulationId
		order by `sl`.`SimulationLogId` desc 
        LIMIT	HowManyRows
;

	END IF;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SimulationList_old` (IN `UserId` INT UNSIGNED, IN `CharacterId` INT UNSIGNED, IN `SimulationStatusId` TINYINT UNSIGNED, IN `SimulationTypeId` TINYINT UNSIGNED, IN `QueuedAfterTimestamp` TIMESTAMP, IN `QueuedBeforeTimestamp` TIMESTAMP, IN `CompletedAfterTimestamp` TIMESTAMP, IN `CompletedBeforeTimestamp` TIMESTAMP, IN `HowManyRows` INT UNSIGNED, IN `ClassId` TINYINT UNSIGNED, IN `SpecializationId` SMALLINT UNSIGNED, IN `ScaleFactors` VARCHAR(5), IN `ReportArchived` VARCHAR(5), IN `IterationsLow` MEDIUMINT UNSIGNED, IN `IterationsHigh` MEDIUMINT UNSIGNED, IN `ServerId` MEDIUMINT UNSIGNED, IN `ItemLevelLow` SMALLINT UNSIGNED, IN `ItemLevelHigh` SMALLINT UNSIGNED, IN `BossCountLow` TINYINT UNSIGNED, IN `BossCountHigh` TINYINT UNSIGNED, IN `IsHiddenReport` TINYINT(1) UNSIGNED, IN `SimulationArchived` TINYINT(1) UNSIGNED)  NO SQL
BEGIN

	IF UserId = '' THEN
    
		SET UserId = null;
        
	END IF;

	IF CharacterId = '' THEN
    
		SET CharacterId = null;
        
	END IF;

	IF SimulationStatusId > 6 THEN
    
		SET SimulationStatusId = null;
    
    ELSEIF SimulationStatusId = '' THEN
    	
        SET SimulationStatusId = null;
    
	END IF;

	IF SimulationTypeId = '' THEN
    
		SET SimulationTypeId = null;
        
	END IF;

	IF QueuedAfterTimestamp = '' THEN
    
		SET QueuedAfterTimestamp = null;
        
	END IF;

	IF QueuedBeforeTimestamp = '' THEN
    
		SET QueuedBeforeTimestamp = null;
        
	END IF;

	IF CompletedAfterTimestamp = '' THEN
    
		SET CompletedAfterTimestamp = null;
        
	END IF;

	IF CompletedBeforeTimestamp = '' THEN
    
		SET CompletedBeforeTimestamp = null;
        
	END IF;

	IF ClassId = '' THEN
    
		SET ClassId = null;
        
	END IF;

	IF SpecializationId = '' THEN
    
		SET SpecializationId = null;
        
	END IF;

	IF ScaleFactors = '' THEN
    
		SET ScaleFactors = null;
        
	END IF;

	IF ReportArchived = '' THEN
    
		SET ReportArchived = null;
        
	END IF;

	IF IterationsLow = '' THEN
    
		SET IterationsLow = null;
        
	END IF;

	IF IterationsHigh = '' THEN
    
		SET IterationsHigh = null;
        
	END IF;

	IF ServerId = '' THEN
    
		SET ServerId = null;
        
	END IF;

	IF ItemLevelLow = '' THEN
    
		SET ItemLevelLow = null;
        
	END IF;

	IF ItemLevelHigh = '' THEN
    
		SET ItemLevelHigh = null;
        
	END IF;

	IF BossCountLow = '' THEN
    
		SET BossCountLow = null;
        
	END IF;

	IF BossCountHigh = '' THEN
    
		SET BossCountHigh = null;
        
	END IF;
    
	SET @HasRecords = 1;

	IF @HasRecords > 0 THEN
       
        SELECT	vsd.*
                ,vsads.*
                ,vsq.QueuePosition
                ,(SELECT COUNT(*) FROM vSimulationQueue) as TotalQueue
        FROM		vSimulationsDetails as vsd
        LEFT JOIN	vHighestActors as vha on vha.SimulationId = vsd.SimulationId 
        LEFT JOIN	vSimulationActorDetailsShort as vsads on vha.SimulationActorId = vsads.SimulationActorId
        LEFT JOIN	vSimulationQueue as vsq on vsq.SimulationId = vsd.SimulationId
        WHERE	CASE WHEN UserId Is Null THEN TRUE ELSE vsd.UserId = UserId END
		AND     CASE WHEN SimulationStatusId Is Null THEN TRUE ELSE SimulationStatusId = vsd.SimulationStatusId END
		AND		CASE WHEN SimulationTypeId Is Null THEN TRUE ELSE vsd.SimulationTypeId = SimulationTypeId END
		AND		CASE WHEN ScaleFactors = "yes" THEN vsd.ScaleFactors = 1 WHEN ScaleFactors = "no" THEN vsd.ScaleFactors = 0 ELSE TRUE END
		AND		CASE WHEN ReportArchived = "yes" THEN vsd.ReportArchived = 0 WHEN ReportArchived = "no" THEN vsd.ReportArchived = 1 ELSE TRUE END
        AND		CASE WHEN IterationsLow Is Null THEN TRUE ELSE vsd.Iterations >= IterationsLow END
        AND		CASE WHEN IterationsHigh Is Null THEN TRUE ELSE vsd.Iterations <= IterationsHigh END
        AND		CASE WHEN BossCountLow Is Null THEN TRUE ELSE vsd.BossCount >= BossCountLow END
        AND		CASE WHEN BossCountHigh Is Null THEN TRUE ELSE vsd.BossCount <= BossCountHigh END
		AND		CASE WHEN QueuedAfterTimestamp Is Null THEN TRUE ELSE vsd.TimeQueued >= QueuedAfterTimestamp END
		AND		CASE WHEN QueuedBeforeTimestamp Is Null THEN TRUE ELSE vsd.TimeQueued <= QueuedBeforeTimestamp END
		AND		CASE WHEN CompletedAfterTimestamp Is Null THEN TRUE ELSE vsd.TimeCompleted >= CompletedAfterTimestamp END
		AND		CASE WHEN CompletedBeforeTimestamp Is Null THEN TRUE ELSE vsd.TimeCompleted >= CompletedBeforeTimestamp END
		AND		CASE WHEN CharacterId Is Null THEN TRUE ELSE vsads.CharacterId = CharacterId END
        AND		CASE WHEN ClassId Is Null THEN TRUE ELSE vsads.ClassId = ClassId END
		AND		CASE WHEN SpecializationId Is Null THEN TRUE ELSE vsads.SpecializationId = SpecializationId END
        AND		CASE WHEN ItemLevelLow Is Null THEN TRUE ELSE vsads.ItemLevel >= ItemLevelLow END
        AND		CASE WHEN ItemLevelHigh Is Null THEN TRUE ELSE vsads.ItemLevel <= ItemLevelHigh END
        AND		CASE WHEN ServerId Is Null THEN TRUE ELSE vsads.ServerId = ServerId END
        AND		CASE WHEN IsHiddenReport = 0 THEN vsd.IsHidden = 0 WHEN IsHiddenReport = 1 THEN vsd.IsHidden = 1 ELSE TRUE END#
        AND		CASE WHEN SimulationArchived = 0 THEN vsd.SimulationArchived = 0 WHEN SimulationArchived = 1 THEN vsd.SimulationArchived = 1 ELSE TRUE END
		ORDER BY	vsd.SimulationLogId DESC
        LIMIT	HowManyRows
;


	END IF;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SimulationLogInsert` (IN `SimulationId` INT UNSIGNED, IN `SimulationStatusId` TINYINT UNSIGNED, IN `ComputerId` INT UNSIGNED, OUT `SimulationLogId` INT UNSIGNED)  NO SQL
BEGIN

	IF SimulationId = '' THEN
		CALL raise(1356, 'Parameter `SimulationID` is required by SPROC SimulationLogInsert');
	END IF;

	IF SimulationStatusId = '' THEN
		CALL raise(1356, 'Parameter `SimulationStatusId` is required by SPROC SimulationLogInsert');
	END IF;

	IF ComputerId = '' THEN
	SET ComputerId = null;
	END IF;

	INSERT INTO SimulationLog (SimulationId, SimulationStatusId, ComputerId)
	VALUES (SimulationId, SimulationStatusId, ComputerId);
    
    SET SimulationLogId = LAST_INSERT_ID();
    
    UPDATE	SimulationLog sl
    SET		sl.IsArchive = 1
    WHERE	sl.SimulationId = SimulationId
    AND		sl.SimulationLogId <> SimulationLogId;
    
END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SimulationToggleHidden` (IN `SimulationGUID` VARCHAR(38), IN `UserId` INT UNSIGNED)  NO SQL
BEGIN

    SET @SimulationId = (
        SELECT	sim.SimulationId
        FROM	Simulations as sim
        WHERE	sim.SimulationGUID = SimulationGUID
        );
    
    SET @SimulationOwnerId = (
        SELECT	sim.UserId
        FROM	Simulations as sim
        WHERE	sim.SimulationId = @SimulationId
        );
    
    SET @CurrentVisible = (
        SELECT	sim.IsHidden
        FROM	Simulations as sim
        WHERE	sim.SimulationId = @SimulationId
        );
    
    SET @UserLevel = (
        SELECT	u.UserLevelId
        FROM	Users as u
        JOIN	UserLevels as ul on u.UserLevelId = ul.UserLevelId
        WHERE	u.UserId = UserId
        );
    
    IF @SimulationOwnerId = UserId OR @UserLevel = 9 THEN
    
    	UPDATE	Simulations as sim
        SET		sim.IsHidden = CASE
        	WHEN	@CurrentVisible = 0 THEN 1
            ELSE	0	END
        WHERE sim.SimulationId = @SimulationId;
    
        SELECT	sim.IsHidden as Response
    	FROM	Simulations as sim
        WHERE	sim.SimulationId = @SimulationId;
    
    ELSE
    
    	SELECT -1 as Response;
    
    END IF;
   
END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SimulationTypeList` ()  NO SQL
BEGIN

	SELECT	SimulationTypeId
    		,SimulationTypeFriendlyName
            ,SimulationTypeSystemName
            ,SimulationTypeDescription
    FROM	SimulationTypes
    ORDER BY	SimulationTypeId ASC;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `SiteStatistics` ()  NO SQL
BEGIN

	SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED ;
SELECT  DATE_FORMAT(x.ts, '%Y-%m-%d') as RecordDate
		,SUM(IF(sim.SimulationId Is Not Null, 1, 0)) as Total_Simulations
        ,SUM(IF(vsd.SimulationId Is Not Null, 1, 0)) as Total_Actors
        ,IFNULL(AVG(vsd.Iterations), 0) as Average_Iterations
		,SUM(IF(vsd.RegionId = 1, 1, 0)) as Region_US
		,SUM(IF(vsd.RegionId = 2, 1, 0)) as Region_EU
		,SUM(IF(vsd.RegionId = 3, 1, 0)) as Region_KR
		,SUM(IF(vsd.RegionId = 4, 1, 0)) as Region_TW
		,SUM(IF(vsd.RegionId = 5, 1, 0)) as Region_CN
		,SUM(IF(vsd.SimulationTypeId = 1, 1, 0)) as FightType_Patchwerk
		,SUM(IF(vsd.SimulationTypeId = 2, 1, 0)) as FightType_HelterSkelter
		,SUM(IF(vsd.SimulationTypeId = 3, 1, 0)) as FightType_LightMovement
		,SUM(IF(vsd.SimulationTypeId = 4, 1, 0)) as FightType_HeavyMovement
		,SUM(IF(vsd.SimulationTypeId = 5, 1, 0)) as FightType_Ultraxion
		,SUM(IF(vsd.SimulationTypeId = 6, 1, 0)) as FightType_HecticAddCleave
		,SUM(IF(vsd.SimulationTypeId = 7, 1, 0)) as FightType_Beastlord
		,SUM(IF(vsd.BossCount = 1, 1, 0)) as BossCount_1
		,SUM(IF(vsd.BossCount = 2, 1, 0)) as BossCount_2
		,SUM(IF(vsd.BossCount = 3, 1, 0)) as BossCount_3
		,SUM(IF(vsd.BossCount = 4, 1, 0)) as BossCount_4
		,SUM(IF(vsd.BossCount = 5, 1, 0)) as BossCount_5
		,SUM(IF(vsd.BossCount = 6, 1, 0)) as BossCount_6
		,SUM(IF(vsd.BossCount = 7, 1, 0)) as BossCount_7
		,SUM(IF(vsd.BossCount = 8, 1, 0)) as BossCount_8
		,SUM(IF(vsd.SimulationRole = 'DPS', 1, 0)) as Role_DPS
		,SUM(IF(vsd.SimulationRole = 'Tank', 1, 0)) as Role_Tank
		,SUM(IF(vsd.ClassId = 1, 1, 0)) as Class_Warrior
		,SUM(IF(vsd.ClassId = 2, 1, 0)) as Class_Paladin
		,SUM(IF(vsd.ClassId = 3, 1, 0)) as Class_Hunter
		,SUM(IF(vsd.ClassId = 4, 1, 0)) as Class_Rogue
		,SUM(IF(vsd.ClassId = 5, 1, 0)) as Class_Priest
		,SUM(IF(vsd.ClassId = 6, 1, 0)) as Class_DeathKnight
		,SUM(IF(vsd.ClassId = 7, 1, 0)) as Class_Shaman
		,SUM(IF(vsd.ClassId = 8, 1, 0)) as Class_Mage
		,SUM(IF(vsd.ClassId = 9, 1, 0)) as Class_Warlock
		,SUM(IF(vsd.ClassId = 10, 1, 0)) as Class_Monk
		,SUM(IF(vsd.ClassId = 11, 1, 0)) as Class_Druid
		,SUM(IF(vsd.ClassId = 12, 1, 0)) as Class_DemonHunter
		,SUM(IF(vsd.SpecializationId = 62, 1, 0)) as Spec_Mage_Arcane
		,SUM(IF(vsd.SpecializationId = 63, 1, 0)) as Spec_Mage_Fire
		,SUM(IF(vsd.SpecializationId = 64, 1, 0)) as Spec_Mage_Frost
		,SUM(IF(vsd.SpecializationId = 65, 1, 0)) as Spec_Paladin_Holy
		,SUM(IF(vsd.SpecializationId = 66, 1, 0)) as Spec_Paladin_Protection
		,SUM(IF(vsd.SpecializationId = 70, 1, 0)) as Spec_Paladin_Retribution
		,SUM(IF(vsd.SpecializationId = 71, 1, 0)) as Spec_Warrior_Arms
		,SUM(IF(vsd.SpecializationId = 72, 1, 0)) as Spec_Warrior_Fury
		,SUM(IF(vsd.SpecializationId = 73, 1, 0)) as Spec_Warrior_Protection
		,SUM(IF(vsd.SpecializationId = 102, 1, 0)) as Spec_Druid_Balance
		,SUM(IF(vsd.SpecializationId = 103, 1, 0)) as Spec_Druid_Feral
		,SUM(IF(vsd.SpecializationId = 104, 1, 0)) as Spec_Druid_Guardian
		,SUM(IF(vsd.SpecializationId = 105, 1, 0)) as Spec_Druid_Restoration
		,SUM(IF(vsd.SpecializationId = 250, 1, 0)) as Spec_DeathKnight_Blood
		,SUM(IF(vsd.SpecializationId = 251, 1, 0)) as Spec_DeathKnight_Frost
		,SUM(IF(vsd.SpecializationId = 252, 1, 0)) as Spec_DeathKnight_Unholy
		,SUM(IF(vsd.SpecializationId = 253, 1, 0)) as Spec_Hunter_BeastMastery
		,SUM(IF(vsd.SpecializationId = 254, 1, 0)) as Spec_Hunter_Marksmanship
		,SUM(IF(vsd.SpecializationId = 255, 1, 0)) as Spec_Hunter_Survival
		,SUM(IF(vsd.SpecializationId = 256, 1, 0)) as Spec_Priest_Discipline
		,SUM(IF(vsd.SpecializationId = 257, 1, 0)) as Spec_Priest_Holy
		,SUM(IF(vsd.SpecializationId = 258, 1, 0)) as Spec_Priest_Shadow
		,SUM(IF(vsd.SpecializationId = 259, 1, 0)) as Spec_Rogue_Assassination
		,SUM(IF(vsd.SpecializationId = 260, 1, 0)) as Spec_Rogue_Combat
		,SUM(IF(vsd.SpecializationId = 261, 1, 0)) as Spec_Rogue_Subtlety
		,SUM(IF(vsd.SpecializationId = 262, 1, 0)) as Spec_Shaman_Elemental
		,SUM(IF(vsd.SpecializationId = 263, 1, 0)) as Spec_Shaman_Enhancement
		,SUM(IF(vsd.SpecializationId = 264, 1, 0)) as Spec_Shaman_Restoration
		,SUM(IF(vsd.SpecializationId = 265, 1, 0)) as Spec_Warlock_Affliction
		,SUM(IF(vsd.SpecializationId = 266, 1, 0)) as Spec_Warlock_Demonology
		,SUM(IF(vsd.SpecializationId = 267, 1, 0)) as Spec_Warlock_Destruction
		,SUM(IF(vsd.SpecializationId = 268, 1, 0)) as Spec_Monk_Brewmaster
		,SUM(IF(vsd.SpecializationId = 269, 1, 0)) as Spec_Monk_Windwalker
		,SUM(IF(vsd.SpecializationId = 270, 1, 0)) as Spec_Monk_Mistweaver
		,SUM(IF(vsd.SpecializationId = 577, 1, 0)) as Spec_DemonHunter_Havoc
		,SUM(IF(vsd.SpecializationId = 581, 1, 0)) as Spec_DemonHunter_Vengeance
FROM	(SELECT	DATE_FORMAT(DATE_ADD('2016-01-26', INTERVAL CAST(n.number as SIGNED) - 1 DAY), '%Y-%m-%d') AS ts
         FROM	numbers as n
         WHERE	DATE_ADD('2016-01-26', INTERVAL CAST(n.number as SIGNED) - 1 DAY) <= CURRENT_DATE()) as x
LEFT OUTER JOIN	vSimulationActorDetails as vsd on DATE_FORMAT(vsd.TimeQueued, '%Y-%m-%d') = x.ts
JOIN	Simulations as sim on sim.SimulationId = vsd.SimulationId
GROUP BY RecordDate
ORDER BY RecordDate ASC;
    COMMIT ;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `TalentsList` (IN `ClassId` TINYINT UNSIGNED, IN `SpecializationId` SMALLINT UNSIGNED, IN `CalcSpec` VARCHAR(1), IN `PatchVersion` VARCHAR(8))  NO SQL
BEGIN

	IF SpecializationId = '' THEN
    	SET SpecializationId = (
            SELECT	spec.SpecializationId
            FROM	Specializations as spec
            WHERE	spec.ClassId = ClassId
            AND		spec.CalcSpec = CalcSpec
            );
    END IF;

    select	t.TalentId
            ,t.TalentName
            ,t.TalentIcon
            ,t.SpellId
            ,t.TalentRow
            ,t.TalentColumn
            ,c.ClassId
            ,c.ClassName
            ,spec.SpecializationId
            ,spec.SpecializationName
            ,t.PatchVersion
    FROM	Talents as t 
    JOIN	Classes as c on c.ClassId = t.ClassId
    LEFT JOIN Specializations as spec on spec.SpecializationId = t.SpecializationId
    WHERE	c.ClassId = ClassId
    AND		(t.SpecializationId = 9999 OR t.SpecializationID = SpecializationId)
    AND		t.IsActive = 1
    AND		t.PatchVersion = PatchVersion
    ORDER BY c.ClassId ASC, t.TalentRow ASC, t.TalentColumn ASC;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `UserActivate` (IN `ActivationCode` VARCHAR(32) CHARSET utf8)  NO SQL
BEGIN
	
    SET @IsActive = 0;
    SET @UserId = 0;
    
    IF ActivationCode = "" THEN
    
    	SET @UserId = 0;
        SET @IsActive = -1;
    
    ELSE    

		SET @UserId = (
    	    SELECT	u.UserId
        	FROM	Users as u
	        WHERE	u.ActivationCode = ActivationCode
    	);

	    IF @UserId > 0 THEN

            SET @IsActive = (
                SELECT	u.IsActive
                FROM	Users as u
                WHERE	u.ActivationCode = ActivationCode
            );

			IF @IsActive = 0 THEN
            
	    		UPDATE	Users as u
		        SET		u.IsActive = 1
                		,u.ActivationTimestamp = NOW()
    		    WHERE	u.ActivationCode = ActivationCode;
    		            
            END IF;
        
	    END IF;
	
    END IF;
    
    SELECT	@UserId as UserId
    		,@IsActive as IsActive;
    
END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `UserArchiveReport` (IN `SimulationGUID` VARCHAR(38), IN `UserId` INT UNSIGNED)  NO SQL
BEGIN

    SET @SimulationId = (
        SELECT	sim.SimulationId
        FROM	Simulations as sim
        WHERE	sim.SimulationGUID = SimulationGUID
        );
    
    SET @SimulationOwnerId = (
        SELECT	sim.UserId
        FROM	Simulations as sim
        WHERE	sim.SimulationId = @SimulationId
        );
    
    SET @IsArchived = (
        SELECT	sim.ReportArchived
        FROM	Simulations as sim
        WHERE	sim.SimulationId = @SimulationId
        );
    
    SET @Status = (
        SELECT	vsd.SimulationStatusId
        FROM	vSimulationsDetails as vsd
        WHERE	vsd.SimulationId = @SimulationId
        );
    
    SET @UserLevel = (
        SELECT	u.UserLevelId
        FROM	Users as u
        JOIN	UserLevels as ul on u.UserLevelId = ul.UserLevelId
        WHERE	u.UserId = UserId
        );
    
    IF @IsArchived = 0 AND @Status >= 3 AND (@SimulationOwnerId = UserId OR @UserLevel = 9) THEN
    
    	UPDATE	Simulations as sim
        SET		sim.ReportArchived = 1
            	,sim.TimeArchived = NOW()
        WHERE	sim.SimulationId = @SimulationId;
    
        SELECT	sim.ReportArchived as Response
        		,sim.TimeArchived as TimeArchived
    	FROM	Simulations as sim
        WHERE	sim.SimulationId = @SimulationId;
    
    ELSE
    
    	SELECT -1 as Response;
    
    END IF;
   
END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `UserGet` (IN `UserId` INT UNSIGNED, IN `Email` VARCHAR(255) CHARSET utf8)  NO SQL
BEGIN

	SET @ExitOnError = 0;

	IF (UserId = '' OR UserId Is Null) AND (Email = '' OR Email Is Null) THEN
		CALL raise(1356, 'Parameters `UserId` and `Email` are missing. One is required by SPROC UserGet');
        SET @ExitOnError = 1;
    ELSEIF UserId <> '' AND Email <> '' THEN
    	SET Email = null;
    ELSEIF UserId = '' THEN
    	SET UserId = null;
    ELSEIF Email = '' THEN
    	SET Email = null;
	END IF;

	IF @ExitOnError = 0 THEN
    	
        SELECT	u.*
        		,ul.*
        FROM	Users as u
        JOIN	UserLevels as ul on u.UserLevelId = ul.UserLevelId
        WHERE	CASE WHEN UserId Is Null THEN TRUE ELSE UserId = u.UserId END
        AND		CASE WHEN Email Is Null THEN TRUE ELSE Email = u.Email END;
    END IF;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `UserGetBySimulationId` (IN `SimulationId` INT UNSIGNED, IN `GetSimulation` TINYINT(1) UNSIGNED)  NO SQL
BEGIN

SELECT	u.UserId
    		,u.Email
            ,u.Password
            ,u.Salt
	        ,u.IsActive
           	,u.ActivationCode
            ,u.SimEmails
            ,ul.UserLevelId
            ,ul.UserLevelTitle
            ,ul.UserLevelDescription
            ,ul.DaysBeforeCleanup
            ,ul.MaxIterations
            ,ul.MaxSimQueueSize
            ,ul.MinSimLength
            ,ul.MaxSimLength
            ,ul.ScalingEnabled
            ,ul.MaxReports
            ,ul.MaxBossCount
                ,ul.MaxActors
    FROM	Users as u
	JOIN	UserLevels as ul on u.UserLevelId = ul.UserLevelId
    JOIN	Simulations as sim on u.UserId = sim.UserId
    WHERE	SimulationId = sim.SimulationId;

	IF GetSimulation = 1 THEN
    	
        CALL SimulationGet(SimulationId, 0, 0);
        
    END IF;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `UserPasswordReset` (IN `UserId` INT UNSIGNED, IN `Email` VARCHAR(255), IN `ResetCode` VARCHAR(32), IN `Password` VARCHAR(128), IN `Salt` VARCHAR(128))  NO SQL
BEGIN

	IF ResetCode <> '' AND ResetCode Is Not Null THEN
    
    	IF (Password = '' OR Password Is Null) OR (Salt = '' OR Salt Is Null) THEN        
        
        	SELECT	u.UserId
            		,u.Email
            FROM	Users as u
            WHERE	u.PasswordResetCode = ResetCode;
        
        ELSE
        
        	SELECT	u.UserId
            		,u.Email
            FROM	Users as u
            WHERE	u.PasswordResetCode = ResetCode;
        
        	UPDATE	Users as u
            SET		u.Password = Password
            		,u.Salt = Salt
                    ,u.PasswordResetCode = null
            WHERE	u.PasswordResetCode = ResetCode;   
        
        END IF;
    
    ELSE
        
        IF UserId > 0 AND UserId Is Not NULL THEN
        
        	UPDATE	Users as u
	        SET		u.Password = Password
    	    		,u.Salt = Salt
        	        ,u.PasswordResetCode = null
	        WHERE	u.UserId = UserId;
        
        	SELECT	u.UserId
            		,u.Email
            FROM	Users as u
            WHERE	u.UserId = UserId; 
            
        ELSEIF Email <> '' AND Email Is Not Null THEN
        
        	UPDATE	Users as u
	        SET		u.Password = Password
    	    		,u.Salt = Salt
        	        ,u.PasswordResetCode = null
	        WHERE	u.Email = Email;   
        
        	SELECT	u.UserId
            		,u.Email
            FROM	Users as u
            WHERE	u.Email = Email;     	
        
    	END IF;
    
    END IF;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `UserPasswordResetCodeSet` (IN `UserId` INT UNSIGNED, IN `ResetCode` VARCHAR(32))  NO SQL
BEGIN

	IF ResetCode = '' THEN
    
    	SET ResetCode = null;
        
    END IF;
    
    UPDATE	Users as u
    SET		u.PasswordResetCode = ResetCode
    WHERE	u.UserId = UserId;

	SELECT 	u.UserId
    FROM	Users as u
    WHERE	u.UserId = UserId;

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `UserUpdate` (IN `UserId` INT UNSIGNED, IN `SimEmails` TINYINT(1) UNSIGNED)  NO SQL
BEGIN

	IF UserId = '' THEN
		CALL raise(1356, 'Parameter `UserID` is required by SPROC UserUpdate');
	END IF;
    
    UPDATE	Users u
    SET		u.SimEmails = SimEmails
    WHERE	u.UserId = UserId;
    
    CALL UserGet(UserId, null);
    
END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `WorkItemClaim` (IN `ComputerId` MEDIUMINT UNSIGNED, IN `ComputerAPIKey` VARCHAR(38) CHARSET utf8, IN `SimulationId` INT UNSIGNED, IN `IPAddress` VARCHAR(45) CHARSET utf8)  NO SQL
BEGIN
	
    SET @ExitOnError = 0;
    
    SET @SendWorkItems = 0;

	IF SimulationId = '' THEN
		CALL raise(1356, 'Parameter `SimulationID` is required by SPROC WorkItemClaim');
		SET @ExitOnError = 1;
	END IF;
    
    IF @ExitOnError = 0 THEN
	
    	IF (ComputerId = '' OR ComputerId Is Null) AND (ComputerAPIKey = '' OR ComputerAPIKey Is Null) THEN
    
			SET ComputerAPIKey = null;
	        SET ComputerId = null;
        
    	ELSEIF (ComputerId = '' OR ComputerId Is Null) THEN
    
			SET ComputerId = (
            	SELECT	c.ComputerId
	        	FROM	Computers c
    	    	WHERE	c.ComputerAPIKey = ComputerAPIKey
        	);
        	
            SET @SendWorkItems = (
            	SELECT	c.SendWorkItems
        		FROM	Computers c
        		WHERE	c.ComputerAPIKey = ComputerAPIKey
        	);
            
	        IF ComputerId Is Null OR ComputerId < 1 THEN
    	    
        		SET ComputerId = null;
        	
	        END IF;
    
		END IF;
	
    	SET @SimulationStatus = 1;
    	SET @ResultCode = 0;
	    
		SELECT	sl.SimulationStatusId INTO @SimulationStatus
    	FROM	SimulationLog sl
    	WHERE	sl.SimulationId = SimulationId
    	AND		sl.IsArchive = 0;
	    
    	IF @SimulationStatus = 1 AND @SendWorkItems > 0 THEN    
			SET @SimulationLogId = NULL;
    		CALL SimulationLogInsert(SimulationId, 2, ComputerId, @SimulationLogId);
    	
	        SET @ResultCode = 1;
    
    	ELSE
        
	        SET @ResultCode = -1;

    	END IF;

		CALL ConnectionLogInsert(@ComputerId, ComputerAPIKey, @SimulationLogId, IPAddress);

		IF ComputerId > 0 AND @SendWorkItems > 0 THEN

			SELECT	@ResultCode as RequestResult
    	   		,sim.SimulationId
                ,sim.SimulationGUID
	   			,sl.SimulationLogTime
   				,ss.SimulationStatusId
	        	,ss.StatusName
    	    	,ss.StatusDescription
   				,c.ComputerId
   				,c.ComputerName
	         	,c.ComputerDescription
			FROM	Simulations sim
    	    JOIN	SimulationLog sl on sim.SimulationId = sl.SimulationId
			JOIN	SimulationStatus ss on sl.SimulationStatusId = ss.SimulationStatusId
			JOIN	Computers c on sl.ComputerId = c.ComputerId
		    WHERE	sl.SimulationId = SimulationId
    		ORDER BY	sl.SimulationLogTime DESC
	    	LIMIT 1;


		END IF;

    END IF;
    
END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `WorkItemRequest` (IN `ComputerId` MEDIUMINT(5) UNSIGNED, IN `ComputerAPIKey` VARCHAR(38) CHARSET utf8, IN `IPAddress` VARCHAR(45) CHARSET utf8, IN `Threads` TINYINT)  NO SQL
BEGIN
    
    SET @SendWorkItems = 0;
    SET @SimcLive = 0;
    SET @SimcPTR = 0;
    SET @SimcBeta = 0;
    SET @CustomProfiles = 0;
    
	IF (ComputerId = '' OR ComputerId Is Null) AND (ComputerAPIKey = '' OR ComputerAPIKey Is Null) THEN
    
		SET ComputerAPIKey = null;
        SET ComputerId = null;
        
    ELSEIF (ComputerId = '' OR ComputerId Is Null) THEN
    
		SET ComputerId = (
            SELECT	c.ComputerId
        	FROM	Computers c
        	WHERE	c.ComputerAPIKey = ComputerAPIKey
        );
        
        SET @SendWorkItems = (
            SELECT	c.SendWorkItems
        	FROM	Computers c
        	WHERE	c.ComputerAPIKey = ComputerAPIKey
        );
        
        SET @SimcLive = (
            SELECT	c.SimcLive
        	FROM	Computers c
        	WHERE	c.ComputerAPIKey = ComputerAPIKey
        );
        
        SET @SimcPTR = (
            SELECT	c.SimcPTR
        	FROM	Computers c
        	WHERE	c.ComputerAPIKey = ComputerAPIKey
        );
        
        SET @SimcBeta = (
            SELECT	c.SimcBeta
        	FROM	Computers c
        	WHERE	c.ComputerAPIKey = ComputerAPIKey
        );
        
        SET @CustomProfiles = (
            SELECT	c.CustomProfiles
        	FROM	Computers c
        	WHERE	c.ComputerAPIKey = ComputerAPIKey
        );
        
        IF Threads Is Null OR Threads = '' OR Threads < 1 THEN
        
            SET Threads = (
                SELECT	GREATEST(1, c.Threads)
                FROM	Computers c
                WHERE	c.ComputerAPIKey = ComputerAPIKey
            );
        
        END IF;
        
        IF ComputerId Is Null OR ComputerId < 1 THEN
        
        	SET ComputerId = null;
        
        END IF;
    
	END IF;

	CALL ConnectionLogInsert(ComputerId, ComputerAPIKey, null, IPAddress);
    
	IF ComputerId > 0 AND @SendWorkItems > 0 THEN

		SET @AvailableWorkItem = 0;
 	
    	IF Threads > 2 THEN
        
        	
			SELECT	vsd.SimulationId INTO @AvailableWorkItem
            FROM	SimulationLog sl
            JOIN	vSimulationsDetails vsd on sl.SimulationId = vsd.SimulationId
            WHERE	sl.SimulationStatusId = 1
            AND		sl.IsArchive = 0
            #AND		vsd.TMIBoss Is Null
            AND		(vsd.CustomProfile = '' OR @CustomProfiles = 1)
            AND		(
                		(vsd.SimulationCraftVersion = 'live' AND @SimcLive = 1)
                	OR	(vsd.SimulationCraftVersion = 'ptr' AND @SimcPTR = 1)
                	OR	(vsd.SimulationCraftVersion = 'beta' AND @SimcBeta = 1)
                	)
            ORDER BY	vsd.TimeQueued ASC
            LIMIT 1;
        
        ELSE
        
        	            
			SELECT	vsd.SimulationId INTO @AvailableWorkItem
            FROM	SimulationLog sl
            JOIN	vSimulationsDetails vsd on sl.SimulationId = vsd.SimulationId
            WHERE	sl.SimulationStatusId = 1
            AND		sl.IsArchive = 0
            #AND		vsd.TMIBoss Is Null
            AND		(	(vsd.ScaleFactors = 0
                         AND vsd.CustomProfile = "")
                	OR	vsd.TimeQueued < TIMESTAMPADD(MINUTE, -30, NOW())
                    )
            AND		(vsd.CustomProfile = '' OR @CustomProfiles = 1)
            AND		(
                		(vsd.SimulationCraftVersion = 'live' AND @SimcLive = 1)
                	OR	(vsd.SimulationCraftVersion = 'ptr' AND @SimcPTR = 1)
                	OR	(vsd.SimulationCraftVersion = 'beta' AND @SimcBeta = 1)
                	)
            ORDER BY	vsd.ScaleFactors ASC, vsd.TimeQueued ASC
            LIMIT 1;
        
        END IF;
                
    	IF @AvailableWorkItem > 0 THEN   
	
			SELECT	vsd.SimulationId
            		,vsd.SimulationGUID
					,vsd.Iterations
					,vsd.TimeQueued
					,vsd.SimulationTypeSystemName
                    ,vsd.BossCount
                    ,vsd.ScaleFactors
                    ,vsd.SimulationLength
                    ,vsd.SimulationLengthVariance
                    ,vsd.TMIWindow
                    ,vsd.TMIBoss
                    ,vsd.ActorCount
                    ,vsd.CustomProfile
                    ,vsd.SimulationCraftVersion
            FROM	vSimulationsDetails as vsd
            JOIN	SimulationLog as sl on vsd.SimulationId = sl.SimulationId
			WHERE	vsd.SimulationId = @AvailableWorkItem
            AND		sl.IsArchive = 0
    		ORDER BY	vsd.TimeQueued ASC
    		LIMIT 1;
            
            
			SELECT	vsad.SimulationActorId
            		,vsad.CharacterName
					,vsad.ServerName
					,vsad.RegionURL
                    ,vsad.RegionPrefix
            		,vsad.RegionAPIUrl
                    ,vsad.SimTalent
                    ,sa.CharacterJSON
                    ,vsad.ArmorySpec
                    ,vsad.SimulationRole
            FROM	vSimulationActorDetails as vsad
            JOIN	SimulationActors as sa on vsad.SimulationActorId = sa.SimulationActorId
			WHERE	vsad.SimulationId = @AvailableWorkItem
    		ORDER BY	vsad.SimulationActorId ASC;
			
            															            						                		    		
		END IF;

	END IF;    

END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `WorkItemSaveActorResult` (IN `SimulationActorId` INT UNSIGNED, IN `DPS` INT UNSIGNED, IN `ScalePrimaryStat` FLOAT, IN `ScaleStamina` FLOAT, IN `ScaleSpirit` FLOAT, IN `ScaleSpellPower` FLOAT, IN `ScaleAttackPower` FLOAT, IN `ScaleCrit` FLOAT, IN `ScaleHaste` FLOAT, IN `ScaleMastery` FLOAT, IN `ScaleVersatility` FLOAT, IN `ScaleMultistrike` FLOAT, IN `ScaleWeaponDPS` FLOAT, IN `ScaleOffhandWeaponDPS` FLOAT, IN `ScaleArmor` FLOAT, IN `ScaleBonusArmor` FLOAT, IN `ScaleAvoidance` FLOAT, IN `ScaleLeech` FLOAT, IN `ScaleMovementSpeed` FLOAT, IN `TMI` INT UNSIGNED, IN `DTPS` INT UNSIGNED, IN `HPS` INT UNSIGNED, IN `APS` INT UNSIGNED)  NO SQL
BEGIN

	SET @ExitOnError = 0;

	IF SimulationActorId = '' THEN
		CALL raise(1356, 'Parameter `SimulationActorID` is required by SPROC WorkItemSaveActorResult');
		SET @ExitOnError = 1;
	END IF;

    IF @ExitOnError = 0 THEN
        
        UPDATE	SimulationActors as sa 
        SET		sa.DPS = DPS
                ,sa.TMI = TMI
                ,sa.DTPS = DTPS
                ,sa.HPS = HPS
                ,sa.APS = APS
                ,sa.ScalePrimaryStat = ScalePrimaryStat
                ,sa.ScaleStamina = ScaleStamina
                ,sa.ScaleSpirit = ScaleSpirit
                ,sa.ScaleSpellPower = ScaleSpellPower
                ,sa.ScaleAttackPower = ScaleAttackPower
                ,sa.ScaleCrit = ScaleCrit
                ,sa.ScaleHaste = ScaleHaste
                ,sa.ScaleMastery = ScaleMastery
                ,sa.ScaleVersatility = ScaleVersatility
                ,sa.ScaleMultistrike = ScaleMultistrike
                ,sa.ScaleWeaponDPS = ScaleWeaponDPS
                ,sa.ScaleOffhandWeaponDPS = ScaleOffhandWeaponDPS
                ,sa.ScaleArmor = ScaleArmor
                ,sa.ScaleBonusArmor = ScaleBonusArmor
                ,sa.ScaleAvoidance = ScaleAvoidance
                ,sa.ScaleLeech = ScaleLeech
                ,sa.ScaleMovementSpeed = ScaleMovementSpeed
		WHERE	sa.SimulationActorId = SimulationActorId;

    END IF;
    
END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `WorkItemSaveResult` (IN `ComputerId` INT UNSIGNED, IN `ComputerAPIKey` VARCHAR(38), IN `SimulationId` INT UNSIGNED, IN `SimulationStatusId` TINYINT, IN `RawLog` MEDIUMTEXT, IN `IPAddress` VARCHAR(45))  NO SQL
BEGIN

	SET @ExitOnError = 0;

	IF SimulationId = '' THEN
		CALL raise(1356, 'Parameter `SimulationID` is required by SPROC WorkItemSaveLog');
		SET @ExitOnError = 1;
	END IF;

	IF SimulationStatusId = '' THEN
		CALL raise(1356, 'Parameter `SimulationStatusId` is required by SPROC WorkItemSaveLog');
		SET @ExitOnError = 1;
	END IF;
    
	IF (ComputerId = '' OR ComputerId Is Null) AND (ComputerAPIKey = '' OR ComputerAPIKey Is Null) THEN
    
		SET ComputerAPIKey = null;
       	SET ComputerId = null;
        
    ELSEIF (ComputerId = '' OR ComputerId Is Null) THEN
    
		SET ComputerId = (
       	    SELECT	c.ComputerId
       		FROM	Computers c
        	WHERE	c.ComputerAPIKey = ComputerAPIKey
   	    );
        
       	IF ComputerId Is Null OR ComputerId < 1 THEN
        
    		SET @ExitOnError = 1;
        
	    END IF;
    
	END IF;

    IF @ExitOnError = 1 THEN
    
		CALL ConnectionLogInsert(ComputerId, ComputerAPIKey, null, IPAddress);
    
    ELSE
			
		SET @SimulationLogId = NULL;
   		CALL SimulationLogInsert(SimulationId, SimulationStatusId, ComputerId, @SimulationLogId);

		CALL ConnectionLogInsert(ComputerId, ComputerAPIKey, @SimulationLogId, IPAddress);

		UPDATE	Simulations as s
        SET		s.SimulationRawLog = RawLog
        		,s.TimeCompleted = CURRENT_TIMESTAMP
		WHERE	s.SimulationId = SimulationId;
        
		SET @UserId = (
            SELECT	u.UserId
            FROM	Users as u
            JOIN	Simulations as sim on u.UserId = sim.UserId
            WHERE	sim.SimulationId = SimulationId
            );

        SET @ReportCount = (
            SELECT	COUNT(*)
            FROM	vSimulationsDetails as vsd
            JOIN	Users as u on vsd.UserId = u.UserId
            JOIN	UserLevels as ul on u.UserLevelId = ul.UserLevelId
            JOIN	SimulationLog as sl on vsd.SimulationId = sl.SimulationId
            WHERE	u.UserId = @UserId
            AND		vsd.ReportArchived = 0
            AND		sl.IsArchive = 0
            AND		sl.SimulationStatusId = 3
            );
            
        SET @MaxReports = (
            SELECT	ul.MaxReports
            FROM	UserLevels as ul
            JOIN	Users as u on ul.UserLevelId = u.UserLevelId
            WHERE	u.UserId = @UserId
            );

		IF @ReportCount > @MaxReports THEN
        
        	SET @ReportsToRemoveCount = (SELECT (@ReportCount - @MaxReports));
        	
            PREPARE tmpstmt FROM "SELECT	sim.SimulationId
            		,sim.SimulationGUID
            FROM	Simulations as sim
            JOIN	Users as u on sim.UserId = u.UserId
            JOIN	UserLevels as ul on u.UserLevelId = ul.UserLevelId
            JOIN	SimulationLog as sl on sim.SimulationId = sl.SimulationId
            WHERE	u.UserId = ?
            AND		sim.ReportArchived = 0
            AND		sl.IsArchive = 0
            AND		sl.SimulationStatusId = 3
            ORDER BY	sim.TimeQueued ASC
            LIMIT ?;";
            
            EXECUTE tmpstmt USING @UserId, @ReportsToRemoveCount;
            
            DEALLOCATE PREPARE tmpstmt;
        
        ELSE
        
			SELECT -1 AS SimulationId;
        
        END IF;

    END IF;
    
END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `WorkItemSaveResult1` (IN `ComputerId` INT UNSIGNED, IN `ComputerAPIKey` VARCHAR(38) CHARSET utf8, IN `SimulationId` INT UNSIGNED, IN `SimulationStatusId` TINYINT UNSIGNED, IN `RawLog` MEDIUMTEXT CHARSET utf8, IN `IPAddress` VARCHAR(45) CHARSET utf8, IN `ClassName` VARCHAR(20) CHARSET utf8, IN `SpecializationName` VARCHAR(30) CHARSET utf8, IN `RaceName` VARCHAR(20) CHARSET utf8, IN `Level` TINYINT UNSIGNED, IN `DPS` INT UNSIGNED, IN `ScalePrimaryStat` FLOAT, IN `ScaleStamina` FLOAT, IN `ScaleSpirit` FLOAT, IN `ScaleSpellPower` FLOAT, IN `ScaleAttackPower` FLOAT, IN `ScaleCrit` FLOAT, IN `ScaleHaste` FLOAT, IN `ScaleMastery` FLOAT, IN `ScaleVersatility` FLOAT, IN `ScaleMultistrike` FLOAT, IN `ScaleWeaponDPS` FLOAT, IN `ScaleOffhandWeaponDPS` FLOAT, IN `ScaleArmor` FLOAT, IN `ScaleBonusArmor` FLOAT, IN `ScaleAvoidance` FLOAT, IN `ScaleLeech` FLOAT, IN `ScaleMovementSpeed` FLOAT)  NO SQL
BEGIN

	SET @ExitOnError = 0;

	IF SimulationId = '' THEN
		CALL raise(1356, 'Parameter `SimulationID` is required by SPROC WorkItemSaveLog');
		SET @ExitOnError = 1;
	END IF;

	IF SimulationStatusId = '' THEN
		CALL raise(1356, 'Parameter `SimulationStatusId` is required by SPROC WorkItemSaveLog');
		SET @ExitOnError = 1;
	END IF;
    
	IF (ComputerId = '' OR ComputerId Is Null) AND (ComputerAPIKey = '' OR ComputerAPIKey Is Null) THEN
    
		SET ComputerAPIKey = null;
       	SET ComputerId = null;
        
    ELSEIF (ComputerId = '' OR ComputerId Is Null) THEN
    
		SET ComputerId = (
       	    SELECT	c.ComputerId
       		FROM	Computers c
        	WHERE	c.ComputerAPIKey = ComputerAPIKey
   	    );
        
       	IF ComputerId Is Null OR ComputerId < 1 THEN
        
    		SET @ExitOnError = 1;
        
	    END IF;
    
	END IF;

    IF @ExitOnError = 1 THEN
    
		CALL ConnectionLogInsert(ComputerId, ComputerAPIKey, null, IPAddress);
    
    ELSE
		
		SET @ClassId = (
            SELECT	c.ClassId
            FROM	Classes as c
            WHERE	c.ClassName = ClassName
        );
        
        SET @SpecializationId = (
            SELECT	s.SpecializationId
            FROM	Specializations as s
            WHERE	s.ClassId = @ClassId
            AND		s.SpecializationName = SpecializationName
        );
        
#        SET @RaceId = (
#            SELECT	r.RaceId
#            FROM	Races as r
#            WHERE	r.RaceName = RaceName
#        );
		
		SET @SimulationLogId = NULL;
   		CALL SimulationLogInsert(SimulationId, SimulationStatusId, ComputerId, @SimulationLogId);

		CALL ConnectionLogInsert(ComputerId, ComputerAPIKey, @SimulationLogId, IPAddress);

		UPDATE	Simulations as s
        SET		s.SimulationRawLog = RawLog
        		,s.TimeCompleted = CURRENT_TIMESTAMP
                ,s.ClassId = @ClassId
                ,s.SpecializationId = @SpecializationId
#                ,s.RaceId = @RaceId
                ,s.Level = Level
                ,s.DPS = DPS
                ,s.ScalePrimaryStat = ScalePrimaryStat
                ,s.ScaleStamina = ScaleStamina
                ,s.ScaleSpirit = ScaleSpirit
                ,s.ScaleSpellPower = ScaleSpellPower
                ,s.ScaleAttackPower = ScaleAttackPower
                ,s.ScaleCrit = ScaleCrit
                ,s.ScaleHaste = ScaleHaste
                ,s.ScaleMastery = ScaleMastery
                ,s.ScaleVersatility = ScaleVersatility
                ,s.ScaleMultistrike = ScaleMultistrike
                ,s.ScaleWeaponDPS = ScaleWeaponDPS
                ,s.ScaleOffhandWeaponDPS = ScaleOffhandWeaponDPS
                ,s.ScaleArmor = ScaleArmor
                ,s.ScaleBonusArmor = ScaleBonusArmor
                ,s.ScaleAvoidance = ScaleAvoidance
                ,s.ScaleLeech = ScaleLeech
                ,s.ScaleMovementSpeed = ScaleMovementSpeed
		WHERE	s.SimulationId = SimulationId;

		SELECT 1;

    END IF;
    
END$$

CREATE DEFINER=`beotorch`@`64.111.96.0/255.255.224.0` PROCEDURE `WorkItemSaveResultShort` (IN `ComputerId` INT UNSIGNED, IN `ComputerAPIKey` VARCHAR(38), IN `SimulationId` INT UNSIGNED, IN `SimulationStatusId` TINYINT UNSIGNED, IN `RawLog` MEDIUMTEXT, IN `IPAddress` VARCHAR(45), IN `Archive` TINYINT(1) UNSIGNED)  NO SQL
BEGIN

	SET @ExitOnError = 0;

	IF SimulationId = '' THEN
		CALL raise(1356, 'Parameter `SimulationID` is required by SPROC WorkItemSaveLog');
		SET @ExitOnError = 1;
	END IF;

	IF SimulationStatusId = '' THEN
		CALL raise(1356, 'Parameter `SimulationStatusId` is required by SPROC WorkItemSaveLog');
		SET @ExitOnError = 1;
	END IF;
    
	IF (ComputerId = '' OR ComputerId Is Null) AND (ComputerAPIKey = '' OR ComputerAPIKey Is Null) THEN
    
		SET ComputerAPIKey = null;
       	SET ComputerId = null;
        
    ELSEIF (ComputerId = '' OR ComputerId Is Null) THEN
    
		SET ComputerId = (
       	    SELECT	c.ComputerId
       		FROM	Computers c
        	WHERE	c.ComputerAPIKey = ComputerAPIKey
   	    );
        
       	IF ComputerId Is Null OR ComputerId < 1 THEN
        
    		SET @ExitOnError = 1;
        
	    END IF;
    
	END IF;

    IF @ExitOnError = 1 THEN
    
		CALL ConnectionLogInsert(ComputerId, ComputerAPIKey, null, IPAddress);
    
    ELSE

		SET @SimulationLogId = NULL;
   		CALL SimulationLogInsert(SimulationId, SimulationStatusId, ComputerId, @SimulationLogId);

		CALL ConnectionLogInsert(ComputerId, ComputerAPIKey, @SimulationLogId, IPAddress);

		IF Archive = 0 THEN
        
			UPDATE	Simulations as s
    	    SET		s.SimulationRawLog = RawLog
        			,s.TimeCompleted = CURRENT_TIMESTAMP
			WHERE	s.SimulationId = SimulationId;
            
        ELSE
        
        	UPDATE	Simulations as s
	        SET		s.SimulationRawLog = RawLog
    	    		,s.TimeCompleted = CURRENT_TIMESTAMP
        	        ,s.ReportArchived = Archive
                    ,s.TimeArchived = CURRENT_TIMESTAMP
			WHERE	s.SimulationId = SimulationId;

        
        END IF;

		SELECT 1;

    END IF;
    
END$$

DELIMITER ;
