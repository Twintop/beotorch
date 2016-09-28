
CREATE TABLE `CharacterTempStorage` (
  `CharacterTempStorageId` int(10) UNSIGNED NOT NULL,
  `CharacterId` int(10) UNSIGNED NOT NULL,
  `UserId` int(10) UNSIGNED NOT NULL,
  `CharacterJSON` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
