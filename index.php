<?php
include_once("./fonction.php");
include_once("formulaire.php");
session_start();
$loggedOut = false;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="js/fonction.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
</head>

<body class="">
    <?php
    if (empty($_SESSION)) {
        $_SESSION = array();
        session_destroy();
        $loggedOut = true;
        redirect('./connexion.php', 0);
    } else {
        Menu();
        echo ('<h1 class="m-5">Bienvenue ' . $_SESSION['login'] . '</h1>');
    }

    if (!empty($_GET) && isset($_GET['action']) && $_GET['action'] == 'logout') {
        $_SESSION = array();
        session_destroy();
        $loggedOut = true;
        redirect('connexion.php', 0);
    }

    if (!isset($_GET['action']) or empty($_GET)) {
        ListeNote();
    }

    if (isset($_GET['action'])) {
        if ($_GET['action'] == 'filtre') {
                FormulaireFiltreNote();
                FormulaireFiltreMatiere();
                ?>
            <div id="vide"></div>
    <?php
        }
    }

    ?>
    <br>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>

</html>