
CREATE TABLE `LoginAttempts` (
  `LoginAttemptId` int(10) UNSIGNED NOT NULL,
  `UserId` int(10) UNSIGNED NOT NULL,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ResultCode` tinyint(4) NOT NULL DEFAULT '1',
  `IPAddress` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Login attempts';
