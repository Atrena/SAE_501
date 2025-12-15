<?php
session_start();
include_once("../fonction.php");
include_once("../formulaire.php");
if(isset($_POST['captcha'])){
    if($_POST['captcha']==$_SESSION['code']){
        if(modificationNotes($_POST['nomNote'], $_POST['nomMat'], $_POST['coef'])){
            echo '<p class="ms-5">Modification réussi</p>';
        } else {
            echo '<p class="erreur ms-5">Note déjà existante</p>';
        }
    } else {
        echo "<p class='erreur ms-5'>Code incorrect</p>"; }
} 
?>