<?php
include_once("../fonction.php");
include_once("../formulaire.php");
header('Content-type: text/plain'); // on retourne du texte brut// pour montrer les différentes phases
?>
<form action="../index.php?action=filtre" method="post">
    <?php
    if (isset($_POST['noMat'])) {
        FiltreParMatiere($_POST['noMat']);
    }
    if (isset($_POST['noNote']))
        FiltreParNote($_POST['noNote']);
    ?>
</form>
<?php
?>