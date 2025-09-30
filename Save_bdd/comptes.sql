PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS "utilisateurs" (
	"EMAIL"	VARCHAR NOT NULL,
	"PASS"	VARCHAR NOT NULL,
	"STATUT"	VARCHAR NOT NULL DEFAULT 'Etudiant',
	PRIMARY KEY("EMAIL")
);
INSERT INTO utilisateurs VALUES('superuser@test.fr','L@nnion','admin');
INSERT INTO utilisateurs VALUES('simpleuser@test.fr','L@nnion','user');
COMMIT;
