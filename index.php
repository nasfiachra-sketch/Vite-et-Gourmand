<?php

require 'config.php';

$requeteHoraires = $db->query(
    'SELECT
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
        Vite & Gourmand - Traiteur à Bordeaux
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
        Traiteur à Bordeaux
    </p>

</header>

<nav>

    <a href="index.php">
        Accueil
    </a>

    <a href="menus.php">
        Nos menus
    </a>

    <a href="connexion.php">
        Connexion
    </a>

    <a href="inscription.php">
        Créer un compte
    </a>

    <a href="contact.php">
        Contact
    </a>

</nav>

<main>

<section class="hero">

    <h2>
        Des moments gourmands, livrés chez vous
    </h2>

    <p>
        Vite & Gourmand vous accompagne pour vos repas,
        anniversaires, événements et moments en famille.
    </p>

    <a
        class="btn"
        href="menus.php"
    >
        Découvrir nos menus
    </a>

</section>

<section>
    <h2>Qui sommes-nous ?</h2>

    <p>
        Vite & Gourmand est une entreprise de traiteur
        située à Bordeaux.
        Julie et José mettent leur savoir-faire au service
        de leurs clients afin de proposer des repas gourmands
        et adaptés à différents besoins.
    </p>

    <p>
        Notre objectif est de proposer une cuisine de qualité,
        un service professionnel et une expérience simple
        pour nos clients.
    </p>
</section>

<section>

    <h2>
        Pourquoi choisir Vite & Gourmand ?
    </h2>

    <div class="cards">

        <div class="card">

            <h3>
                Qualité
            </h3>

            <p>
                Des menus préparés avec soin pour garantir
                une expérience gourmande.
            </p>

        </div>

        <div class="card">

            <h3>
                Professionnalisme
            </h3>

            <p>
                Une équipe à votre écoute avant,
                pendant et après votre commande.
            </p>

        </div>

        <div class="card">

            <h3>
                Livraison
            </h3>

            <p>
                Une solution de livraison adaptée à vos
                événements à Bordeaux et ses alentours.
            </p>

        </div>

    </div>

</section>

<section>

    <h2>
        Avis de nos clients
    </h2>

    <div class="cards">

        <div class="card">

            <h3>
                ★★★★★
            </h3>

            <p>
                « Très bon repas, livraison ponctuelle
                et équipe professionnelle. »
            </p>

            <strong>
                Marie
            </strong>

            <p>
                Avis validé par Vite & Gourmand
            </p>

        </div>

        <div class="card">

            <h3>
                ★★★★★
            </h3>

            <p>
                « Les menus étaient délicieux et parfaitement
                adaptés à notre événement. »
            </p>

            <strong>
                Thomas
            </strong>

            <p>
                Avis validé par Vite & Gourmand
            </p>

        </div>

    </div>

</section>

<section>

    <h2>
        Nos horaires
    </h2>

<?php if (count($horaires) === 0): ?>

    <p style="text-align:center;">
        Les horaires seront bientôt disponibles.
    </p>

<?php else: ?>

    <p style="text-align:center;">

<?php foreach ($horaires as $horaire): ?>

        <?= htmlspecialchars(
            $horaire['jour'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

        :

        <?= htmlspecialchars(
            $horaire['heure_ouverture'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

        -

        <?= htmlspecialchars(
            $horaire['heure_fermeture'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

        <br>

<?php endforeach; ?>

    </p>

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

        Contact :
        contact@vite-et-gourmand.fr

    </p>

    <p>

        <a href="mentions-legales.php">
            Mentions légales
        </a>

        |

        <a href="cgv.php">
            Conditions générales de vente
        </a>

    </p>

</footer>

</body>
</html>