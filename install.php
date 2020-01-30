<?php
define("PROTECT", true);
require_once "include/init.php";

$query = $db->prepare(<<<SQLSTMT
CREATE TABLE IF NOT EXISTS `projekt` (
`nev` varchar(32) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`nev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;
SQLSTMT
);
$query->execute();

$query = $db->prepare(<<<SQLSTMT
CREATE TABLE IF NOT EXISTS `kategoria` (
`projekt.nev` varchar(32) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  `nev` varchar(32) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`projekt.nev`,`nev`),
  CONSTRAINT `FK_kategoria_projekt` FOREIGN KEY (`projekt.nev`) REFERENCES `projekt` (`nev`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;
SQLSTMT
);
$query->execute();

$query = $db->prepare(<<<SQLSTMT
CREATE TABLE IF NOT EXISTS `felhasznalo` (
`felhasznalonev` varchar(32) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  `jelszo` binary(60) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `email` varchar(32) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  `telefon` varchar(16) COLLATE utf8mb4_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`felhasznalonev`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `felhasznalo_telefon_uindex` (`telefon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;
SQLSTMT
);
$query->execute();

$query = $db->prepare(<<<SQLSTMT
CREATE TABLE IF NOT EXISTS `projektkezeles` (
`projekt.nev` varchar(32) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  `felhasznalo.felhasznalonev` varchar(32) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`projekt.nev`,`felhasznalo.felhasznalonev`),
  KEY `FK_felhasznalo_projektkezeles` (`felhasznalo.felhasznalonev`),
  CONSTRAINT `FK_felhasznalo_projektkezeles` FOREIGN KEY (`felhasznalo.felhasznalonev`) REFERENCES `felhasznalo` (`felhasznalonev`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_projekt_projektkezeles` FOREIGN KEY (`projekt.nev`) REFERENCES `projekt` (`nev`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;
SQLSTMT
);
$query->execute();

$query = $db->prepare(<<<SQLSTMT
CREATE TABLE IF NOT EXISTS `kategoriakezeles` (
`projekt.nev` varchar(32) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  `kategoria.nev` varchar(32) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  `felhasznalo.felhasznalonev` varchar(32) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`projekt.nev`,`kategoria.nev`,`felhasznalo.felhasznalonev`),
  KEY `FK_felhasznalo_kategoriakezeles` (`felhasznalo.felhasznalonev`),
  CONSTRAINT `FK_felhasznalo_kategoriakezeles` FOREIGN KEY (`felhasznalo.felhasznalonev`) REFERENCES `felhasznalo` (`felhasznalonev`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_kategoria_kategoriakezeles` FOREIGN KEY (`projekt.nev`, `kategoria.nev`) REFERENCES `kategoria` (`projekt.nev`, `nev`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;
SQLSTMT
);
$query->execute();

$query = $db->prepare(<<<SQLSTMT
CREATE TABLE IF NOT EXISTS `bug` (
`azonosito` int(11) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `targy` varchar(32) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  `leiras` text COLLATE utf8mb4_hungarian_ci NOT NULL,
  `sulyossag` varchar(16) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  `szerzo.felhasznalonev` varchar(32) COLLATE utf8mb4_hungarian_ci DEFAULT NULL,
  `hozzarendelt.felhasznalonev` varchar(32) COLLATE utf8mb4_hungarian_ci DEFAULT NULL,
  `projekt.nev` varchar(32) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  `kategoria.nev` varchar(32) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  `keszites_idopont` datetime NOT NULL,
  PRIMARY KEY (`azonosito`),
  KEY `FK_felhasznalo_bug` (`szerzo.felhasznalonev`),
  KEY `FK_kategoria_bug` (`projekt.nev`,`kategoria.nev`),
  KEY `FK_hozzarendelt_bug` (`hozzarendelt.felhasznalonev`),
  CONSTRAINT `FK_felhasznalo_bug` FOREIGN KEY (`szerzo.felhasznalonev`) REFERENCES `felhasznalo` (`felhasznalonev`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_hozzarendelt_bug` FOREIGN KEY (`hozzarendelt.felhasznalonev`) REFERENCES `felhasznalo` (`felhasznalonev`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `FK_kategoria_bug` FOREIGN KEY (`projekt.nev`, `kategoria.nev`) REFERENCES `kategoria` (`projekt.nev`, `nev`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;
SQLSTMT
);
$query->execute();

$query = $db->prepare(<<<SQLSTMT
CREATE TABLE IF NOT EXISTS `hozzaszolas` (
`idopont` datetime NOT NULL,
  `bug.azonosito` int(11) unsigned NOT NULL,
  `tartalom` text COLLATE utf8mb4_hungarian_ci NOT NULL,
  `felhasznalo.felhasznalonev` varchar(32) COLLATE utf8mb4_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`idopont`,`bug.azonosito`),
  KEY `FK_bug_hozzaszolas` (`bug.azonosito`),
  CONSTRAINT `FK_bug_hozzaszolas` FOREIGN KEY (`bug.azonosito`) REFERENCES `bug` (`azonosito`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;
SQLSTMT
);
$query->execute();

$query = $db->prepare(<<<SQLSTMT
CREATE TABLE IF NOT EXISTS `statuszfrissites` (
`idopont` datetime NOT NULL,
  `bug.azonosito` int(11) unsigned NOT NULL,
  `regi_statusz` varchar(32) COLLATE utf8mb4_hungarian_ci DEFAULT NULL,
  `uj_statusz` varchar(32) COLLATE utf8mb4_hungarian_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`idopont`,`bug.azonosito`),
  KEY `FK_bug_statuszfrissites` (`bug.azonosito`),
  CONSTRAINT `FK_bug_statuszfrissites` FOREIGN KEY (`bug.azonosito`) REFERENCES `bug` (`azonosito`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;
SQLSTMT
);
$query->execute();

header("Location: ./");