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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = isset($_POST['action'])
        ? trim($_POST['action'])
        : '';

    if ($action === 'ajouter') {

        $titre = trim($_POST['titre']);
        $description = trim($_POST['description']);
        $theme = trim($_POST['theme']);
        $regime = trim($_POST['regime']);
        $allergenes = trim($_POST['allergenes']);
        $minimumPersonnes = (int) $_POST['minimum_personnes'];
        $prixMinimum = (float) $_POST['prix_minimum'];
        $stock = (int) $_POST['stock'];
        $conditions = trim($_POST['conditions']);

        if (
            $titre === '' ||
            $description === '' ||
            $theme === '' ||
            $regime === '' ||
            $minimumPersonnes < 1 ||
            $prixMinimum < 0 ||
            $stock < 0 ||
            $conditions === ''
        ) {

            $erreur =
                'Veuillez remplir correctement tous les champs obligatoires.';

        } else {

            $requete = $db->prepare(
                'INSERT INTO menus
                (
                    titre,
                    description,
                    theme,
                    regime,
                    allergenes,
                    minimum_personnes,
                    prix_minimum,
                    stock,
                    conditions,
                    actif
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
            );

            $requete->execute([
                $titre,
                $description,
                $theme,
                $regime,
                $allergenes,
                $minimumPersonnes,
                $prixMinimum,
                $stock,
                $conditions
            ]);

            $message = 'Le menu a été ajouté avec succès.';
        }
    }

    if ($action === 'modifier') {

        $menuId = (int) $_POST['menu_id'];

        $titre = trim($_POST['titre']);
        $description = trim($_POST['description']);
        $theme = trim($_POST['theme']);
        $regime = trim($_POST['regime']);
        $allergenes = trim($_POST['allergenes']);
        $minimumPersonnes = (int) $_POST['minimum_personnes'];
        $prixMinimum = (float) $_POST['prix_minimum'];
        $stock = (int) $_POST['stock'];
        $conditions = trim($_POST['conditions']);

        if (
            $menuId < 1 ||
            $titre === '' ||
            $description === '' ||
            $theme === '' ||
            $regime === '' ||
            $minimumPersonnes < 1 ||
            $prixMinimum < 0 ||
            $stock < 0 ||
            $conditions === ''
        ) {

            $erreur =
                'Veuillez remplir correctement tous les champs obligatoires.';

        } else {

            $requete = $db->prepare(
                'UPDATE menus
                 SET
                    titre = ?,
                    description = ?,
                    theme = ?,
                    regime = ?,
                    allergenes = ?,
                    minimum_personnes = ?,
                    prix_minimum = ?,
                    stock = ?,
                    conditions = ?
                 WHERE id = ?'
            );

            $requete->execute([
                $titre,
                $description,
                $theme,
                $regime,
                $allergenes,
                $minimumPersonnes,
                $prixMinimum,
                $stock,
                $conditions,
                $menuId
            ]);

            $message = 'Le menu a été modifié avec succès.';
        }
    }

    if ($action === 'desactiver') {

        $menuId = (int) $_POST['menu_id'];

        $requete = $db->prepare(
            'UPDATE menus
             SET actif = 0
             WHERE id = ?'
        );

        $requete->execute([
            $menuId
        ]);

        $message = 'Le menu a été désactivé.';
    }

    if ($action === 'activer') {

        $menuId = (int) $_POST['menu_id'];

        $requete = $db->prepare(
            'UPDATE menus
             SET actif = 1
             WHERE id = ?'
        );

        $requete->execute([
            $menuId
        ]);

        $message = 'Le menu a été réactivé.';
    }
}

$requeteMenus = $db->query(
    'SELECT *
     FROM menus
     ORDER BY id DESC'
);

$menus = $requeteMenus->fetchAll();

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
        Gestion des menus - Vite & Gourmand
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
        Gestion des menus
    </p>

</header>

<nav>

    <a href="index.php">
        Accueil
    </a>

    <a href="menus.php">
        Nos menus
    </a>

    <a href="compte.php">
        Mon compte
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
        Ajouter un menu
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
        action="gestion-menus.php"
    >

        <input
            type="hidden"
            name="action"
            value="ajouter"
        >

        <label for="titre">
            Titre du menu :
        </label>

        <br>

        <input
            type="text"
            id="titre"
            name="titre"
            required
        >

        <br><br>


        <label for="description">
            Description :
        </label>

        <br>

        <textarea
            id="description"
            name="description"
            rows="4"
            required
        ></textarea>

        <br><br>


        <label for="theme">
            Thème :
        </label>

        <br>

        <input
            type="text"
            id="theme"
            name="theme"
            placeholder="classique, evenement, noel..."
            required
        >

        <br><br>


        <label for="regime">
            Régime :
        </label>

        <br>

        <input
            type="text"
            id="regime"
            name="regime"
            placeholder="classique, vegetarien, vegan..."
            required
        >

        <br><br>


        <label for="allergenes">
            Allergènes :
        </label>

        <br>

        <input
            type="text"
            id="allergenes"
            name="allergenes"
        >

        <br><br>


        <label for="minimum_personnes">
            Nombre minimum de personnes :
        </label>

        <br>

        <input
            type="number"
            id="minimum_personnes"
            name="minimum_personnes"
            min="1"
            required
        >

        <br><br>


        <label for="prix_minimum">
            Prix par personne (€) :
        </label>

        <br>

        <input
            type="number"
            id="prix_minimum"
            name="prix_minimum"
            min="0"
            step="0.01"
            required
        >

        <br><br>


        <label for="stock">
            Stock :
        </label>

        <br>

        <input
            type="number"
            id="stock"
            name="stock"
            min="0"
            value="1"
            required
        >

        <br><br>


        <label for="conditions">
            Conditions :
        </label>

        <br>

        <textarea
            id="conditions"
            name="conditions"
            rows="3"
            required
        ></textarea>

        <br><br>

        <button
            class="btn"
            type="submit"
        >
            Ajouter le menu
        </button>

    </form>

</div>


<h2>
    Liste des menus
</h2>


<?php foreach ($menus as $menu): ?>

<div class="card">

    <h3>

        <?= htmlspecialchars(
            $menu['titre'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </h3>

    <p>

        <strong>
            Description :
        </strong>

        <?= htmlspecialchars(
            $menu['description'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>

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
            Prix :
        </strong>

        <?= number_format(
            (float) $menu['prix_minimum'],
            2,
            ',',
            ' '
        ) ?>

        €

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
            Stock :
        </strong>

        <?= (int) $menu['stock'] ?>

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
            Statut :
        </strong>

<?php if ((int) $menu['actif'] === 1): ?>

        Actif

<?php else: ?>

        Désactivé

<?php endif; ?>

    </p>


    <h3>
        Modifier ce menu
    </h3>

    <form
        method="POST"
        action="gestion-menus.php"
    >

        <input
            type="hidden"
            name="action"
            value="modifier"
        >

        <input
            type="hidden"
            name="menu_id"
            value="<?= (int) $menu['id'] ?>"
        >


        <label>
            Titre :
        </label>

        <br>

        <input
            type="text"
            name="titre"
            value="<?= htmlspecialchars(
                $menu['titre'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            required
        >

        <br><br>


        <label>
            Description :
        </label>

        <br>

        <textarea
            name="description"
            rows="4"
            required
        ><?= htmlspecialchars(
            $menu['description'],
            ENT_QUOTES,
            'UTF-8'
        ) ?></textarea>

        <br><br>


        <label>
            Thème :
        </label>

        <br>

        <input
            type="text"
            name="theme"
            value="<?= htmlspecialchars(
                $menu['theme'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            required
        >

        <br><br>


        <label>
            Régime :
        </label>

        <br>

        <input
            type="text"
            name="regime"
            value="<?= htmlspecialchars(
                $menu['regime'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            required
        >

        <br><br>


        <label>
            Allergènes :
        </label>

        <br>

        <input
            type="text"
            name="allergenes"
            value="<?= htmlspecialchars(
                $menu['allergenes'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

        <br><br>


        <label>
            Minimum de personnes :
        </label>

        <br>

        <input
            type="number"
            name="minimum_personnes"
            min="1"
            value="<?= (int) $menu['minimum_personnes'] ?>"
            required
        >

        <br><br>


        <label>
            Prix par personne (€) :
        </label>

        <br>

        <input
            type="number"
            name="prix_minimum"
            min="0"
            step="0.01"
            value="<?= htmlspecialchars(
                $menu['prix_minimum'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
            required
        >

        <br><br>


        <label>
            Stock :
        </label>

        <br>

        <input
            type="number"
            name="stock"
            min="0"
            value="<?= (int) $menu['stock'] ?>"
            required
        >

        <br><br>


        <label>
            Conditions :
        </label>

        <br>

        <textarea
            name="conditions"
            rows="3"
            required
        ><?= htmlspecialchars(
            $menu['conditions'],
            ENT_QUOTES,
            'UTF-8'
        ) ?></textarea>

        <br><br>

        <button
            class="btn"
            type="submit"
        >
            Enregistrer les modifications
        </button>

    </form>

    <br>


<?php if ((int) $menu['actif'] === 1): ?>

    <form
        method="POST"
        action="gestion-menus.php"
    >

        <input
            type="hidden"
            name="action"
            value="desactiver"
        >

        <input
            type="hidden"
            name="menu_id"
            value="<?= (int) $menu['id'] ?>"
        >

        <button
            class="btn"
            type="submit"
        >
            Désactiver le menu
        </button>

    </form>

<?php else: ?>

    <form
        method="POST"
        action="gestion-menus.php"
    >

        <input
            type="hidden"
            name="action"
            value="activer"
        >

        <input
            type="hidden"
            name="menu_id"
            value="<?= (int) $menu['id'] ?>"
        >

        <button
            class="btn"
            type="submit"
        >
            Réactiver le menu
        </button>

    </form>

<?php endif; ?>

</div>

<?php endforeach; ?>

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