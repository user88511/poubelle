<?php
// db.php
// L'adresse du serveur. 'localhost' signifie que la base de données est actuellement héberger sur notre ordinateur.
$host = 'localhost';
// Le nom exact de la base de données
$db   = 'bdd_projet';
// Le nom d'utilisateur administrateur. Sur XAMPP c'est root par défaut.
$user = 'root';
// Le mot de passe par défaut est vide sur Xamp 
// sur Mac le mot de passe root
$pass = '';
// C'est l'url complète que PHP utilise pour trouver la base de données.
// charset=utf8mb4 permet de gérer les accents (é, à, ç) et les emojis sans bugs d'affichage.
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
// "try" signifie Essaie d'exécuter
try {
    // Explication grâce à un exemple :
    // 1. "new PDO" : Fabrique le téléphone (Création de l'objet).
    // 2. "(...)"   : Insère la carte SIM (Config : serveur, bdd, login).
    // 3. "$pdo ="  : Met le téléphone dans la poche (Variable stockée pour appeler MySQL plus tard).
    // On lui donne l'adresse ($dsn), l'utilisateur ($user) et le mot de passe ($pass).
    $pdo = new PDO($dsn, $user, $pass, [
        // Si une requête SQL échoue, PDO va "lever une exception" (arrêter le script et afficher l'erreur).
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        
        // On demande à récupérer les données sous forme de "tableau associatif".
        // Cela permet d'utiliser $user['email'] au lieu de numéros comme $user[0].
    ]);

} 
// "catch" signifie : "Si une erreur survient dans le bloc 'try', attrape-la ici".
catch (\PDOException $e) {
    // Si la connexion échoue (mauvais mot de passe, serveur éteint, etc...), on arrête tout ("die").
    // $e->getMessage() contient le texte précis de l'erreur
    die("Erreur de connexion BDD : " . $e->getMessage());
}
?>