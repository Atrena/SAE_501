PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE Notes (
	NoNote	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	NomNote	VARCHAR(20) NOT NULL CHECK (NomNote IN ('TP' ,'QCM', 'Oral','DS','TestTP'))	
);
INSERT INTO Notes VALUES(1,'TP');
INSERT INTO Notes VALUES(2,'DS');
INSERT INTO Notes VALUES(3,'QCM');
INSERT INTO Notes VALUES(4,'TestTP');
INSERT INTO Notes VALUES(5,'Oral');
CREATE TABLE IF NOT EXISTS "NotesMatieres" (
	noMat	char(5) NOT NULL ,
	noNote	char(5) NOT NULL ,
	Coefficient	INTEGER NOT NULL CHECK(Coefficient >= 1 and Coefficient <= 5),
	PRIMARY KEY("noMat","noNote"),
	
  CONSTRAINT fk_Matieres FOREIGN KEY (noMat) REFERENCES Matieres (noMat) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_Notes FOREIGN KEY (noNote) REFERENCES Notes (noNote) ON DELETE CASCADE ON UPDATE CASCADE
);
INSERT INTO NotesMatieres VALUES('7','1',2);
INSERT INTO NotesMatieres VALUES('4','2',5);
INSERT INTO NotesMatieres VALUES('1','2',3);
INSERT INTO NotesMatieres VALUES('7','3',1);
INSERT INTO NotesMatieres VALUES('6','4',2);
INSERT INTO NotesMatieres VALUES('7','5',3);
INSERT INTO NotesMatieres VALUES('6','1',1);
INSERT INTO NotesMatieres VALUES('5','5',2);
INSERT INTO NotesMatieres VALUES('2','4',4);
INSERT INTO NotesMatieres VALUES('3','2',3);
INSERT INTO NotesMatieres VALUES('5','2',2);
INSERT INTO NotesMatieres VALUES('4','3',2);
CREATE TABLE IF NOT EXISTS "Matieres" (
	"NoMat"	INTEGER,
	"NomMat"	VARCHAR(20) NOT NULL CHECK("NomMat" IN ('Informatique', 'Electronique', 'Télécoms', 'Réseaux', 'Anglais', 'Culture & Com', 'Mathématiques')),
	"Prof"	TEXT CHECK("Prof" IN ('Gouezel', 'Durand', 'Kerhervé', 'Tuhal', 'Grimal', 'Hill', 'Brault', 'Lecharpentier')),
	PRIMARY KEY("NoMat" AUTOINCREMENT)
);
INSERT INTO Matieres VALUES(1,'Informatique','Durand');
INSERT INTO Matieres VALUES(2,'Electronique','Lecharpentier');
INSERT INTO Matieres VALUES(3,'Télécoms','Tuhal');
INSERT INTO Matieres VALUES(4,'Réseaux','Grimal');
INSERT INTO Matieres VALUES(5,'Anglais','Hill');
INSERT INTO Matieres VALUES(6,'Culture & Com','Kerhervé');
INSERT INTO Matieres VALUES(7,'Mathématiques','Brault');
DELETE FROM sqlite_sequence;
INSERT INTO sqlite_sequence VALUES('Notes',5);
INSERT INTO sqlite_sequence VALUES('Matieres',7);
COMMIT;
