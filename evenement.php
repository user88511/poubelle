<?php
// evenement.php - Bloc PHP de Connexion et de Traitement
// DOIT ÊTRE LA PREMIÈRE CHOSE DANS LE FICHIER (CORRECTION APPLIQUÉE ICI)
session_start();
// Assurez-vous que 'db.php' est correctement accessible et contient la connexion $pdo
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
    // Ajout d'un identifiant unique (timestamp) au titre pour garantir l'unicité BDD.
    $horodatage_unique = time();
    $titre_unique = $titre_original . " [TS:" . $horodatage_unique . "]"; 
    // ******************************************************************

    try {
        // Insertion dans la base de données 
        $stmt = $pdo->prepare("INSERT INTO events (titre, date, start, end, color, description) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$titre_unique, $date, $start, $end, $color, $description])) {
            // Redirection PRG après succès (fonctionne maintenant car $titre_unique est inséré)
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            // Ceci s'affiche si l'exécution BDD échoue pour une raison autre qu'une exception
            // Note: En environnement réel, ceci devrait être loggé et non affiché directement.
            echo "<script>alert('Erreur lors de l\'ajout de l\'événement. L\'exécution de la requête a échoué.');</script>";
        }
    } catch (PDOException $e) {
        // Gérer l'erreur BDD 
        error_log("Erreur BDD POST: " . $e->getMessage());
        echo "<script>alert('Erreur BDD PDO : L\'insertion a échoué. Cause : " . $e->getMessage() . "');</script>";
    }
}


// ------------------------------------------------------------
// 2. Récupération des événements et préparation pour FullCalendar
// ------------------------------------------------------------
$events = []; 
try {
    // Récupération de TOUS les événements
    $stmt = $pdo->query("SELECT titre, date, start, end, color, description FROM events");
    $dbEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formater les événements pour FullCalendar
    foreach ($dbEvents as $event) {
        $start_iso = $event['date'] . 'T' . $event['start'];
        $end_iso = $event['end'] ? ($event['date'] . 'T' . $event['end']) : null;
        
        // Ajout à la liste des événements FullCalendar
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
    // Afficher une alerte JS en cas d'échec de chargement BDD
    echo "<script>alert('Erreur de BDD: Impossible de charger les événements depuis la base de données.');</script>";
}

// Encoder les événements en JSON pour être utilisés par JavaScript
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
          <button class="btn btn-primary" type="submit">Ajouter à la BDD</button>
          <button id="clearBtn" type="button" class="btn btn-secondary">Effacer le Formulaire</button>
        </div>

        <p class="muted">Note : Les événements sont maintenant gérés par la base de données (PHP/MySQL) et le calendrier local (localStorage) est désactivé.</p>
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
  // mobile nav toggle
  (function(){
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
    // La gestion du localStorage a été retirée car les événements viennent de la BDD

    function toISO(dateStr, timeStr){
      if(!timeStr) timeStr = '00:00';
      return dateStr + 'T' + timeStr;
    }

    // Récupérer les événements générés par PHP (maintenant $json_events)
    // NOTE : Assurez-vous que votre JSON est bien encodé et ne contient pas d'erreurs.
    const dbEvents = <?php echo $json_events; ?>;

    // init calendar
    document.addEventListener('DOMContentLoaded', function() {
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
        
        // CHARGEMENT DES ÉVÉNEMENTS DE LA BDD
        events: dbEvents, 

        eventClick: function(info){
          info.jsEvent.preventDefault();
          const ev = info.event;
          const desc = ev.extendedProps.description || '';
          
          // Note: La suppression doit être gérée par une requête AJAX ou une autre soumission de formulaire 
          // à un script PHP pour interagir avec la BDD. Pour l'instant, c'est juste une alerte.
          alert(
            'Événement (B-D-D) : ' + ev.title + 
            '\n\nDescription : ' + (desc || 'Aucune') +
            '\n\nPour supprimer un événement de la base de données, une nouvelle requête vers un script PHP de suppression serait nécessaire.'
          );
        },
        datesSet: function(){
          // Re-render la liste rapide chaque fois que la vue change
          renderWeekList(calendar);
        }
      });

      calendar.render();

      // Form handling (Le code JS local d'ajout a été neutralisé car l'ajout est maintenant géré par PHP)
      const form = document.getElementById('eventForm');
      const clearBtn = document.getElementById('clearBtn');
      
      // Laissez l'écouteur de soumission pour permettre au formulaire d'être envoyé à PHP (méthode POST)
      // form.addEventListener('submit', (e) => { ... });

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
          // Utilisation de ev.title qui contient le titre avec le suffixe [TS:...]
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