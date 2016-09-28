
CREATE TABLE `ConnectionLog` (
  `ConnectionLogId` int(10) UNSIGNED NOT NULL,
  `ComputerId` smallint(5) UNSIGNED DEFAULT NULL,
  `SimulationLogId` int(10) UNSIGNED DEFAULT NULL,
  `ConnectionLogTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IPAddress` varchar(45) NOT NULL,
  `SuppliedComputerAPIKey` varchar(38) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
