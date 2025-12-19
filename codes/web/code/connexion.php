<?php
session_start();
include_once("fonction.php");
include_once("formulaire.php");


if (isset($_POST['connect'])) {
    if (authentification($_POST["login"], $_POST["pass"])) {
        // Le jeton est déjà mis en session par la fonction authentification()
        header('Location: index.php');
        exit();
    } else {
        $error_message = "Identifiant ou mot de passe incorrect (via API).";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="js/fonction.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <title>Projet_WEB_SAE23_HAVARD_JEGU</title>
</head>
<body>
    <div class="m-5">
    <form class="border p-5" method="POST">
        <h1 class="m-5">Connexion</h1>
        <label class="ms-5" for="mail">Identifiant</label>
        <input class="me-5" type="email" name="login" id="mail" required>
        <label for="password">Mot de Passe</label>
        <input class="me-5" type="password" onkeyup="verifPass()" name="pass" id="password" required>
        <?php if (isset($error_message)) echo "<p class='red'>$error_message</p>"; ?>
        <p class="erreur" id="erreur"></p>
        <input class="m-5" type="submit" name="connect" value="Se connecter">
    </form>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    </div>
</body>
</html>