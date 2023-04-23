CREATE TABLE `linksRelevancy` (
  `id` int NOT NULL AUTO_INCREMENT,
  `urlDomain` varchar(255) NOT NULL,
  `ocurrences` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_UNIQUE` (`urlDomain`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE `searched` (
  `idSearch` int NOT NULL AUTO_INCREMENT,
  `query` varchar(511) DEFAULT NULL,
  PRIMARY KEY (`idSearch`),
  UNIQUE KEY `query_UNIQUE` (`query`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
