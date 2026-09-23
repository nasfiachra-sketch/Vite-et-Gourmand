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

        $nom = trim($_POST['nom']);
        $description = trim($_POST['description']);
        $type = trim($_POST['type']);
        $allergenes = trim($_POST['allergenes']);

        if (
            $nom === '' ||
            $description === '' ||
            $type === ''
        ) {

            $erreur =
                'Veuillez remplir tous les champs obligatoires.';

        } else {

            $requete = $db->prepare(
                'INSERT INTO plats
                (
                    nom,
                    description,
                    type,
                    allergenes
                )
                VALUES (?, ?, ?, ?)'
            );

            $requete->execute([
                $nom,
                $description,
                $type,
                $allergenes
            ]);

            $message =
                'Le plat a été ajouté avec succès.';
        }
    }

    if ($action === 'modifier') {

        $platId = (int) $_POST['plat_id'];

        $nom = trim($_POST['nom']);
        $description = trim($_POST['description']);
        $type = trim($_POST['type']);
        $allergenes = trim($_POST['allergenes']);

        if (
            $platId < 1 ||
            $nom === '' ||
            $description === '' ||
            $type === ''
        ) {

            $erreur =
                'Veuillez remplir tous les champs obligatoires.';

        } else {

            $requete = $db->prepare(
                'UPDATE plats
                 SET
                    nom = ?,
                    description = ?,
                    type = ?,
                    allergenes = ?
                 WHERE id = ?'
            );

            $requete->execute([
                $nom,
                $description,
                $type,
                $allergenes,
                $platId
            ]);

            $message =
                'Le plat a été modifié avec succès.';
        }
    }

    if ($action === 'supprimer') {

        $platId = (int) $_POST['plat_id'];

        $requete = $db->prepare(
            'DELETE FROM plats
             WHERE id = ?'
        );

        $requete->execute([
            $platId
        ]);

        $message =
            'Le plat a été supprimé.';
    }
}

$requetePlats = $db->query(
    'SELECT *
     FROM plats
     ORDER BY id DESC'
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
        Gestion des plats - Vite & Gourmand
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
        Gestion des plats
    </p>

</header>

<nav>

    <a href="index.php">
        Accueil
    </a>

    <a href="menus.php">
        Nos menus
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
        Ajouter un plat
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
        action="gestion-plats.php"
    >

        <input
            type="hidden"
            name="action"
            value="ajouter"
        >

        <label for="nom">
            Nom du plat :
        </label>

        <br>

        <input
            type="text"
            id="nom"
            name="nom"
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


        <label for="type">
            Type :
        </label>

        <br>

        <select
            id="type"
            name="type"
            required
        >

            <option value="">
                Choisir
            </option>

            <option value="entrée">
                Entrée
            </option>

            <option value="plat">
                Plat
            </option>

            <option value="dessert">
                Dessert
            </option>

        </select>

        <br><br>


        <label for="allergenes">
            Allergènes :
        </label>

        <br>

        <input
            type="text"
            id="allergenes"
            name="allergenes"
            placeholder="Gluten, lait, œufs..."
        >

        <br><br>

        <button
            class="btn"
            type="submit"
        >
            Ajouter le plat
        </button>

    </form>

</div>


<h2>
    Liste des plats
</h2>


<?php if (count($plats) === 0): ?>

    <div class="card">

        <p>
            Aucun plat enregistré.
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
                Allergènes :
            </strong>

            <?= htmlspecialchars(
                $plat['allergenes'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </p>


        <h3>
            Modifier ce plat
        </h3>


        <form
            method="POST"
            action="gestion-plats.php"
        >

            <input
                type="hidden"
                name="action"
                value="modifier"
            >

            <input
                type="hidden"
                name="plat_id"
                value="<?= (int) $plat['id'] ?>"
            >


            <label>
                Nom du plat :
            </label>

            <br>

            <input
                type="text"
                name="nom"
                value="<?= htmlspecialchars(
                    $plat['nom'],
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
                $plat['description'],
                ENT_QUOTES,
                'UTF-8'
            ) ?></textarea>

            <br><br>


            <label>
                Type :
            </label>

            <br>

            <select
                name="type"
                required
            >

<?php if ($plat['type'] === 'entrée'): ?>

                <option value="entrée" selected>
                    Entrée
                </option>

                <option value="plat">
                    Plat
                </option>

                <option value="dessert">
                    Dessert
                </option>

<?php elseif ($plat['type'] === 'plat'): ?>

                <option value="entrée">
                    Entrée
                </option>

                <option value="plat" selected>
                    Plat
                </option>

                <option value="dessert">
                    Dessert
                </option>

<?php else: ?>

                <option value="entrée">
                    Entrée
                </option>

                <option value="plat">
                    Plat
                </option>

                <option value="dessert" selected>
                    Dessert
                </option>

<?php endif; ?>

            </select>

            <br><br>


            <label>
                Allergènes :
            </label>

            <br>

            <input
                type="text"
                name="allergenes"
                value="<?= htmlspecialchars(
                    $plat['allergenes'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <br><br>

            <button
                class="btn"
                type="submit"
            >
                Enregistrer les modifications
            </button>

        </form>

        <br>


        <form
            method="POST"
            action="gestion-plats.php"
        >

            <input
                type="hidden"
                name="action"
                value="supprimer"
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
                Supprimer le plat
            </button>

        </form>

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