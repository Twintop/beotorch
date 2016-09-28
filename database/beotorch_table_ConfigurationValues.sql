
CREATE TABLE `ConfigurationValues` (
  `ConfigurationValuesId` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ConfigurationValues` (`ConfigurationValuesId`, `Name`, `Value`) VALUES
(1, 'GitRevision', '1013459cde6fc5845337c29ab96708f96a5d15d1');
