
CREATE TABLE `LoginAttemptCode` (
  `LoginAttemptCodeId` tinyint(4) NOT NULL,
  `LoginAttemptCodeName` varchar(255) NOT NULL,
  `LoginAttemptCodeDescription` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `LoginAttemptCode` (`LoginAttemptCodeId`, `LoginAttemptCodeName`, `LoginAttemptCodeDescription`) VALUES
(1, 'Success', 'Valid login.'),
(2, 'Hash Mismatch', 'Password + Browser Hash did not match session store.'),
(3, 'Activation', 'Account requires activation.'),
(4, 'Brute Force', 'Brute force detected, not allowing login.'),
(5, 'Invalid', 'Invalid Password for a user with a record in the Users table.'),
(6, 'User Missing', 'User does not have a matching email address in the Users table.');
