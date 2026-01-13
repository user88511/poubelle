<?php
// evenement.php - Bloc PHP de Connexion et de Traitement
// DOIT ÊTRE LA PREMIÈRE CHOSE DANS LE FICHIER
session_start();
require 'db.php'; 

// ------------------------------------------------------------
// 1. Traitement du formulaire d'ajout d'événement (si POST)
// ------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $titre_original = htmlspecialchars($_POST['title']);
    $date = htmlspecialchars($_POST['date']);
    $start = htmlspecialchars($_POST['start']);
    $end = htmlspecialchars($_POST['end']);
    $color = htmlspecialchars($_POST['color']); 
    $description = htmlspecialchars($_POST['desc']);

    // ***** SOLUTION DE CONTOURNEMENT POUR LA CLÉ PRIMAIRE 'titre' *****
    // Nous générons un identifiant unique (timestamp) et l'ajoutons au titre.
    // Cela garantit l'unicité requise par la BDD pour la clé primaire 'titre'.
    $horodatage_unique = time();
    $titre_unique = $titre_original . " [TS:" . $horodatage_unique . "]"; // Exemple: "Réunion [TS:1672531200]"
    // ******************************************************************

    try {
        // Insertion dans la base de données (nous insérons le titre unique $titre_unique)
        $stmt = $pdo->prepare("INSERT INTO events (titre, date, start, end, color, description) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$titre_unique, $date, $start, $end, $color, $description])) {
            // Redirection PRG après succès
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "<script>alert('Erreur lors de l\'ajout de l\'événement. L\'exécution de la requête a échoué.');</script>";
        }
    } catch (PDOException $e) {
        // Gérer l'erreur BDD (bien que cette solution devrait l'éviter pour les doublons)
        error_log("Erreur BDD POST: " . $e->getMessage());
        echo "<script>alert('Erreur BDD PDO : L\'insertion a échoué. Cause : " . $e->getMessage() . "');</script>";
    }
}


// ------------------------------------------------------------
// 2. Récupération des événements (inchangé, mais utilise les titres avec le suffixe)
// ------------------------------------------------------------
$events = []; 
try {
    $stmt = $pdo->query("SELECT titre, date, start, end, color, description FROM events");
    $dbEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formater les événements pour FullCalendar
    foreach ($dbEvents as $event) {
        $start_iso = $event['date'] . 'T' . $event['start'];
        $end_iso = $event['end'] ? ($event['date'] . 'T' . $event['end']) : null;
        
        // Nous conservons le titre complet (avec le suffixe [TS:..]) pour l'affichage,
        // car c'est la valeur stockée dans la colonne 'titre'.
        $events[] = [
            'id' => uniqid(), // ID généré temporairement pour FullCalendar
            'title' => $event['titre'],
            'start' => $start_iso,
            'end' => $end_iso,
            'backgroundColor' => $event['color'],
            'borderColor' => $event['color'], 
            'extendedProps' => ['description' => $event['description'] ?? '']
        ];
    }

} catch (PDOException $e) {
    error_log("Erreur de BDD lors du chargement des événements: " . $e->getMessage());
}

// Encoder les événements en JSON pour JavaScript
$json_events = json_encode($events);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Planning - Campus Events</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./../Style/styles.css"/>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css"/>

    <link rel="stylesheet" href="./../Style/evenement.css"/>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
</head>
<body>
  <header class="header" role="banner">
    <a href="index.html#accueil" class="logo">Campus Events</a>

    <button class="nav-toggle" id="navToggle" aria-controls="primaryNav" aria-expanded="false" aria-label="Afficher le menu">
      <span class="bar"></span><span class="bar"></span><span class="bar"></span>
    </button>

    <nav id="primaryNav" class="nav" role="navigation">
      <ul class="nav-list">
        <li><a href="index.php#accueil">Accueil</a></li>
        <li><a href="evenement.php" class="active">Événements</a></li>
        <li><a href="login.php" class="btn btn-secondary">Connexion</a></li>
        <li><a href="meteo.php" class="btn btn-outline">Météo</a></li>
      </ul>
    </nav>
  </header>

  <main class="page-grid">
    <aside class="panel" aria-labelledby="create-title">
      <h2 id="create-title">Créer un événement</h2>

      <form id="eventForm" class="event-form" autocomplete="off" method="POST">
        <label for="title">Titre</label>
        <input id="title" name="title" required placeholder="Titre de l'événement" />

        <div class="form-row">
          <div>
            <label for="date">Date</label>
            <input id="date" name="date" type="date" required />
          </div>
          <div>
            <label for="start">Heure début</label>
            <input id="start" name="start" type="time" required />
          </div>
        </div>

        <div class="form-row">
          <div>
            <label for="end">Heure fin</label>
            <input id="end" name="end" type="time" />
          </div>
          <div>
            <label for="color">Couleur</label>
            <input id="color" name="color" type="color" value="#ff6b3d" />
          </div>
        </div>

        <label for="desc">Description (optionnel)</label>
        <textarea id="desc" name="desc" rows="4" placeholder="Détails, lieu, lien..."></textarea>

        <div class="form-actions">
          <button class="btn btn-primary" type="submit">Ajouter</button>
          <button id="clearBtn" type="button" class="btn btn-secondary">Effacer</button>
        </div>

        <p class="muted">Les événements sont stockés dans la base de données et s'affichent dans la vue semaine.</p>
      </form>

      <hr/>

      <h3>Liste rapide - semaine sélectionnée</h3>
      <div id="weekList" class="week-list" aria-live="polite"></div>
    </aside>

    <section class="calendar-wrap" aria-labelledby="planning-title">
      <h2 id="planning-title" class="section-title">📋 Planning Hebdomadaire</h2>
      <div id="calendar"></div>
    </section>
  </main>

  <footer class="footer" role="contentinfo">
    <p>&copy; 2025 Campus Events</p>
  </footer>

  <script>
  (function(){
    // mobile nav toggle
    const btn = document.getElementById('navToggle');
    const nav = document.getElementById('primaryNav');
    if(btn && nav){
      btn.addEventListener('click', ()=> {
        const expanded = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', String(!expanded));
        nav.classList.toggle('nav-open');
      });
    }
  })();
  </script>

  <script>
  (function(){
    // 1. Récupération des données JSON de PHP
    // Le moteur PHP remplace '<?php echo $json_events; ?>' par le tableau JSON des événements BDD.
    const initialDbEvents = <?php echo $json_events; ?>;

    // init calendar
    document.addEventListener('DOMContentLoaded', function() {
      
      const storedEvents = initialDbEvents; 

      const calendarEl = document.getElementById('calendar');
      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'timeGridWeek,dayGridMonth,listWeek'
        },
        selectable: true,
        editable: false,
        navLinks: true,
        nowIndicator: true,
        weekNumbers: false,
        locale: 'fr',
        height: 'auto',
        
        // 2. Utilisation des événements BDD
        events: storedEvents, 
        
        eventClick: function(info){
          info.jsEvent.preventDefault();
          const ev = info.event;
          const desc = ev.extendedProps.description || '';
          const keep = confirm('Événement : ' + ev.title + '\n' + (desc ? (desc + '\n\n') : '') + 'Voulez-vous supprimer cet événement ? (Nécessite une requête AJAX pour la suppression définitive en BDD)');
          if(keep){
            // Suppression visuelle
            ev.remove(); 
            
            // NOTE : Pour la suppression BDD, vous devrez ajouter ici une requête AJAX DELETE
            // en utilisant l'ID de l'événement : ev.id

            renderWeekList(calendar);
          }
        },
        datesSet: function(){
          renderWeekList(calendar);
        }
      });

      calendar.render();

      // Form handling: Le preventDefault et la logique JS/localStorage sont retirés
      // car l'ajout est géré par PHP (POST + Redirection).
      const form = document.getElementById('eventForm');
      const clearBtn = document.getElementById('clearBtn');

      form.addEventListener('submit', (e) => {
        // Pas de e.preventDefault() ici pour que le formulaire se soumette à PHP
      });

      clearBtn.addEventListener('click', () => form.reset());

      // Render a compact list of events for the currently visible week
      window.renderWeekList = function(calendarInstance){
        const listWrap = document.getElementById('weekList');
        listWrap.innerHTML = '';
        const view = calendarInstance.view;
        const start = view.activeStart;
        const end = view.activeEnd;

        // gather events from calendar in the week range
        const weekEvents = calendarInstance.getEvents().filter(ev => {
          const s = ev.start;
          return s && s >= start && s < end;
        }).sort((a,b) => a.start - b.start);

        if(weekEvents.length === 0){
          listWrap.innerHTML = '<div class="empty-state">Aucun événement cette semaine.</div>';
          return;
        }

        const ul = document.createElement('ul');
        ul.className = 'compact-list';
        weekEvents.forEach(ev => {
          const li = document.createElement('li');
          const d = new Date(ev.start);
          const time = d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
          li.innerHTML = '<strong>' + time + '</strong> — <span class="ev-title">' + ev.title + '</span>' +
            (ev.extendedProps.description ? '<div class="small-desc">' + ev.extendedProps.description + '</div>' : '');
          ul.appendChild(li);
        });
        listWrap.appendChild(ul);
      };

      // initial list render
      renderWeekList(calendar);
    });
  })();
  </script>
</body>
</html>