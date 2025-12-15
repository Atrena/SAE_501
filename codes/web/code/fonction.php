<?php

$pdo_db = new PDO(
    'mysql:host=mariadb_sae;port=3306;dbname=saedb',
    'saeuser',
    'lannion'
);

// Configuration de l'API
// Si vous utilisez Docker, mettez le nom du service (ex: http://api-sae:8000)
// Si vous êtes en local sans conteneur pour l'API, mettez http://127.0.0.1:8000
define('API_BASE_URL', 'http://sae_api:8000');

// Fonction améliorée pour voir les erreurs
function callAPI($method, $endpoint, $data = false) {
    $url = API_BASE_URL . $endpoint;
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => $method,
            'ignore_errors' => true // Important pour récupérer le message d'erreur 400/500
        )
    );
    if ($data) {
        $options['http']['content'] = json_encode($data);
    }
    
    $context  = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        echo "<div style='color:red; font-weight:bold;'>Erreur critique : Impossible de joindre l'API ($url)</div>";
        return false;
    }

    // Analyse des en-têtes HTTP pour détecter les erreurs (400, 404, 500)
    $status_code = 200; // Par défaut
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('#^HTTP/\d\.\d (\d+)#', $header, $matches)) {
                $status_code = intval($matches[1]);
            }
        }
    }

    // Si c'est une erreur (4xx ou 5xx), on l'affiche pour déboguer
    if ($status_code >= 400) {
        $json_error = json_decode($result, true);
        $msg = isset($json_error['detail']) ? $json_error['detail'] : $result;
        
        // AFFICHE L'ERREUR REELLE A L'ECRAN
        echo "<div style='background-color:#f8d7da; color:#721c24; padding:10px; margin:10px; border:1px solid #f5c6cb;'>";
        echo "<strong>Erreur API ($status_code) :</strong> " . htmlspecialchars($msg);
        echo "</div>";
        
        return false;
    }

    // Si tout va bien, on retourne le JSON décodé
    return json_decode($result, true); 
}

//****************************Redirection****************************

function redirect($url, $tps)
{
    $temps = $tps * 1000;
    echo "<script type=\"text/javascript\">\n"
        . "setTimeout(function() { window.location='" . $url . "'; }, " . $temps . ");\n"
        . "</script>\n";
}

//****************************Authentification****************************
// On garde PDO ici car l'API ne gère pas encore les logins
function authentification($mail, $pass)
{
    $retour = false;
    global $pdo_db;
    $madb = $pdo_db;
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

//****************************Suppression (Via API)****************************

function SuppressionNotes($note, $matiere, $coef){
    // DELETE /NoteMatieres/{noMat}/{noNote}
    $endpoint = "/NoteMatieres/" . $matiere . "/" . $note;
    $result = callAPI('DELETE', $endpoint);

    if ($result !== false) {
        return 1;
    }
    return 0;
}

//****************************Ajouter (Via API)****************************

function ajouterNote($noMat, $noNote, $coefficient)
{
    $data = array(
        "noMat" => intval($noMat),
        "noNote" => intval($noNote),
        "Coefficient" => intval($coefficient)
    );

    // Si cette combinaison (noMat, noNote) existe déjà, l'API renverra une erreur 400 (Duplicate entry)
    // Mon callAPI modifié affichera cette erreur à l'écran.
    $result = callAPI('POST', '/NoteMatieres/', $data);

    if ($result !== false) {
        return 1;
    }
    return 0;
}

//****************************Liste (Via API maintenant !)****************************

function ListeNote()
{
    // Appel API : GET /NoteMatieres/
    // Attention : l'API Python doit renvoyer les noms (NomMat, NomNote) grâce aux JOIN SQL.
    // Si votre API renvoie bien une liste de dictionnaires, ça marchera directement.
    $tableau_assoc = callAPI('GET', '/NoteMatieres/');

    if ($tableau_assoc !== false && !empty($tableau_assoc)) {
        afficheTableau($tableau_assoc);
        return true;
    }
    return false;
}

function afficheTableau($tab)
{
    if (empty($tab)) {
        echo "<p class='m-5'>Aucune donnée.</p>";
        return;
    }

    echo '<div class="container-fluid d-flex justify-content-center mb-5"><table class="border">';
    echo '<tr class="border">';
    // Affichage des en-têtes
    foreach ($tab[0] as $colonne => $valeur) {
        echo "<th class='border'><p class='m-5'>" . htmlspecialchars($colonne) . "</p></th>";
    }
    echo "</tr>\n";
    // Affichage des lignes
    foreach ($tab as $ligne) {
        echo '<tr class="border">';
        foreach ($ligne as $cellule) {
            echo "<td class='border'><p class='m-5'>" . htmlspecialchars($cellule) . "</p></td>";
        }
        echo "</tr>\n";
    }
    echo '</table></div>';
}

//****************************Modification (Via API)****************************

function modificationNotes($note, $matiere, $coef){
    
    // NOTE : Ici, $note et $matiere sont probablement des NOMS venant du formulaire.
    // L'API a besoin des ID. 
    // Le formulaire de modification envoie aussi des champs cachés 'old_noNote' et 'old_noMat'.
    
    if (isset($_POST['old_noNote']) && isset($_POST['old_noMat'])) {
        $old_noNote = $_POST['old_noNote'];
        $old_noMat = $_POST['old_noMat'];
        
        // On suppose qu'on garde les mêmes IDs (on change juste le coef)
        // Si vous voulez changer de matière, il faut récupérer le nouvel ID correspondant au nom choisi
        // Pour simplifier l'exemple API, on utilise les vieux ID comme nouveaux ID
        $new_noMat = intval($old_noMat);
        $new_noNote = intval($old_noNote);
        
        $data = array(
            "noMat" => $new_noMat,
            "noNote" => $new_noNote,
            "Coefficient" => intval($coef)
        );

        $endpoint = "/NoteMatieres/" . $old_noMat . "/" . $old_noNote;
        $result = callAPI('PUT', $endpoint, $data);

        if ($result !== false) {
            return 1;
        }
    } else {
        echo "<p>Erreur: ID manquants pour la modification</p>";
    }
    return 0;
}

//****************************Filtrage (Via API)****************************

function FiltreParNote($note)
{
    $endpoint = "/Notes/" . $note; // Assurez-vous que cette route existe dans main.py
    // Sinon utiliser /NoteMatieres/ et filtrer en PHP, ou créer une route /NoteMatieres/Note/{id}
    
    // Si votre API n'a pas de filtre spécifique, on peut tricher en réutilisant ListeNote pour l'instant
    // Mais l'idéal est :
    // $tableau_assoc = callAPI('GET', '/NoteMatieres/?noNote=' . $note); 
    
    // Comme votre main.py précédent avait GET /NoteMatieres/{noMat}/{noNote},
    // il faudrait ajouter une route de recherche dans main.py pour filtrer par une seule colonne.
    // Pour l'instant, je désactive le filtre API pour éviter le crash si la route n'existe pas :
    echo "Filtre via API à implémenter dans main.py";
    return false;
}

function FiltreParMatiere($matiere)
{
    echo "Filtre via API à implémenter dans main.py";
    return false;
}
?>