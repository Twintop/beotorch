
CREATE TABLE `Batches` (
  `BatchId` int(10) UNSIGNED NOT NULL,
  `BatchGUID` char(38) NOT NULL,
  `BatchName` varchar(255) NOT NULL,
  `UserId` int(11) UNSIGNED NOT NULL,
  `IsArchive` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
