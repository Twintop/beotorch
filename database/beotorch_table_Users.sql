
CREATE TABLE `Users` (
  `UserId` int(10) UNSIGNED NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(128) NOT NULL,
  `Salt` varchar(128) NOT NULL,
  `IsActive` tinyint(4) NOT NULL DEFAULT '0',
  `UserLevelId` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `ActivationCode` varchar(32) NOT NULL,
  `ActivationTimestamp` timestamp NULL DEFAULT NULL,
  `SimEmails` tinyint(1) NOT NULL DEFAULT '1',
  `PasswordResetCode` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Users table';
