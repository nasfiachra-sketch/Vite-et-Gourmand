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

$menuSelectionne = isset($_GET['menu_id'])
    ? (int) $_GET['menu_id']
    : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $menuId = isset($_POST['menu_id'])
        ? (int) $_POST['menu_id']
        : 0;

    $platId = isset($_POST['plat_id'])
        ? (int) $_POST['plat_id']
        : 0;

    $action = isset($_POST['action'])
        ? trim($_POST['action'])
        : '';

    $menuSelectionne = $menuId;

    if ($menuId < 1 || $platId < 1) {

        $erreur = 'Menu ou plat invalide.';

    } elseif ($action === 'ajouter') {

        $verification = $db->prepare(
            'SELECT menu_id
             FROM menu_plat
             WHERE menu_id = ?
             AND plat_id = ?'
        );

        $verification->execute([
            $menuId,
            $platId
        ]);

        if ($verification->fetch()) {

            $erreur = 'Ce plat est déjà associé à ce menu.';

        } else {

            $requete = $db->prepare(
                'INSERT INTO menu_plat
                (menu_id, plat_id)
                VALUES (?, ?)'
            );

            $requete->execute([
                $menuId,
                $platId
            ]);

            $message = 'Le plat a été ajouté au menu.';
        }

    } elseif ($action === 'supprimer') {

        $requete = $db->prepare(
            'DELETE FROM menu_plat
             WHERE menu_id = ?
             AND plat_id = ?'
        );

        $requete->execute([
            $menuId,
            $platId
        ]);

        $message = 'Le plat a été retiré du menu.';
    }
}

$requeteMenus = $db->query(
    'SELECT id, titre
     FROM menus
     ORDER BY titre ASC'
);

$menus = $requeteMenus->fetchAll();

$platsDuMenu = [];

if ($menuSelectionne > 0) {

    $requetePlatsMenu = $db->prepare(
        'SELECT
            plats.id,
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

    $requetePlatsMenu->execute([
        $menuSelectionne
    ]);

    $platsDuMenu = $requetePlatsMenu->fetchAll();
}

$idsPlatsDuMenu = [];

foreach ($platsDuMenu as $plat) {
    $idsPlatsDuMenu[] = (int) $plat['id'];
}

$requetePlats = $db->query(
    'SELECT id, nom, type
     FROM plats
     ORDER BY type ASC, nom ASC'
);

$plats = $requetePlats->fetchAll();

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
        Plats des menus - Vite & Gourmand
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
        Association des plats et des menus
    </p>

</header>

<nav>

    <a href="index.php">
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
        Associer des plats aux menus
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

    <h3>
        Choisir un menu
    </h3>

    <form
        method="GET"
        action="gestion-menu-plats.php"
    >

        <label for="menu_id">
            Menu :
        </label>

        <br>

        <select
            id="menu_id"
            name="menu_id"
            required
        >

            <option value="">
                Choisir un menu
            </option>

<?php foreach ($menus as $menu): ?>

            <option
                value="<?= (int) $menu['id'] ?>"
                <?php
                if ($menuSelectionne === (int) $menu['id']) {
                    echo 'selected';
                }
                ?>
            >

                <?= htmlspecialchars(
                    $menu['titre'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </option>

<?php endforeach; ?>

        </select>

        <br><br>

        <button
            class="btn"
            type="submit"
        >
            Afficher les plats
        </button>

    </form>

</div>


<?php if ($menuSelectionne > 0): ?>

<div class="card">

    <h3>
        Ajouter un plat à ce menu
    </h3>

<?php if (count($plats) === count($idsPlatsDuMenu)): ?>

    <p>
        Tous les plats disponibles sont déjà associés à ce menu.
    </p>

<?php else: ?>

    <form
        method="POST"
        action="gestion-menu-plats.php?menu_id=<?= $menuSelectionne ?>"
    >

        <input
            type="hidden"
            name="action"
            value="ajouter"
        >

        <input
            type="hidden"
            name="menu_id"
            value="<?= $menuSelectionne ?>"
        >

        <label for="plat_id">
            Plat :
        </label>

        <br>

        <select
            id="plat_id"
            name="plat_id"
            required
        >

            <option value="">
                Choisir un plat
            </option>

<?php foreach ($plats as $plat): ?>

<?php if (!in_array((int) $plat['id'], $idsPlatsDuMenu, true)): ?>

            <option
                value="<?= (int) $plat['id'] ?>"
            >

                <?= htmlspecialchars(
                    $plat['nom'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

                -

                <?= htmlspecialchars(
                    $plat['type'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </option>

<?php endif; ?>

<?php endforeach; ?>

        </select>

        <br><br>

        <button
            class="btn"
            type="submit"
        >
            Ajouter au menu
        </button>

    </form>

<?php endif; ?>

</div>


<h2>
    Plats actuellement dans le menu
</h2>


<?php if (count($platsDuMenu) === 0): ?>

<div class="card">

    <p>
        Aucun plat n'est encore associé à ce menu.
    </p>

</div>

<?php else: ?>


<?php foreach ($platsDuMenu as $plat): ?>

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

        <strong>
            Description :
        </strong>

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

    <form
        method="POST"
        action="gestion-menu-plats.php?menu_id=<?= $menuSelectionne ?>"
    >

        <input
            type="hidden"
            name="action"
            value="supprimer"
        >

        <input
            type="hidden"
            name="menu_id"
            value="<?= $menuSelectionne ?>"
        >

        <input
            type="hidden"
            name="plat_id"
            value="<?= (int) $plat['id'] ?>"
        >

        <button
            class="btn"
            type="submit"
        >
            Retirer du menu
        </button>

    </form>

</div>

<?php endforeach; ?>

<?php endif; ?>

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