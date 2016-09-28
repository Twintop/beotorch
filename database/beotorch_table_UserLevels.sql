
CREATE TABLE `UserLevels` (
  `UserLevelId` tinyint(3) UNSIGNED NOT NULL,
  `UserLevelTitle` varchar(255) NOT NULL,
  `UserLevelDescription` varchar(1000) NOT NULL,
  `MaxSimQueueSize` int(10) UNSIGNED NOT NULL,
  `DaysBeforeCleanup` int(10) UNSIGNED NOT NULL,
  `MaxIterations` int(10) UNSIGNED NOT NULL,
  `ScalingEnabled` tinyint(3) UNSIGNED NOT NULL,
  `MinSimLength` smallint(5) UNSIGNED NOT NULL,
  `MaxSimLength` smallint(5) UNSIGNED NOT NULL,
  `MaxReports` int(10) UNSIGNED NOT NULL,
  `MaxBossCount` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `MaxActors` tinyint(3) UNSIGNED NOT NULL DEFAULT '3',
  `HiddenSimulations` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `CustomProfile` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `UserLevels` (`UserLevelId`, `UserLevelTitle`, `UserLevelDescription`, `MaxSimQueueSize`, `DaysBeforeCleanup`, `MaxIterations`, `ScalingEnabled`, `MinSimLength`, `MaxSimLength`, `MaxReports`, `MaxBossCount`, `MaxActors`, `HiddenSimulations`, `CustomProfile`) VALUES
(1, 'Member', '', 10, 15, 10000, 1, 180, 600, 25, 2, 1, 0, 0),
(2, 'HowToPriest Donor', '', 25, 45, 25000, 1, 60, 900, 50, 4, 6, 1, 0),
(3, 'Patreon Donor', '', 50, 180, 50000, 1, 60, 900, 75, 8, 9, 1, 0),
(4, 'Friend', 'You know someone...Gratz for your nepotism!', 50, 90, 50000, 1, 60, 900, 50, 4, 9, 1, 0),
(5, 'Custom Profiles Enabled', '', 50, 365, 50000, 1, 60, 900, 250, 8, 9, 1, 1),
(9, 'Administrator', '', 999, 10000, 100000, 1, 1, 1500, 100000, 8, 27, 1, 1);
