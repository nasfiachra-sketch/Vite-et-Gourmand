<?php

session_start();

require 'config.php';

if (
    !isset($_SESSION['utilisateur_id']) ||
    !in_array($_SESSION['role'], ['admin', 'employee'], true)
) {
    die('Accès réservé aux employés et à l’administrateur.');
}

$message = '';
$erreur = '';

$jours = [
    'Lundi',
    'Mardi',
    'Mercredi',
    'Jeudi',
    'Vendredi',
    'Samedi',
    'Dimanche'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jour = trim($_POST['jour']);
    $heureOuverture = trim($_POST['heure_ouverture']);
    $heureFermeture = trim($_POST['heure_fermeture']);

    if (
        $jour === '' ||
        $heureOuverture === '' ||
        $heureFermeture === ''
    ) {

        $erreur = 'Tous les champs sont obligatoires.';

    } elseif (!in_array($jour, $jours, true)) {

        $erreur = 'Jour invalide.';

    } else {

        $verification = $db->prepare(
            'SELECT id
             FROM horaires
             WHERE jour = ?'
        );

        $verification->execute([
            $jour
        ]);

        $horaireExistant = $verification->fetch();

        if ($horaireExistant) {

            $requete = $db->prepare(
                'UPDATE horaires
                 SET heure_ouverture = ?,
                     heure_fermeture = ?
                 WHERE jour = ?'
            );

            $requete->execute([
                $heureOuverture,
                $heureFermeture,
                $jour
            ]);

            $message = 'Les horaires ont été mis à jour.';

        } else {

            $requete = $db->prepare(
                'INSERT INTO horaires
                (
                    jour,
                    heure_ouverture,
                    heure_fermeture
                )
                VALUES (?, ?, ?)'
            );

            $requete->execute([
                $jour,
                $heureOuverture,
                $heureFermeture
            ]);

            $message = 'Les horaires ont été ajoutés.';
        }
    }
}


$requeteHoraires = $db->query(
    'SELECT
        id,
        jour,
        heure_ouverture,
        heure_fermeture
     FROM horaires
     ORDER BY id ASC'
);

$horaires = $requeteHoraires->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Gestion des horaires - Vite & Gourmand
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<header>

    <h1>
        Vite & Gourmand
    </h1>

    <p>
        Gestion des horaires
    </p>

</header>

<nav>

    <a href="index.html">
        Accueil
    </a>

    <a href="menus.php">
        Nos menus
    </a>

    <a href="gestion-menus.php">
        Gestion des menus
    </a>

    <a href="gestion-plats.php">
        Gestion des plats
    </a>

    <a href="gestion-menu-plats.php">
        Association menus / plats
    </a>

    <a href="gestion-horaires.php">
        Gestion des horaires
    </a>

<?php if ($_SESSION['role'] === 'admin'): ?>

    <a href="admin.php">
        Administration
    </a>

<?php endif; ?>

    <a href="logout.php">
        Déconnexion
    </a>

</nav>

<main>

<section>

    <h2>
        Modifier les horaires
    </h2>


<?php if ($message !== ''): ?>

    <div class="card">

        <p>
            <strong>
                <?= htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </strong>
        </p>

    </div>

<?php endif; ?>


<?php if ($erreur !== ''): ?>

    <div class="card">

        <p>
            <strong>
                <?= htmlspecialchars(
                    $erreur,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </strong>
        </p>

    </div>

<?php endif; ?>


<div class="card">

    <form
        method="POST"
        action="gestion-horaires.php"
    >

        <label for="jour">
            Jour :
        </label>

        <br>

        <select
            id="jour"
            name="jour"
            required
        >

            <option value="">
                Choisir un jour
            </option>

<?php foreach ($jours as $jour): ?>

            <option value="<?= htmlspecialchars(
                $jour,
                ENT_QUOTES,
                'UTF-8'
            ) ?>">
                <?= htmlspecialchars(
                    $jour,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </option>

<?php endforeach; ?>

        </select>

        <br><br>


        <label for="heure_ouverture">
            Heure d'ouverture :
        </label>

        <br>

        <input
            type="time"
            id="heure_ouverture"
            name="heure_ouverture"
            required
        >

        <br><br>


        <label for="heure_fermeture">
            Heure de fermeture :
        </label>

        <br>

        <input
            type="time"
            id="heure_fermeture"
            name="heure_fermeture"
            required
        >

        <br><br>

        <button
            class="btn"
            type="submit"
        >
            Enregistrer les horaires
        </button>

    </form>

</div>


<h2>
    Horaires actuels
</h2>


<?php if (count($horaires) === 0): ?>

    <div class="card">

        <p>
            Aucun horaire n'est encore enregistré.
        </p>

    </div>

<?php else: ?>


<?php foreach ($horaires as $horaire): ?>

    <div class="card">

        <h3>
            <?= htmlspecialchars(
                $horaire['jour'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </h3>

        <p>
            <strong>
                Ouverture :
            </strong>

            <?= htmlspecialchars(
                $horaire['heure_ouverture'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Fermeture :
            </strong>

            <?= htmlspecialchars(
                $horaire['heure_fermeture'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

    </div>

<?php endforeach; ?>

<?php endif; ?>

</section>

</main>

<footer>

    <p>
        <strong>
            Vite & Gourmand
        </strong>
        - Traiteur à Bordeaux
    </p>

    <p>
        Contact : contact@vite-et-gourmand.fr
    </p>

    <p>

        <a href="#">
            Mentions légales
        </a>

        |

        <a href="#">
            Conditions générales de vente
        </a>

    </p>

</footer>

</body>
</html>