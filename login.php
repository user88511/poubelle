<?php
// login.php
session_start();
require 'db.php';

// Si l'utilisateur est déjà connecté, on le redirige (par exemple vers index.php)
if (isset($_SESSION['users'])) {
    header("Location: index.php"); 
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Quand une requête de type "POST" est reçue
    // On récupère les données du formulaire
    $mail = htmlspecialchars($_POST['mail']); // On utilise htmlspecialchars pour éviter les attaques XSS
    // On n'utilise pas htmlspecialchars pour le mot de passe car il ne doit pas être
    $password = $_POST['password'];

    // 1. On cherche l'utilisateur par son email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE mail = ?"); // On prépare une requête pour sélectionner l'utilisateur par son email
    // On utilise un paramètre pour éviter les injections SQL
    $stmt->execute([$mail]); // On exécute la requête avec l'email fourni
    // On récupère l'utilisateur
    $user = $stmt->fetch();

    // 2. On vérifie le mot de passe
    if ($user && password_verify($password, $user['password'])){ // On vérifie si l'utilisateur existe et si le mot de passe correspond 
        // SUCCÈS : On enregistre les infos en session
        $_SESSION['user_id'] = $user['id']; // On garde l'ID de l'utilisateur pour les requêtes futures
        $_SESSION['user_name'] = $user['name']; // On garde le nom de l'utilisateur pour l'afficher dans l'interface
        
        // Redirection vers l'accueil
        header("Location: index.php");
        exit();
    } else {
        // ÉCHEC : On affiche un message d'erreur
        $message = "Email ou mot de passe incorrect.";
    }
}
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Connexion — Guardia</title>
    <link rel="stylesheet" href="./../Style/register.css"/>
</head>
<body>
    <main class="wrap" role="main">
        <section class="card">
            <h2 id="login-title">Se connecter</h2>
            
            <form id="loginForm" action="login.php" method="POST">
                
                <div>
                    <label for="email">Adresse e-mail</label>
                    <input id="email" name="mail" type="email" required placeholder="vous@exemple.com" value="<?php echo isset($_POST['mail']) ? htmlspecialchars($_POST['mail']) : ''; ?>" />
                </div>

                <div style="margin-top: 15px;">
                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" required />
                </div>

                <div style="margin-top:20px;">
                    <button id="submitBtn" class="btn" type="submit">Connexion</button>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="error-box" style="color:red; margin-top:15px; text-align:center;">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <p style="margin-top:20px;">Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
            </form>
        </section>
    </main>
</body>
</html>