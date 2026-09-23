<?php

require 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Menu introuvable.');
}

$id = (int) $_GET['id'];

$requete = $db->prepare(
    'SELECT *
     FROM menus
     WHERE id = ?
     AND actif = 1'
);

$requete->execute([$id]);

$menu = $requete->fetch();

if (!$menu) {
    die('Menu introuvable.');
}

$requetePlats = $db->prepare(
    'SELECT
        plats.nom,
        plats.description,
        plats.type,
        plats.allergenes
     FROM plats
     INNER JOIN menu_plat
        ON plats.id = menu_plat.plat_id
     WHERE menu_plat.menu_id = ?
     ORDER BY plats.type ASC, plats.nom ASC'
);

$requetePlats->execute([$id]);

$plats = $requetePlats->fetchAll();

session_start();

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
        <?= htmlspecialchars(
            $menu['titre'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>
        - Vite & Gourmand
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
        Détail du menu
    </p>

</header>

<nav>

    <a href="index.html">
        Accueil
    </a>

    <a href="menus.php">
        Nos menus
    </a>

<?php if (isset($_SESSION['utilisateur_id'])): ?>

    <a href="compte.php">
        Mon compte
    </a>

<?php else: ?>

    <a href="connexion.php">
        Connexion
    </a>

<?php endif; ?>

    <a href="contact.php">
        Contact
    </a>

</nav>

<main>

<section>

    <h2>
        <?= htmlspecialchars(
            $menu['titre'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </h2>


    <div class="card">

        <h3>
            Description
        </h3>

        <p>
            <?= htmlspecialchars(
                $menu['description'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Prix :
            </strong>

            <?= number_format(
                (float) $menu['prix_minimum'],
                2,
                ',',
                ' '
            ) ?>

            € / personne
        </p>

        <p>
            <strong>
                Minimum :
            </strong>

            <?= (int) $menu['minimum_personnes'] ?>

            personnes
        </p>

        <p>
            <strong>
                Thème :
            </strong>

            <?= htmlspecialchars(
                $menu['theme'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Régime :
            </strong>

            <?= htmlspecialchars(
                $menu['regime'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Allergènes :
            </strong>

            <?= htmlspecialchars(
                $menu['allergenes'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Conditions :
            </strong>

            <?= htmlspecialchars(
                $menu['conditions'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Stock disponible :
            </strong>

            <?= (int) $menu['stock'] ?>
        </p>

    </div>


    <h2>
        Composition du menu
    </h2>


<?php if (count($plats) === 0): ?>

    <div class="card">

        <p>
            La composition de ce menu sera bientôt disponible.
        </p>

    </div>

<?php else: ?>


<?php foreach ($plats as $plat): ?>

    <div class="card">

        <h3>
            <?= htmlspecialchars(
                $plat['nom'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </h3>

        <p>
            <strong>
                Type :
            </strong>

            <?= htmlspecialchars(
                $plat['type'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <?= htmlspecialchars(
                $plat['description'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Allergènes :
            </strong>

            <?= htmlspecialchars(
                $plat['allergenes'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

    </div>

<?php endforeach; ?>

<?php endif; ?>


    <div class="card">

<?php if (isset($_SESSION['utilisateur_id'])): ?>

        <a
            class="btn"
            href="commande.php?menu_id=<?= (int) $menu['id'] ?>"
        >
            Commander ce menu
        </a>

<?php else: ?>

        <a
            class="btn"
            href="connexion.php"
        >
            Se connecter pour commander
        </a>

        <p>
            Vous devez être connecté pour passer une commande.
        </p>

<?php endif; ?>

    </div>

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
