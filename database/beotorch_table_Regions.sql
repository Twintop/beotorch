
CREATE TABLE `Regions` (
  `RegionId` tinyint(3) UNSIGNED NOT NULL,
  `RegionName` varchar(255) NOT NULL,
  `RegionURL` varchar(255) NOT NULL,
  `RegionPrefix` varchar(4) NOT NULL,
  `RegionAPIUrl` varchar(255) NOT NULL,
  `RegionThumbnailURL` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `Regions` (`RegionId`, `RegionName`, `RegionURL`, `RegionPrefix`, `RegionAPIUrl`, `RegionThumbnailURL`) VALUES
(1, 'Americas & Oceania', 'us.battle.net/wow/en/character/', 'us', 'https://us.api.battle.net/wow/character/', 'http://render-api-us.worldofwarcraft.com/static-render/us/'),
(2, 'Europe', 'eu.battle.net/wow/en/character/', 'eu', 'https://eu.api.battle.net/wow/character/', 'http://render-api-eu.worldofwarcraft.com/static-render/eu/'),
(3, 'Korea', 'kr.battle.net/wow/ko/character/', 'kr', 'https://kr.api.battle.net/wow/character/', 'http://render-api-kr.worldofwarcraft.com/static-render/kr/'),
(4, 'Taiwan', 'tw.battle.net/wow/zh/character/', 'tw', 'https://tw.api.battle.net/wow/character/', 'http://render-api-tw.worldofwarcraft.com/static-render/tw/'),
(5, 'China', 'www.battlenet.com.cn/wow/zh/character/', 'cn', 'www.battlenet.com.cn/api/wow/character/', '');
