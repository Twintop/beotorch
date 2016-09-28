
CREATE TABLE `Classes` (
  `ClassId` tinyint(3) UNSIGNED NOT NULL,
  `ClassName` varchar(20) NOT NULL,
  `ClassColor` varchar(6) NOT NULL,
  `CalcClass` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `Classes` (`ClassId`, `ClassName`, `ClassColor`, `CalcClass`) VALUES
(1, 'Warrior', 'C79C6E', 'Z'),
(2, 'Paladin', 'F58CBA', 'b'),
(3, 'Hunter', 'ABD473', 'Y'),
(4, 'Rogue', 'FFF569', 'c'),
(5, 'Priest', 'FFFFFF', 'X'),
(6, 'Death Knight', 'C41F3B', 'd'),
(7, 'Shaman', '0070DE', 'W'),
(8, 'Mage', '69CCF0', 'e'),
(9, 'Warlock', '9482C9', 'V'),
(10, 'Monk', '00FF96', 'f'),
(11, 'Druid', 'FF7D0A', 'U'),
(12, 'Demon Hunter', 'A330C9', 'g'),
(99, '(Unknown)', '555555', '.');
