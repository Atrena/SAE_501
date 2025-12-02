<?php

// --- CONFIGURATION API ---
// IMPORTANT : Si vous utilisez Docker, mettez le nom du service (ex: http://api-sae:8000)
// Si vous testez en local sans conteneur, mettez http://127.0.0.1:8000
define('API_URL', 'http://127.0.0.1:8000'); 

// --- Connexion BDD (UNIQUEMENT POUR AUTHENTIFICATION) ---
try {
    $pdo_db = new PDO(
        'mysql:host=mariadb_sae;port=3306;dbname=saedb',
        'saeuser',
        'lannion'
    );
    $pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // On laisse continuer si la BDD échoue, pour que l'API puisse prendre le relais sur le reste
}

//**************************** FONCTION GENERIQUE CURL ****************************
function callAPI($method, $url, $data = false)
{
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case "DELETE":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
            break;
        default: // GET
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Options obligatoires
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
    ));

    $result = curl_exec($curl);
    
    if(!$result){
        return null;
    }
    
    curl_close($curl);
    
    return json_decode($result, true);
}

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
        . "\n"
        . "</script>\n";
}

//****************************Authentification****************************
function authentification($mail, $pass)
{
    $retour = false;
    global $pdo_db;
    
    if (!$pdo_db) return false;

    $madb = $pdo_db;
    $mail = $madb->quote($mail);
    $pass = $madb->quote($pass);
    $requete = "SELECT EMAIL,PASS,STATUT FROM utilisateurs WHERE EMAIL = " . $mail . " AND PASS = " . $pass;
    
    try {
        $resultat = $madb->query($requete);
        $tableau_assoc = $resultat->fetchAll(PDO::FETCH_ASSOC);
        if (sizeof($tableau_assoc) != 0) {
            $retour = true;
            $_SESSION['statut'] = $tableau_assoc[0]['STATUT'];
        }
    } catch (Exception $e) {
        return false;
    }
    return $retour;
}

//****************************Suppression (VIA API)****************************

function SuppressionNotes($note, $matiere, $coef){
<<<<<<< Updated upstream
    // $note est l'ID de la note (noNote)
    // $matiere est l'ID de la matière (noMat)
    // L'API utilise DELETE /NoteMatieres/{noMat}/{noNote}
    $endpoint = "/NoteMatieres/" . $matiere . "/" . $note;
    $result = callAPI('DELETE', $endpoint);

    if ($result !== false) {
=======
    // note et matiere sont ici les ID (noNote et noMat)
    
    // Appel API : DELETE /NoteMatieres/{noMat}/{noNote}
    $url = API_URL . "/NoteMatieres/" . $matiere . "/" . $note;
    $result = callAPI('DELETE', $url);

    if (isset($result['message']) && strpos($result['message'], 'réussie') !== false) {
>>>>>>> Stashed changes
        return 1;
    }
    return 0;
}

//****************************Ajouter (VIA API)****************************

function ajouterNote($noMat, $noNote, $coefficient)
{
    $data = array(
<<<<<<< Updated upstream
        "noMat" => intval($noMat),
        "noNote" => intval($noNote),
        "Coefficient" => intval($coefficient)
    );

    $result = callAPI('POST', '/NoteMatieres/', $data);

    if ($result !== false) {
=======
        "noMat" => (int)$noMat,
        "noNote" => (int)$noNote,
        "Coefficient" => (int)$coefficient
    );

    $url = API_URL . "/NoteMatieres/";
    $result = callAPI('POST', $url, $data);

    if (isset($result['message']) && strpos($result['message'], 'réussie') !== false) {
>>>>>>> Stashed changes
        return 1;
    }
    return 0;
}

//****************************Liste (VIA API)****************************

function ListeNote()
{
    $retour = false;
    
    $url = API_URL . "/NoteMatieres/";
    $tableau_assoc = callAPI('GET', $url);

    if ($tableau_assoc != null && !isset($tableau_assoc['detail'])) {
        afficheTableau($tableau_assoc);
        $retour = true;
    } else {
        echo "<p>Erreur ou aucune donnée reçue de l'API.</p>";
    }
    return $retour;
}

function afficheTableau($tab)
{
    if (empty($tab)) {
        echo "<p class='m-5'>Aucune donnée à afficher.</p>";
        return;
    }

    echo '<div class="container-fluid d-flex justify-content-center mb-5"><table class="border">';
    echo '<tr class="border">'; 
    foreach ($tab[0] as $colonne => $valeur) {
        echo "<th class='border'><p class='m-5'>$colonne</p></th>";
    }
    echo "</tr>\n";
    
    foreach ($tab as $ligne) {
        echo '<tr class="border">';
        foreach ($ligne as $cellule) {
            echo "<td class='border'><p class='m-5'>$cellule</p></td>";
        }
        echo "</tr>\n";
    }
    echo '</table></div>';
}

//****************************Modification (VIA API)****************************

function modificationNotes($note, $matiere, $coef){
<<<<<<< Updated upstream
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
=======
    // Récupération des IDs originaux pour l'URL
    if (isset($_POST['old_noNote']) && isset($_POST['old_noMat'])) {
        $old_noNote = $_POST['old_noNote'];
        $old_noMat = $_POST['old_noMat'];
    } else {
        return 0; // Impossible de modifier sans les anciens ID
    }

    // Conversion des valeurs
    $newCoef = (int)$coef;
    
    // On suppose ici que l'utilisateur ne change que le coefficient via ce formulaire simplifié
    // Sinon il faudrait gérer les nouveaux ID
    $new_noMat = (int)$old_noMat; 
    $new_noNote = (int)$old_noNote;

    $data = array(
        "noMat" => $new_noMat,
        "noNote" => $new_noNote,
        "Coefficient" => $newCoef
    );

    $url = API_URL . "/NoteMatieres/" . $old_noMat . "/" . $old_noNote;
    $result = callAPI('PUT', $url, $data);

    if (isset($result['message']) && strpos($result['message'], 'réussie') !== false) {
        return 1;
    }
    return 0;
>>>>>>> Stashed changes
}

//****************************Filtrage (VIA API)****************************

function FiltreParNote($note)
{
    $retour = false;
    $url = API_URL . "/Notes/" . $note;
    $tableau_assoc = callAPI('GET', $url);

    if ($tableau_assoc != null && !isset($tableau_assoc['detail'])) {
        afficheTableau($tableau_assoc);
        $retour = true;
    } else {
        echo "<p>Aucun résultat.</p>";
    }
    
    return $retour;
}

function FiltreParMatiere($matiere)
{
    $retour = false;
    $url = API_URL . "/Matieres/" . $matiere;
    $tableau_assoc = callAPI('GET', $url);

    if ($tableau_assoc != null && !isset($tableau_assoc['detail'])) {
        afficheTableau($tableau_assoc);
        $retour = true;
    } else {
        echo "<p>Aucun résultat.</p>";
    }
    return $retour;
}
?>