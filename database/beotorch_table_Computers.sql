
CREATE TABLE `Computers` (
  `ComputerId` smallint(5) UNSIGNED NOT NULL,
  `ComputerName` varchar(255) NOT NULL,
  `ComputerDescription` varchar(1000) NOT NULL,
  `ComputerAPIKey` varchar(38) NOT NULL,
  `SendWorkItems` tinyint(1) NOT NULL DEFAULT '1',
  `ScriptVersion` varchar(10) NOT NULL,
  `GitRevision` varchar(40) NOT NULL,
  `Threads` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `SimcLive` tinyint(1) NOT NULL DEFAULT '1',
  `SimcPTR` tinyint(1) NOT NULL DEFAULT '0',
  `SimcBeta` tinyint(1) NOT NULL DEFAULT '0',
  `CustomProfiles` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
