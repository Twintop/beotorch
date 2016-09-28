
CREATE TABLE `SimulationStatus` (
  `SimulationStatusId` tinyint(4) NOT NULL,
  `StatusName` varchar(255) NOT NULL,
  `StatusDescription` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `SimulationStatus` (`SimulationStatusId`, `StatusName`, `StatusDescription`) VALUES
(1, 'New', 'Simulation is waiting in the queue to be processed.'),
(2, 'Simulating', 'Simulation is now being processed by the Beotorch system. Results incoming!'),
(3, 'Complete', 'Simulation completed successfully! Results are now available.'),
(4, 'Error', 'An error occurred while trying to simulate this character + fight type.'),
(5, 'Character Not Found', 'Specified character was not found via the Battle.net API.'),
(6, 'Canceled', 'Queued simulation has been canceled.');

