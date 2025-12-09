<?php

//****************************Menu****************************

function Menu()
{
?>
	<nav class="menu m-3 navbar bg-menu navbar-dark navbar-expand-xl">
		<div class="container-fluid">
			<div class="col-4 d-flex flex-row align-items-center">
				<a class="navbar-brand border p-2" href="index.php">Mat/Note</a>
			</div>
			<button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbar1" aria-controls="navbar1" aria-expanded="false" aria-label="Toggle Navigation"><span class="navbar-toggler-icon"></span></button>
			<div class="col-8 justify-content-end navbar-collapse collapse" id="navbar1">
				<ul class="navbar-nav">
					<li class="nav-item">
						<a class="nav-link" href="index.php">Liste de toutes les évaluations</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="index.php?action=filtre">Liste de toutes les évaluations par matières/notes</a>
					</li>
					<?php if ($_SESSION["statut"] == 'admin') { ?>
						<li class="nav-item">
							<a class="nav-link" href="insertion.php">Inserer une nouvelle évaluation</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="modification.php">Modifier une évaluation</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="suppression.php">Suprimer une évaluation</a>
						</li>
					<?php } ?>
					<li class="nav-item">
						<a class="nav-link" href="index.php?action=logout">Se deconnecter</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>
	<div class="separate m-3">
		<p>_</p>
	</div>
	<?php
}

//****************************Ajout Notes****************************

function FormulaireAjoutNotes()
{
	echo "<br/>";
	try {
        // Modification pour utiliser la connexion MySQL globale
        global $pdo_db;
        $madb = $pdo_db;

		// Requêtes SQL pour récupérer les matières et les types de notes
		$rq_matiere = "SELECT NoMat, NomMat FROM Matieres";
		$rq_type_eval = "SELECT NoNote, NomNote FROM Notes";

		$resultat_matiere = $madb->query($rq_matiere);
		$resultat_type_eval = $madb->query($rq_type_eval);

		// Récupération des résultats sous forme de tableau associatif
		$tableau_assoc_matiere = $resultat_matiere->fetchAll(PDO::FETCH_ASSOC);
		$tableau_assoc_type_eval = $resultat_type_eval->fetchAll(PDO::FETCH_ASSOC);

		if ($tableau_assoc_matiere && $tableau_assoc_type_eval) {
	?>
			<form class="ms-5 mb-5" method="post" onsubmit="return verifierCoefficient();">
				<fieldset>
					<label for="id_matiere">Matière : </label>
					<select class="me-5" id="id_matiere" name="noMat" required>
						<?php
						foreach ($tableau_assoc_matiere as $matiere) {
							echo '<option value="' . $matiere['NoMat'] . '">' . $matiere['NomMat'] . '</option>';
						}
						?>
					</select>
					<label for="id_type_eval">Type d'évaluation : </label>
					<select class="me-5" id="id_type_eval" name="noNote" required>
						<?php
						foreach ($tableau_assoc_type_eval as $type_eval) {
							echo '<option value="' . $type_eval['NoNote'] . '">' . $type_eval['NomNote'] . '</option>';
						}
						?>
					</select>
					<label for="id_Coefficient">Coefficient : </label>
					<input type="number" name="Coefficient" id="id_Coefficient" placeholder="Coefficient" required/>
					<input type="submit" value="Insérer" /><br>
					<p id="erreur" class="erreur"></p>
				</fieldset>
			</form>
		<?php
		}
	} catch (Exception $e) {
		echo "<p class='erreur'>Erreur lors de la connexion à la BDD : " . $e->getMessage() . "</p>";
	}
}

//****************************Choix Notes****************************

function FormulaireChoixNotes($choix)
{
	try {
        // Modification pour utiliser la connexion MySQL globale
        global $pdo_db;
        $madb = $pdo_db;

		$rq = "SELECT Notes.NomNote, Matieres.NomMat, Matieres.Prof, NotesMatieres.Coefficient FROM NotesMatieres INNER JOIN Notes ON Notes.NoNote = NotesMatieres.noNote INNER JOIN Matieres ON Matieres.NoMat = NotesMatieres.noMat;";
		$resultat = $madb->query($rq);
		$tableau_assoc = $resultat->fetchAll(PDO::FETCH_ASSOC);
		if ($tableau_assoc != null) {

		?>

			<div class="container-fluid d-flex justify-content-center mb-5">
				<table class="border">
					<tr class="border">
						<?php
						foreach ($tableau_assoc[0] as $colonne => $valeur) {
							echo "<th class='border'><p class='m-5'>$colonne</p></th>";
						}
						echo "<th></th>";
						?>
					</tr>
					<?php
					foreach ($tableau_assoc as $ligne) {
						$id = uniqid('form_');
						echo '<tr class="border">';
						foreach ($ligne as $cellule => $valeur) {
							echo "<td class='border'><p class='m-5'>$valeur</p>
								<input type='hidden' name='" . $cellule . "' value='" . $valeur . "' form='" . $id . "'/></td>";
						}
						echo "<td class='border'><form id='" . $id . "' action='modification.php' method='post'><input class='m-5 p-2' type='submit' value='$choix'/></form></td>";
						echo "</tr>\n";
					}
					?>
				</table>
			</div>

		<?php
		}
	} catch (Exception $e) {
		echo "<p>Erreur lors de la connexion à la BDD : " . $e->getMessage() . "</p>";
	}
	echo "<br/>";
}

//****************************Suppression Notes****************************

function FormulaireSuppressionNotes()
{
	echo "<br/>";
	try {
        // Modification pour utiliser la connexion MySQL globale
        global $pdo_db;
        $madb = $pdo_db;

		// Requêtes SQL pour récupérer les matières et les types de notes
		$rq_matiere = "SELECT NoMat, NomMat FROM Matieres";
		$rq_type_eval = "SELECT NoNote, NomNote FROM Notes";

		$resultat_matiere = $madb->query($rq_matiere);
		$resultat_type_eval = $madb->query($rq_type_eval);

		// Récupération des résultats sous forme de tableau associatif
		$tableau_assoc_matiere = $resultat_matiere->fetchAll(PDO::FETCH_ASSOC);
		$tableau_assoc_type_eval = $resultat_type_eval->fetchAll(PDO::FETCH_ASSOC);

		if ($tableau_assoc_matiere && $tableau_assoc_type_eval) {
		?>
			<form class="ms-5 mb-5" method="post" onsubmit="return verifierCoefficient();">
				<fieldset>
					<label for="id_type_eval">Type d'évaluation : </label>
					<select class="me-5" id="id_type_eval" name="noNote" required>
						<?php
						foreach ($tableau_assoc_type_eval as $type_eval) {
							echo '<option value="' . $type_eval['NoNote'] . '">' . $type_eval['NomNote'] . '</option>';
						}
						?>
					</select>
					<label for="id_matiere">Matière : </label>
					<select class="me-5" id="id_matiere" name="noMat" required>
						<?php
						foreach ($tableau_assoc_matiere as $matiere) {
							echo '<option value="' . $matiere['NoMat'] . '">' . $matiere['NomMat'] . '</option>';
						}
						?>
					</select>
					<label for="id_Coefficient">Coefficient : </label>
					<input class="me-5" type="number" name="Coefficient" id="id_Coefficient" placeholder="Coefficient" required/>
					<img src="image.php" onclick="this.src='image.php?' + Math.random();" alt="captcha" style="cursor:pointer;">
					<input type="text" name="captcha" />
					<input type="submit" value="Suprimer" /><br>
					<p id="erreur" class="erreur"></p>
				</fieldset>
			</form>
		<?php
		}
	} catch (Exception $e) {
		echo "<p class='erreur'>Erreur lors de la connexion à la BDD : " . $e->getMessage() . "</p>";
	}
}

//****************************Modification Notes****************************

function FormulaireModificationNotes($note, $matiere, $coef)
{
	try {
        // Modification pour utiliser la connexion MySQL globale
        global $pdo_db;
        $madb = $pdo_db;

		$rq = "SELECT Notes.NomNote, Matieres.NomMat, Matieres.Prof, NotesMatieres.Coefficient FROM NotesMatieres INNER JOIN Notes ON Notes.NoNote = NotesMatieres.noNote INNER JOIN Matieres ON Matieres.NoMat = NotesMatieres.noMat WHERE Notes.NomNote = '$note' AND Matieres.NomMat = '$matiere' AND NotesMatieres.Coefficient = '$coef';";
		$rq_notes = "SELECT * FROM Notes;";
		$rq_matieres = "SELECT * FROM Matieres;";
		$resultat = $madb->query($rq);
		$resultat_notes = $madb->query($rq_notes);
		$resultat_matieres = $madb->query($rq_matieres);
		$tableau_assoc = $resultat->fetch(PDO::FETCH_ASSOC);
		$tableau_notes = $resultat_notes->fetchAll(PDO::FETCH_ASSOC);
		$tableau_matieres = $resultat_matieres->fetchAll(PDO::FETCH_ASSOC);

		if ($tableau_assoc != null) {
		?>
			<form class="ms-5" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="form_modif" method="post" onsubmit='EnvoiRequete(event, this)'>
				<fieldset>
					<label for="id_notes">Nom de la Notes : </label>
					<select class="me-5" id="id_notes" name="nomNote" size="1">
						<?php
						foreach ($tableau_notes as $key_note => $value) {
							if ($value['NomNote'] == $tableau_assoc['NomNote']) {
								echo '<option value="' . $value['NomNote'] . '" selected>'
									. $value['NomNote'] . '</option>';
							} else {
								echo '<option value="' . $value['NomNote'] . '">'
									. $value['NomNote'] . '</option>';
							}
						}
						?>
					</select>
					<label for="id_mat">Matière : </label>
					<select class="me-5" id="id_mat" name="nomMat" size="1">
						<?php
						foreach ($tableau_matieres as $key_mat => $value) {
							if ($value['NomMat'] == $tableau_assoc['NomMat']) {
								echo '<option value="' . $value['NomMat'] . '" selected>'
									. $value['NomMat'] . '</option>';
							} else {
								echo '<option value="' . $value['NomMat'] . '">'
									. $value['NomMat'] . '</option>';
							}
						}
						?>
					</select>
					<label for="id_coef">Coefficient :</label>
					<input class="me-5" type="text" name="coef" id="id_Coefficient" value="<?php echo $tableau_assoc['Coefficient']; ?>" required>
					<?php
					$old_nomNote = $_POST['NomNote'];
					$rq_notes = "SELECT NoNote FROM Notes WHERE NomNote='$old_nomNote'";
					$resultat_noNote = $madb->query($rq_notes);
					$old_noNote = $resultat_noNote->fetch(PDO::FETCH_ASSOC)['NoNote'];

					$old_nomMat = $_POST['NomMat'];
					$rq_matieres = "SELECT NoMat FROM Matieres WHERE NomMat='$old_nomMat'";
					$resultat_noMat = $madb->query($rq_matieres);
					$old_noMat = $resultat_noMat->fetch(PDO::FETCH_ASSOC)['NoMat'];
					?>
					<input type="hidden" name="old_noNote" value="<?php echo $old_noNote; ?>">
					<input type="hidden" name="old_noMat" value="<?php echo $old_noMat; ?>">
					<input type="hidden" name="old_coef" value="<?php echo $_POST['Coefficient']; ?>">
					<img src="image.php" onclick="this.src='image.php?' + Math.random();" alt="captcha" style="cursor:pointer;">
					<input type="text" name="captcha" />
					<input type="submit" name="modif" value="Modifier" /><br>
				</fieldset>
			</form>
		<?php
		}
	} catch (Exception $e) {
		echo "<p>Erreur lors de la connexion à la BDD : " . $e->getMessage() . "</p>";
	}
	echo "<br/>";
}

//****************************Filtrage par Notes/Matières****************************

function FormulaireFiltreMatiere()
{
	echo "<br/>";
	try {
        // Modification pour utiliser la connexion MySQL globale
        global $pdo_db;
        $madb = $pdo_db;

		// Requêtes SQL pour récupérer les matières et les types de notes
		$rq_matiere = "SELECT NoMat, NomMat FROM Matieres";
		$rq_type_eval = "SELECT NoNote, NomNote FROM Notes";

		$resultat_matiere = $madb->query($rq_matiere);
		$resultat_type_eval = $madb->query($rq_type_eval);

		// Récupération des résultats sous forme de tableau associatif
		$tableau_assoc_matiere = $resultat_matiere->fetchAll(PDO::FETCH_ASSOC);
		$tableau_assoc_type_eval = $resultat_type_eval->fetchAll(PDO::FETCH_ASSOC);

		if ($tableau_assoc_matiere && $tableau_assoc_type_eval) {
		?>
			<form class="ms-5" method="post" onsubmit='return EnvoiRequeteFiltreMat(event, this);'>
				<fieldset>
					<label for="id_matiere">Matière : </label>
					<select id="id_matiere" name="noMat" required>
						<?php
						foreach ($tableau_assoc_matiere as $matiere) {
							echo '<option value="' . $matiere['NoMat'] . '">' . $matiere['NomMat'] . '</option>';
						}
						?>
					</select>
					<br />
					<input type="submit" value="Afficher" />
				</fieldset>
			</form>
		<?php
		}
	} catch (Exception $e) {
		echo "<p class='erreur'>Erreur lors de la connexion à la BDD : " . $e->getMessage() . "</p>";
	}
}

function FormulaireFiltreNote()
{
	echo "<br/>";
	try {
        // Modification pour utiliser la connexion MySQL globale
        global $pdo_db;
        $madb = $pdo_db;

		// Requêtes SQL pour récupérer les matières et les types de notes
		$rq_matiere = "SELECT NoMat, NomMat FROM Matieres";
		$rq_type_eval = "SELECT NoNote, NomNote FROM Notes";

		$resultat_matiere = $madb->query($rq_matiere);
		$resultat_type_eval = $madb->query($rq_type_eval);

		// Récupération des résultats sous forme de tableau associatif
		$tableau_assoc_matiere = $resultat_matiere->fetchAll(PDO::FETCH_ASSOC);
		$tableau_assoc_type_eval = $resultat_type_eval->fetchAll(PDO::FETCH_ASSOC);

		if ($tableau_assoc_matiere && $tableau_assoc_type_eval) {
		?>
			<form class="ms-5" method="post" onsubmit='return EnvoiRequeteFiltreNote(event, this);'>
				<fieldset>
					<label for="id_type_eval">Type d'évaluation : </label>
					<select id="id_type_eval" name="noNote" required>
						<?php
						foreach ($tableau_assoc_type_eval as $type_eval) {
							echo '<option value="' . $type_eval['NoNote'] . '">' . $type_eval['NomNote'] . '</option>';
						}
						?>
					</select>
					<br />
					<input type="submit" value="Afficher" />
				</fieldset>
			</form>
<?php
		}
	} catch (Exception $e) {
		echo "<p class='erreur'>Erreur lors de la connexion à la BDD : " . $e->getMessage() . "</p>";
	}
}

?>