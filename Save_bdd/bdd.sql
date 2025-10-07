START TRANSACTION;

-- 1. Créer d'abord la table Matieres
CREATE TABLE IF NOT EXISTS `Matieres` (
	`NoMat`	INTEGER AUTO_INCREMENT,
	`NomMat`	VARCHAR(20) NOT NULL CHECK(`NomMat` IN ('Informatique', 'Electronique', 'Télécoms', 'Réseaux', 'Anglais', 'Culture & Com', 'Mathématiques')),
	`Prof`	TEXT CHECK(`Prof` IN ('Gouezel', 'Durand', 'Kerhervé', 'Tuhal', 'Grimal', 'Hill', 'Brault', 'Lecharpentier')),
	PRIMARY KEY(`NoMat`)
);

-- 2. Puis la table Notes
CREATE TABLE IF NOT EXISTS Notes (
	`NoNote`	INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`NomNote`	VARCHAR(20) NOT NULL CHECK (`NomNote` IN ('TP' ,'QCM', 'Oral','DS','TestTP'))	
);

-- 3. Enfin la table NotesMatieres qui dépend des deux précédentes
CREATE TABLE IF NOT EXISTS `NotesMatieres` (
	`noMat`	INTEGER NOT NULL,
	`noNote`	INTEGER NOT NULL,
	`Coefficient`	INTEGER NOT NULL CHECK(`Coefficient` >= 1 AND `Coefficient` <= 5),
	PRIMARY KEY(`noMat`,`noNote`),
	
	CONSTRAINT `fk_Matieres` FOREIGN KEY(`noMat`) REFERENCES `Matieres`(`NoMat`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_Notes` FOREIGN KEY(`noNote`) REFERENCES `Notes`(`NoNote`) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Insertion des données dans Matieres
INSERT INTO `Matieres` VALUES(1,'Informatique','Durand');
INSERT INTO `Matieres` VALUES(2,'Electronique','Lecharpentier');
INSERT INTO `Matieres` VALUES(3,'Télécoms','Tuhal');
INSERT INTO `Matieres` VALUES(4,'Réseaux','Grimal');
INSERT INTO `Matieres` VALUES(5,'Anglais','Hill');
INSERT INTO `Matieres` VALUES(6,'Culture & Com','Kerhervé');
INSERT INTO `Matieres` VALUES(7,'Mathématiques','Brault');

-- Insertion des données dans Notes
INSERT INTO `Notes` VALUES(1,'TP');
INSERT INTO `Notes` VALUES(2,'DS');
INSERT INTO `Notes` VALUES(3,'QCM');
INSERT INTO `Notes` VALUES(4,'TestTP');
INSERT INTO `Notes` VALUES(5,'Oral');

-- Insertion des données dans NotesMatieres
INSERT INTO `NotesMatieres` VALUES(7,1,2);
INSERT INTO `NotesMatieres` VALUES(4,2,5);
INSERT INTO `NotesMatieres` VALUES(1,2,3);
INSERT INTO `NotesMatieres` VALUES(7,3,1);
INSERT INTO `NotesMatieres` VALUES(6,4,2);
INSERT INTO `NotesMatieres` VALUES(7,5,3);
INSERT INTO `NotesMatieres` VALUES(6,1,1);
INSERT INTO `NotesMatieres` VALUES(5,5,2);
INSERT INTO `NotesMatieres` VALUES(2,4,4);
INSERT INTO `NotesMatieres` VALUES(3,2,3);
INSERT INTO `NotesMatieres` VALUES(5,2,2);
INSERT INTO `NotesMatieres` VALUES(4,3,2);

COMMIT;