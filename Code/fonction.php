<?php

try {
    // Connexion à la base "bdd"
    $pdo_bdd = new PDO(
        'mysql:host=172.17.0.2;port=3306;dbname=bdd',
        'saeuser',
        'lannion'
    );
    $pdo_bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Connexion à la base "compte"
    $pdo_compte = new PDO(
        'mysql:host=172.17.0.2;port=3306;dbname=compte',
        'saeuser',
        'lannion'
    );
    $pdo_compte->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>


//****************************Redirection****************************

function redirect($url, $tps)
{
	$temps = $tps * 1000;

	echo "<script type=\"text/javascript\">\n"
		. "<!--\n"
		. "\n"
		. "function redirect() {\n"
		. "window.location='" . $url . "'\n"
		. "}\n"
		. "setTimeout('redirect()','" . $temps . "');\n"
		. "\n"
		. "// -->\n"
		. "</script>\n";
}

//****************************Authentification****************************

function authentification($mail, $pass)
{
	$retour = false;
	$madb = new PDO('sqlite:bdd/comptes.sqlite');
	$mail = $madb->quote($mail);
	$pass = $madb->quote($pass);
	$requete = "SELECT EMAIL,PASS,STATUT FROM utilisateurs WHERE EMAIL = " . $mail . " AND PASS = " . $pass;
	$resultat = $madb->query($requete);
	$tableau_assoc = $resultat->fetchAll(PDO::FETCH_ASSOC);
	if (sizeof($tableau_assoc) != 0) {
		$retour = true;
		$_SESSION['statut'] = $tableau_assoc[0]['STATUT'];
	}
	return $retour;
}

//****************************Suppression****************************

function SuppressionNotes($note, $matiere, $coef){
	$retour=0;
		try{
			$madb = new PDO('sqlite:bdd/bdd.db');
			/*
			$rq_notes = "SELECT NoNote FROM Notes WHERE NomNote='$note'";
			$rq_matieres = "SELECT NoMat FROM Matieres WHERE NomMat='$matiere'";
			$resultat_noNote = $madb -> query($rq_notes);
			$noNote = $resultat_noNote->fetch(PDO::FETCH_ASSOC)['NoNote'];
			$resultat_noMat = $madb -> query($rq_matieres);
			$noMat = $resultat_noMat->fetch(PDO::FETCH_ASSOC)['NoMat'];
			*/
			$rq = "DELETE FROM NotesMatieres WHERE noNote='$note' AND noMat='$matiere' AND Coefficient='$coef'";
			$resultat = $madb->exec($rq);
		}
	catch (Exception $e) {	 echo "Erreur " . $e->getMessage(); }
			if (!empty($resultat)) {
				$retour = 1;
		}
		return $retour;
}

//****************************Ajouter****************************

function ajouterNote($noMat, $noNote, $coefficient)
{
	$retour = 0;
	try {
		$madb = new PDO('sqlite:bdd/bdd.db');
		$rq = "INSERT INTO NotesMatieres VALUES ($noMat, $noNote, $coefficient)";
		$resultat = $madb->query($rq);
	} catch (Exception $e) {
		//echo "<p class='erreur'>Erreur de la base de donnée : $e</p>";
	}
	if (!empty($resultat)) {
		$retour = 1;
	}
	return $retour;
}

//****************************Liste****************************

function ListeNote()
{
	$retour = false;
	try {
		$madb = new PDO('sqlite:bdd/bdd.db');
		$rq = "SELECT Notes.NomNote, Matieres.NomMat, Matieres.Prof, NotesMatieres.Coefficient FROM NotesMatieres INNER JOIN Notes ON Notes.NoNote = NotesMatieres.noNote INNER JOIN Matieres ON Matieres.NoMat = NotesMatieres.noMat;";
		$resultat = $madb->query($rq);
		$tableau_assoc = $resultat->fetchAll(PDO::FETCH_ASSOC);
		if ($tableau_assoc != null) {
			afficheTableau($tableau_assoc);
			$retour = true;
		}
	} catch (Exception $e) {
		echo "<p>Erreur lors de la connexion à la BDD ";
	}
	return $retour;
}

function afficheTableau($tab)
{
	echo '<div class="container-fluid d-flex justify-content-center mb-5"><table class="border">';
	echo '<tr class="border">'; // les entetes des colonnes qu'on lit dans le premier tableau par exemple
	foreach ($tab[0] as $colonne => $valeur) {
		echo "<th class='border'><p class='m-5'>$colonne</p></th>";
	}
	echo "</tr>\n";
	// le corps de la table
	foreach ($tab as $ligne) {
		echo '<tr class="border">';
		foreach ($ligne as $cellule) {
			echo "<td class='border'><p class='m-5'>$cellule</p></td>";
		}
		echo "</tr>\n";
	}
	echo '</table></div>';
}

//****************************Modification****************************

function modificationNotes($note, $matiere, $coef){
	$retour=0;
		try{
			$file = dirname(__FILE__);
			$madb = new PDO('sqlite:'.$file.DIRECTORY_SEPARATOR.'bdd'.DIRECTORY_SEPARATOR.'bdd.db'); 
			$rq_notes = "SELECT NoNote FROM Notes WHERE NomNote='$note'";
			$rq_matieres = "SELECT NoMat FROM Matieres WHERE NomMat='$matiere'";
			$resultat_noNote = $madb -> query($rq_notes);
			$noNote = $resultat_noNote->fetch(PDO::FETCH_ASSOC)['NoNote'];
			$resultat_noMat = $madb -> query($rq_matieres);
			$noMat = $resultat_noMat->fetch(PDO::FETCH_ASSOC)['NoMat'];
			$old_noNote = $_POST['old_noNote'];
			$old_noMat = $_POST['old_noMat'];
			$old_coef = $_POST['old_coef'];
			$rq = "UPDATE NotesMatieres SET noNote=$noNote, noMat=$noMat, Coefficient=$coef WHERE noNote='$old_noNote' AND noMat='$old_noMat' AND Coefficient='$old_coef' ";
			$resultat = $madb->exec($rq);
		}
		catch (Exception $e) {		echo "Erreur " . $e->getMessage();		}
			if (!empty($resultat)) {
				$retour = 1;
		}
		return $retour;
}

//****************************Filtrage****************************

function FiltreParNote($note)
{
	$retour = false;
	try {
		$file = dirname(__FILE__);
		$madb = new PDO('sqlite:'.$file.DIRECTORY_SEPARATOR.'bdd'.DIRECTORY_SEPARATOR.'bdd.db'); 
		$rq = "SELECT Notes.NomNote, Matieres.NomMat, Matieres.Prof, NotesMatieres.Coefficient FROM NotesMatieres INNER JOIN Notes ON Notes.NoNote = NotesMatieres.noNote INNER JOIN Matieres ON Matieres.NoMat = NotesMatieres.noMat WHERE Notes.NoNote = $note;";
		$resultat = $madb->query($rq);
		$tableau_assoc = $resultat->fetchAll(PDO::FETCH_ASSOC);
		if ($tableau_assoc != null) {
			afficheTableau($tableau_assoc);
			$retour = true;
		}
	} catch (Exception $e) {
		echo "<p>Erreur lors de la connexion à la BDD ";
		var_dump($e);
	}
	
	return $retour;
}

function FiltreParMatiere($matiere)
{
	$retour = false;
	try {
		$file = dirname(__FILE__);
		$madb = new PDO('sqlite:'.$file.DIRECTORY_SEPARATOR.'bdd'.DIRECTORY_SEPARATOR.'bdd.db'); 
		$rq = "SELECT Notes.NomNote, Matieres.NomMat, Matieres.Prof, NotesMatieres.Coefficient FROM NotesMatieres INNER JOIN Notes ON Notes.NoNote = NotesMatieres.noNote INNER JOIN Matieres ON Matieres.NoMat = NotesMatieres.noMat WHERE Matieres.NoMat = $matiere;";
		$resultat = $madb->query($rq);
		$tableau_assoc = $resultat->fetchAll(PDO::FETCH_ASSOC);
		if ($tableau_assoc != null) {
			afficheTableau($tableau_assoc);
			$retour = true;
		}
	} catch (Exception $e) {
		echo "<p>Erreur lors de la connexion à la BDD ";
	}
	return $retour;
}