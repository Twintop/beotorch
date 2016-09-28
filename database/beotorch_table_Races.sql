
CREATE TABLE `Races` (
  `RaceId` tinyint(10) UNSIGNED NOT NULL,
  `RaceName` varchar(20) NOT NULL,
  `Faction` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `Races` (`RaceId`, `RaceName`, `Faction`) VALUES
(0, '(Unknown)', 3),
(1, 'Human', 0),
(2, 'Orc', 1),
(3, 'Dwarf', 0),
(4, 'Night Elf', 0),
(5, 'Undead', 1),
(6, 'Tauren', 1),
(7, 'Gnome', 0),
(8, 'Troll', 1),
(9, 'Goblin', 1),
(10, 'Blood Elf', 1),
(11, 'Draenei', 0),
(22, 'Worgen', 0),
(24, 'Pandaren', 2),
(25, 'Pandaren', 0),
(26, 'Pandaren', 1);
