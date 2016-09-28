
CREATE TABLE `Locales` (
  `LocaleId` tinyint(4) UNSIGNED NOT NULL,
  `Locale` varchar(5) NOT NULL,
  `LocaleName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `Locales` (`LocaleId`, `Locale`, `LocaleName`) VALUES
(1, 'en_US', 'United States'),
(2, '', 'Oceanic'),
(3, '', 'Brazil'),
(4, '', 'Latin America'),
(5, '', 'English'),
(6, '', 'French'),
(7, '', 'German'),
(8, '', 'Italian'),
(9, '', 'Russian'),
(10, '', 'Spanish'),
(11, '', 'Portuguese'),
(12, 'ko_KR', 'Korean'),
(13, 'zh_TW', 'Taiwanese'),
(14, 'zh_CN', 'Chinese');
