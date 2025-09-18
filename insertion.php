<?php
include_once("./fonction.php");
include_once("formulaire.php");
session_start();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <script src="./js/fonction.js" type="text/javascript"></script>
    <title>WEB2 TD56 TP45 PHP Une Application BDD Insertion ETU</title>
</head>

<body>
    <article>
        <?php
        Menu();
        if (empty($_SESSION)) {
            $_SESSION = array();
            session_destroy();
            $loggedOut = true;
            redirect('./connexion.php',0);
        } else if ($_SESSION['statut'] == 'user'){
            $loggedOut = true;
            redirect('./index.php',0);
        } else {
            echo("<h1 class='m-5'>Bienvenue sur la page d'insertion ".$_SESSION['login']."</h1>");
        }
        FormulaireAjoutNotes();
        if (isset($_POST['noMat'], $_POST['noNote'], $_POST['Coefficient'])) {
            // Insertion de la note
            $insertion_reussie = ajouterNote($_POST['noMat'], $_POST['noNote'], $_POST['Coefficient']);
            if ($insertion_reussie) {
                echo 'Insertion réussie de la note';
                ListeNote();
            } else {
                echo '<p class="erreur">Note déjà existante</p>';
            }
        }
        if (!empty($_GET) && isset($_GET['action']) && $_GET['action'] == 'logout') {
            $_SESSION = array();
            session_destroy();
            redirect('index.php', 0);
        }
        ?>
        <p id="erreur" class="erreur"></p>
    </article>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>

</html>