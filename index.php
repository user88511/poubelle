<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Events - Gestion d'Événements Étudiants</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./../Style/styles.css"/> 
</head>
<body>

    <header class="header" role="banner">
        <a href="#" class="logo">Campus Events</a>

        <button class="nav-toggle" id="navToggle" aria-controls="primaryNav" aria-expanded="false" aria-label="Afficher le menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <nav id="primaryNav" class="nav" role="navigation">
            <ul class="nav-list">
                <li><a href="#accueil">Accueil</a></li>
                <li><a href="evenement.php">Événements</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="login.php" class="btn btn-secondary">Connexion</a></li>
                <li ><a href="register.php" class="btn btn-primary">Inscription</a></li>
                <li ><a href="meteo.php" class="btn btn-outline">Météo</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="accueil" class="hero-banner" aria-labelledby="hero-title">
            <div class="hero-content">
                <h1 id="hero-title">Le cœur de la vie étudiante sur le campus</h1>
                <p class="hero-sub">Organisez, gérez et participez à toutes les activités étudiantes — conférences, ateliers, soirées et plus.</p>
                <div class="hero-ctas">
                    <a href="#organiser" class="btn btn-primary">Créer un événement</a>
                    <a href="evenement.html" class="btn btn-outline">Voir les événements</a>
                </div>
            </div>
        </section>

        <hr>

        <section id="evenements" class="section-evenements">
            <h2 class="section-title">📅 Événements Populaires à Venir</h2>
            <div class="event-grid">
                
                <article class="event-card" style="background-image:url('./../Img/tech-conference-speech-stockcake.png')">
                    <div class="event-info">
                        <h3 class="event-title">Conférence Tech - L'IA en 2025</h3>
                        <p class="event-date-location">🗓 15 Déc. | 18h00 | 📍 Amphi A</p>
                        <p>Plongez dans l'avenir de l'intelligence artificielle avec des experts du domaine.</p>
                        <a href="evenement.html" class="btn btn-card">Détails et Inscription</a>
                    </div>
                </article>

                <article class="event-card" style="background-image:url('./../Img/img_soiree_etudiante.png')">
                    <div class="event-info">
                        <h3 class="event-title">Soirée de Fin de Session</h3>
                        <p class="event-date-location">🗓 22 Déc. | 21h30 | 📍 Bar du Campus</p>
                        <p>Célébrez la fin des examens avec la plus grande fête de l'année !</p>
                        <a href="evenement.html" class="btn btn-card">S'inscrire gratuitement</a>
                    </div>
                </article>

                <article class="event-card" style="background-image:url('./../Img/billet_d_entree.jpg')">
                    <div class="event-info">
                        <h3 class="event-title">Ticketshop - Achat de billet d'entrée</h3>
                        <p class="event-date-location">🗓 10 Janv. | 10h00 | 📍 Labo Informatique C</p>
                        <p>Acheter vos billets d'entrée pour les evenements à venir !</p>
                        <a href="#" class="btn btn-card">Acheter un Billet</a>
                    </div>
                </article>
                
            </div>
        </section>
        
        <hr>
        
        <section id="organiser" class="section-organiser">
            <h2>💡 Vous avez une idée d'événement ?</h2>
            <p>Devenez organisateur ! Notre plateforme simplifie la gestion des inscriptions, la promotion et la coordination logistique.</p>
            <a href="#" class="btn btn-primary btn-large">Commencer l'organisation</a>
        </section>

    </main>

    <footer class="footer" role="contentinfo">
        <p>&copy; 2025 Campus Events. Propulsé par la communauté étudiante.</p>
        <p>Conditions Générales | Politique de Confidentialité</p>
    </footer>

    <script src="./../script/index.js"></script>
</body>
</html>