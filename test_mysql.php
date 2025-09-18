<?php
echo 'Test des drivers PDO :<br>';
print_r(PDO::getAvailableDrivers());

echo '<br><br>Test connexion MySQL :<br>';
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    echo 'Driver PDO MySQL fonctionne !';
} catch (Exception $e) {
    echo 'Erreur : ' . $e->getMessage();
}
?>
