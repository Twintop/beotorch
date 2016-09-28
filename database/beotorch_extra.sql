

ALTER TABLE `Batches`
  ADD PRIMARY KEY (`BatchId`);

ALTER TABLE `BatchSimulations`
  ADD PRIMARY KEY (`BatchSimulationId`);

ALTER TABLE `Characters`
  ADD PRIMARY KEY (`CharacterId`),
  ADD UNIQUE KEY `CharacterId` (`CharacterId`);

ALTER TABLE `CharacterTempStorage`
  ADD PRIMARY KEY (`CharacterTempStorageId`);

ALTER TABLE `Classes`
  ADD PRIMARY KEY (`ClassId`),
  ADD UNIQUE KEY `ClassId` (`ClassId`);

ALTER TABLE `Computers`
  ADD PRIMARY KEY (`ComputerId`),
  ADD UNIQUE KEY `ComputerId` (`ComputerId`);

ALTER TABLE `ConfigurationValues`
  ADD PRIMARY KEY (`ConfigurationValuesId`),
  ADD UNIQUE KEY `ConfigurationValuesId` (`ConfigurationValuesId`);

ALTER TABLE `ConnectionLog`
  ADD PRIMARY KEY (`ConnectionLogId`),
  ADD UNIQUE KEY `ConnectionLogId` (`ConnectionLogId`);

ALTER TABLE `Locales`
  ADD PRIMARY KEY (`LocaleId`),
  ADD UNIQUE KEY `LocaleId` (`LocaleId`);

ALTER TABLE `LoginAttemptCode`
  ADD PRIMARY KEY (`LoginAttemptCodeId`),
  ADD UNIQUE KEY `LoginAttemptCodeId` (`LoginAttemptCodeId`);

ALTER TABLE `LoginAttempts`
  ADD PRIMARY KEY (`LoginAttemptId`),
  ADD UNIQUE KEY `LoginAttemptId` (`LoginAttemptId`),
  ADD KEY `LoginAttemptId_2` (`LoginAttemptId`);

ALTER TABLE `Races`
  ADD PRIMARY KEY (`RaceId`),
  ADD UNIQUE KEY `RaceId` (`RaceId`);

ALTER TABLE `Regions`
  ADD PRIMARY KEY (`RegionId`),
  ADD UNIQUE KEY `RegionId` (`RegionId`);

ALTER TABLE `Servers`
  ADD PRIMARY KEY (`ServerId`),
  ADD UNIQUE KEY `ServerId` (`ServerId`);

ALTER TABLE `SimulationActors`
  ADD PRIMARY KEY (`SimulationActorId`,`SimulationId`) USING BTREE,
  ADD KEY `CharacterId` (`CharacterId`),
  ADD KEY `ClassId` (`ClassId`),
  ADD KEY `SpecializationId` (`SpecializationId`),
  ADD KEY `Level` (`Level`),
  ADD KEY `ItemLevel` (`ItemLevel`),
  ADD KEY `SimulationId` (`SimulationId`),
  ADD KEY `TMI` (`TMI`),
  ADD KEY `DPS` (`DPS`),
  ADD KEY `SimulationRole` (`SimulationRole`);

ALTER TABLE `SimulationLog`
  ADD PRIMARY KEY (`SimulationLogId`),
  ADD UNIQUE KEY `SimulationLogId` (`SimulationLogId`),
  ADD KEY `SimulationId` (`SimulationId`);

ALTER TABLE `Simulations`
  ADD PRIMARY KEY (`SimulationId`),
  ADD UNIQUE KEY `SimulationId` (`SimulationId`),
  ADD KEY `UserId` (`UserId`),
  ADD KEY `Iterations` (`Iterations`),
  ADD KEY `SimulationTypeId` (`SimulationTypeId`),
  ADD KEY `SimulationArchived` (`SimulationArchived`),
  ADD KEY `IsHidden` (`IsHidden`);

ALTER TABLE `SimulationStatus`
  ADD PRIMARY KEY (`SimulationStatusId`),
  ADD UNIQUE KEY `SimulationStatusId` (`SimulationStatusId`);

ALTER TABLE `SimulationTypes`
  ADD PRIMARY KEY (`SimulationTypeId`),
  ADD UNIQUE KEY `SimulationTypeId` (`SimulationTypeId`);

ALTER TABLE `Specializations`
  ADD PRIMARY KEY (`SpecializationId`),
  ADD UNIQUE KEY `SpecializationId` (`SpecializationId`);

ALTER TABLE `Talents`
  ADD PRIMARY KEY (`TalentId`),
  ADD UNIQUE KEY `TalentId` (`TalentId`);

ALTER TABLE `UserLevels`
  ADD PRIMARY KEY (`UserLevelId`),
  ADD UNIQUE KEY `UserLevelId` (`UserLevelId`);

ALTER TABLE `Users`
  ADD PRIMARY KEY (`Email`),
  ADD UNIQUE KEY `UserId` (`UserId`);


ALTER TABLE `Batches`
  MODIFY `BatchId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;
ALTER TABLE `BatchSimulations`
  MODIFY `BatchSimulationId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1504;
ALTER TABLE `Characters`
  MODIFY `CharacterId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8996;
ALTER TABLE `CharacterTempStorage`
  MODIFY `CharacterTempStorageId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8372;
ALTER TABLE `Computers`
  MODIFY `ComputerId` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
ALTER TABLE `ConfigurationValues`
  MODIFY `ConfigurationValuesId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `ConnectionLog`
  MODIFY `ConnectionLogId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1056350;
ALTER TABLE `Locales`
  MODIFY `LocaleId` tinyint(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
ALTER TABLE `LoginAttempts`
  MODIFY `LoginAttemptId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28774;
ALTER TABLE `Regions`
  MODIFY `RegionId` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `Servers`
  MODIFY `ServerId` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=896;
ALTER TABLE `SimulationActors`
  MODIFY `SimulationActorId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54228;
ALTER TABLE `SimulationLog`
  MODIFY `SimulationLogId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81724;
ALTER TABLE `Simulations`
  MODIFY `SimulationId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27250;
ALTER TABLE `SimulationTypes`
  MODIFY `SimulationTypeId` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
ALTER TABLE `Talents`
  MODIFY `TalentId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=964;
ALTER TABLE `Users`
  MODIFY `UserId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7056;