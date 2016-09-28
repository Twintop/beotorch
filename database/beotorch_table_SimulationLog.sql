
CREATE TABLE `SimulationLog` (
  `SimulationLogId` int(10) UNSIGNED NOT NULL,
  `SimulationId` int(10) UNSIGNED NOT NULL,
  `ComputerId` smallint(5) UNSIGNED DEFAULT NULL,
  `SimulationStatusId` tinyint(4) NOT NULL,
  `SimulationLogTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IsArchive` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
