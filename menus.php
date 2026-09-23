<?php

require 'config.php';

$requete = $db->query(
    'SELECT * FROM menus WHERE actif = 1 ORDER BY id ASC'
);

$menus = $requete->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nos menus - Vite & Gourmand</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header>
    <h1>Vite & Gourmand</h1>
    <p>Nos menus</p>
</header>

<nav>
    <a href="index.html">Accueil</a>
    <a href="menus.php">Nos menus</a>
    <a href="connexion.php">Connexion</a>
    <a href="contact.php">Contact</a>
</nav>

<main>

<section>

    <h2>Découvrez nos menus</h2>

    <p>
        Choisissez le menu qui correspond à vos envies
        et à votre événement.
    </p>

    <div class="card">

        <h3>Filtrer les menus</h3>

        <label for="prix">
            Prix maximum (€) :
        </label>

        <br>

        <input
            type="number"
            id="prix"
            min="0"
            placeholder="Ex : 30"
        >

        <br><br>

        <label for="theme">
            Thème :
        </label>

        <br>

        <select id="theme">

            <option value="">Tous les thèmes</option>
            <option value="classique">Classique</option>
            <option value="evenement">Événement</option>
            <option value="noel">Noël</option>
            <option value="paques">Pâques</option>

        </select>

        <br><br>

        <label for="regime">
            Régime :
        </label>

        <br>

        <select id="regime">

            <option value="">Tous les régimes</option>
            <option value="classique">Classique</option>
            <option value="vegetarien">Végétarien</option>
            <option value="vegan">Vegan</option>

        </select>

        <br><br>

        <label for="personnes">
            Nombre maximum de personnes :
        </label>

        <br>

        <input
            type="number"
            id="personnes"
            min="1"
            placeholder="Ex : 10"
        >

    </div>


    <div class="cards">

<?php foreach ($menus as $menu): ?>

        <div
            class="card menu-card"
            data-prix="<?= (float) $menu['prix_minimum'] ?>"
            data-theme="<?= htmlspecialchars($menu['theme'], ENT_QUOTES, 'UTF-8') ?>"
            data-regime="<?= htmlspecialchars($menu['regime'], ENT_QUOTES, 'UTF-8') ?>"
            data-personnes="<?= (int) $menu['minimum_personnes'] ?>"
        >

            <h3>
                <?= htmlspecialchars($menu['titre'], ENT_QUOTES, 'UTF-8') ?>
            </h3>

            <p>
                <?= htmlspecialchars($menu['description'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <p>
                <strong>
                    <?= number_format((float) $menu['prix_minimum'], 2, ',', ' ') ?>
                    € / personne
                </strong>
            </p>

            <p>
                Minimum :
                <?= (int) $menu['minimum_personnes'] ?>
                personnes
            </p>

            <p>
                Thème :
                <?= htmlspecialchars($menu['theme'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <p>
                Régime :
                <?= htmlspecialchars($menu['regime'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <p>
                Allergènes :
                <?= htmlspecialchars($menu['allergenes'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <p>
                <?= htmlspecialchars($menu['conditions'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <a
                class="btn"
                href="menu.php?id=<?= (int) $menu['id'] ?>"
            >
                Voir le menu
            </a>

        </div>

<?php endforeach; ?>

    </div>

</section>

</main>

<footer>

    <p>
        <strong>Vite & Gourmand</strong>
        - Traiteur à Bordeaux
    </p>

    <p>
        Contact : contact@vite-et-gourmand.fr
    </p>

    <p>
        <a href="#">Mentions légales</a> |
        <a href="#">Conditions générales de vente</a>
    </p>

</footer>

<script src="js/app.js"></script>

</body>
</html>
