```php
<?php

session_start();

require 'config.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}

$erreur = '';
$message = '';

if (!isset($_GET['commande_id']) || !is_numeric($_GET['commande_id'])) {
    die('Commande introuvable.');
}

$commandeId = (int) $_GET['commande_id'];

$requete = $db->prepare(
    'SELECT
        commandes.*,
        menus.titre,
        menus.prix_minimum,
        menus.minimum_personnes
     FROM commandes
     INNER JOIN menus ON commandes.menu_id = menus.id
     WHERE commandes.id = ?
     AND commandes.utilisateur_id = ?'
);

$requete->execute([
    $commandeId,
    $_SESSION['utilisateur_id']
]);

$commande = $requete->fetch();

if (!$commande) {
    die('Commande introuvable.');
}

if ($commande['statut'] !== 'en_attente') {
    die('Cette commande ne peut plus être modifiée.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombrePersonnes = (int) $_POST['nombre_personnes'];
    $adresse = trim($_POST['adresse_livraison']);
    $dateLivraison = trim($_POST['date_livraison']);
    $heureLivraison = trim($_POST['heure_livraison']);

    if (
        $nombrePersonnes < (int) $commande['minimum_personnes'] ||
        $adresse === '' ||
        $dateLivraison === '' ||
        $heureLivraison === ''
    ) {

        $erreur = 'Veuillez remplir tous les champs et respecter le nombre minimum de personnes.';

    } else {

        $prixMenu = (float) $commande['prix_minimum'];
        $distanceKm = (float) $commande['distance_km'];

        $prixMenus = $prixMenu * $nombrePersonnes;

        $livraison = 5 + ($distanceKm * 0.59);

        $montantAvantRemise = $prixMenus + $livraison;

        $remise = 0;

        if ($nombrePersonnes >= ((int) $commande['minimum_personnes'] + 5)) {
            $remise = $prixMenus * 0.10;
        }

        $montantTotal = $montantAvantRemise - $remise;

        $modification = $db->prepare(
            'UPDATE commandes
             SET nombre_personnes = ?,
                 adresse_livraison = ?,
                 date_livraison = ?,
                 heure_livraison = ?,
                 livraison = ?,
                 remise = ?,
                 montant_total = ?
             WHERE id = ?
             AND utilisateur_id = ?
             AND statut = ?'
        );

        $modification->execute([
            $nombrePersonnes,
            $adresse,
            $dateLivraison,
            $heureLivraison,
            $livraison,
            $remise,
            $montantTotal,
            $commandeId,
            $_SESSION['utilisateur_id'],
            'en_attente'
        ]);

        $message = 'Votre commande a été modifiée avec succès.';

        $commande['nombre_personnes'] = $nombrePersonnes;
        $commande['adresse_livraison'] = $adresse;
        $commande['date_livraison'] = $dateLivraison;
        $commande['heure_livraison'] = $heureLivraison;
        $commande['livraison'] = $livraison;
        $commande['remise'] = $remise;
        $commande['montant_total'] = $montantTotal;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Modifier une commande - Vite & Gourmand</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<header>

    <h1>Vite & Gourmand</h1>

    <p>
        Modifier ma commande
    </p>

</header>

<nav>

    <a href="index.html">
        Accueil
    </a>

    <a href="menus.php">
        Nos menus
    </a>

    <a href="compte.php">
        Mon compte
    </a>

    <a href="contact.php">
        Contact
    </a>

    <a href="logout.php">
        Déconnexion
    </a>

</nav>

<main>

<section>

    <h2>
        Modifier la commande n°<?= (int) $commande['id'] ?>
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

        <a
            class="btn"
            href="compte.php"
        >
            Retour à mon compte
        </a>

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
            <?= htmlspecialchars(
                $commande['titre'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </h3>

        <p>
            Le choix du menu ne peut pas être modifié.
        </p>

        <p>
            Minimum :
            <?= (int) $commande['minimum_personnes'] ?>
            personnes
        </p>

        <form
            method="POST"
            action="modifier-commande.php?commande_id=<?= (int) $commande['id'] ?>"
        >

            <label for="nombre_personnes">
                Nombre de personnes :
            </label>

            <br>

            <input
                type="number"
                id="nombre_personnes"
                name="nombre_personnes"
                min="<?= (int) $commande['minimum_personnes'] ?>"
                value="<?= (int) $commande['nombre_personnes'] ?>"
                required
            >

            <br><br>


            <label for="adresse_livraison">
                Adresse de livraison :
            </label>

            <br>

            <input
                type="text"
                id="adresse_livraison"
                name="adresse_livraison"
                value="<?= htmlspecialchars(
                    $commande['adresse_livraison'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >

            <br><br>


            <label for="date_livraison">
                Date de livraison :
            </label>

            <br>

            <input
                type="date"
                id="date_livraison"
                name="date_livraison"
                value="<?= htmlspecialchars(
                    $commande['date_livraison'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >

            <br><br>


            <label for="heure_livraison">
                Heure de livraison :
            </label>

            <br>

            <input
                type="time"
                id="heure_livraison"
                name="heure_livraison"
```
