<?php

$pdo_db = new PDO(
        'mysql:host=mariadb_sae;port=3306;dbname=saedb',
        'saeuser',
        'lannion'
    );

// try {
//     // Connexion à la base "bdd"
//     $pdo_bdd = new PDO(
//         'mysql:host=mariadb_sae;port=3306;dbname=saedb',
//         'saeuser',
//         'lannion'
//     );
//     $pdo_bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//     // Connexion à la base "compte"
//     $pdo_compte = new PDO(
//         'mysql:host=mariadb_sae;port=3306;dbname=saedb',
//         'saeuser',
//         'lannion'
//     );
//     $pdo_compte->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// } catch (PDOException $e) {
//     die("Erreur de connexion : " . $e->getMessage());
// }

// Configuration de l'API
define('API_BASE_URL', 'http://127.0.0.1:8000');

function callAPI($method, $endpoint, $data = false) {
    $url = API_BASE_URL . $endpoint;
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => $method,
            'ignore_errors' => true
        )
    );
    if ($data) {
        $options['http']['content'] = json_encode($data);
    }
    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return false;
    }

    // Vérification du code HTTP
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('#^HTTP/\d\.\d (\d+)#', $header, $matches)) {
                $status_code = intval($matches[1]);
                if ($status_code >= 200 && $status_code < 300) {
                    return $result;
                }
            }
        }
    }
    return false;
}

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

	global $pdo_db;
	$madb = $pdo_db;
	//$madb = new PDO('sqlite:bdd/comptes.sqlite');
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
    // $note est l'ID de la note (noNote)
    // $matiere est l'ID de la matière (noMat)
    // L'API utilise DELETE /NoteMatieres/{noMat}/{noNote}
    $endpoint = "/NoteMatieres/" . $matiere . "/" . $note;
    $result = callAPI('DELETE', $endpoint);

    if ($result !== false) {
        return 1;
    }
    return 0;
}

//****************************Ajouter****************************

function ajouterNote($noMat, $noNote, $coefficient)
{
    $data = array(
        "noMat" => intval($noMat),
        "noNote" => intval($noNote),
        "Coefficient" => intval($coefficient)
    );

    $result = callAPI('POST', '/NoteMatieres/', $data);

    if ($result !== false) {
        return 1;
    }
    return 0;
}

//****************************Liste****************************

function ListeNote()
{
	$retour = false;
	global $pdo_db;
	$madb = $pdo_db;
	try {
		//$madb = new PDO('sqlite:bdd/bdd.db');
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
	global $pdo_db;
	$madb = $pdo_db;
	try{
		// Récupération des IDs à partir des noms
		$rq_notes = "SELECT NoNote FROM Notes WHERE NomNote='$note'";
		$rq_matieres = "SELECT NoMat FROM Matieres WHERE NomMat='$matiere'";
		$resultat_noNote = $madb -> query($rq_notes);
		$noNote = $resultat_noNote->fetch(PDO::FETCH_ASSOC)['NoNote'];
		$resultat_noMat = $madb -> query($rq_matieres);
		$noMat = $resultat_noMat->fetch(PDO::FETCH_ASSOC)['NoMat'];

		$old_noNote = $_POST['old_noNote'];
		$old_noMat = $_POST['old_noMat'];
		// $old_coef = $_POST['old_coef']; // Pas utilisé par l'API pour l'identification

		// Appel API
		$endpoint = "/NoteMatieres/" . $old_noMat . "/" . $old_noNote;
		$data = array(
			"noMat" => intval($noMat),
			"noNote" => intval($noNote),
			"Coefficient" => intval($coef)
		);

		$result = callAPI('PUT', $endpoint, $data);

		if ($result !== false) {
			$retour = 1;
		}
	}
	catch (Exception $e) { echo "Erreur " . $e->getMessage(); }
	return $retour;
}

//****************************Filtrage****************************

function FiltreParNote($note)
{
	$retour = false;
	global $pdo_db;
	$madb = $pdo_db;
	try {
		//$file = dirname(__FILE__);
		//$madb = new PDO('sqlite:'.$file.DIRECTORY_SEPARATOR.'bdd'.DIRECTORY_SEPARATOR.'bdd.db'); 
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
	global $pdo_db;
	$madb = $pdo_db;
	try {
		//$file = dirname(__FILE__);
		//$madb = new PDO('sqlite:'.$file.DIRECTORY_SEPARATOR.'bdd'.DIRECTORY_SEPARATOR.'bdd.db'); 
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
?>