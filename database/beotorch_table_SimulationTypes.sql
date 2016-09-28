
CREATE TABLE `SimulationTypes` (
  `SimulationTypeId` tinyint(3) UNSIGNED NOT NULL,
  `SimulationTypeFriendlyName` varchar(255) NOT NULL,
  `SimulationTypeSystemName` varchar(255) NOT NULL,
  `SimulationTypeDescription` varchar(2000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `SimulationTypes` (`SimulationTypeId`, `SimulationTypeFriendlyName`, `SimulationTypeSystemName`, `SimulationTypeDescription`) VALUES
(1, 'Patchwerk', 'Patchwerk', 'A simulation with one boss target, no movement, no add spawns, and no extra mechanics to compensate for.'),
(2, 'HelterSkelter', 'HelterSkelter', 'Movement, stuns, interrupts, target-switching (every 2 minutes)'),
(3, 'Light Movement', 'LightMovement', 'Short bursts of movement spread throughout the entire simulation except for on the pull and at the very end.'),
(4, 'Heavy Movement', 'HeavyMovement', 'Frequent movement throughout the entire simulation.'),
(5, 'Ultraxion', 'Ultraxion', 'Periodic stuns, raid damage. Similar to Ultraxion in Dragon Soul.'),
(6, 'Hectic Add Cleave', 'HecticAddCleave', 'Heavy movement, frequent add spawns. Similar to Horridon in Throne of Thunder.'),
(7, 'Beastlord', 'Beastlord', 'Random movement, advanced positioning, frequent single and wave add spawns. Similar to Beastlord in Blackrock Foundry.');
