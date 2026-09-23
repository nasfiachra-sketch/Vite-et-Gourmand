<?php

session_start();

require 'config.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}

$requete = $db->prepare(
    'SELECT
        nom,
        prenom,
        telephone,
        email,
        adresse,
        role
     FROM utilisateurs
     WHERE id = ?
     AND actif = 1'
);

$requete->execute([
    $_SESSION['utilisateur_id']
]);

$utilisateur = $requete->fetch();

if (!$utilisateur) {
    session_destroy();
    header('Location: connexion.php');
    exit;
}


$requeteCommandes = $db->prepare(
    'SELECT
        commandes.id,
        commandes.nombre_personnes,
        commandes.adresse_livraison,
        commandes.date_livraison,
        commandes.heure_livraison,
        commandes.livraison,
        commandes.distance_km,
        commandes.remise,
        commandes.montant_total,
        commandes.statut,
        commandes.date_creation,
        menus.titre
     FROM commandes
     INNER JOIN menus
        ON commandes.menu_id = menus.id
     WHERE commandes.utilisateur_id = ?
     ORDER BY commandes.id DESC'
);

$requeteCommandes->execute([
    $_SESSION['utilisateur_id']
]);

$commandes = $requeteCommandes->fetchAll();

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
        Mon compte - Vite & Gourmand
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
        Mon compte
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
        Bienvenue
        <?= htmlspecialchars(
            $utilisateur['prenom'],
            ENT_QUOTES,
            'UTF-8'
        ) ?> !
    </h2>


    <div class="card">

        <h3>
            Mes informations
        </h3>

        <p>
            <strong>
                Nom :
            </strong>

            <?= htmlspecialchars(
                $utilisateur['nom'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Prénom :
            </strong>

            <?= htmlspecialchars(
                $utilisateur['prenom'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Téléphone :
            </strong>

            <?= htmlspecialchars(
                $utilisateur['telephone'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                E-mail :
            </strong>

            <?= htmlspecialchars(
                $utilisateur['email'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Adresse :
            </strong>

            <?= htmlspecialchars(
                $utilisateur['adresse'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Type de compte :
            </strong>

            Client
        </p>

        <p>

            <a
                class="btn"
                href="modifier-compte.php"
            >
                Modifier mes informations
            </a>

        </p>

    </div>


    <h2>
        Mes commandes
    </h2>


<?php if (count($commandes) === 0): ?>

    <div class="card">

        <p>
            Vous n'avez pas encore passé de commande.
        </p>

        <a
            class="btn"
            href="menus.php"
        >
            Découvrir les menus
        </a>

    </div>

<?php else: ?>


<?php foreach ($commandes as $commande): ?>

    <div class="card">

        <h3>
            Commande n°<?= (int) $commande['id'] ?>
        </h3>

        <p>
            <strong>
                Menu :
            </strong>

            <?= htmlspecialchars(
                $commande['titre'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Nombre de personnes :
            </strong>

            <?= (int) $commande['nombre_personnes'] ?>
        </p>

        <p>
            <strong>
                Adresse :
            </strong>

            <?= htmlspecialchars(
                $commande['adresse_livraison'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Date :
            </strong>

            <?= htmlspecialchars(
                $commande['date_livraison'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Heure :
            </strong>

            <?= htmlspecialchars(
                $commande['heure_livraison'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Livraison :
            </strong>

            <?= number_format(
                (float) $commande['livraison'],
                2,
                ',',
                ' '
            ) ?> €
        </p>

        <p>
            <strong>
                Distance :
            </strong>

            <?= number_format(
                (float) $commande['distance_km'],
                2,
                ',',
                ' '
            ) ?> km
        </p>

        <p>
            <strong>
                Remise :
            </strong>

            <?= number_format(
                (float) $commande['remise'],
                2,
                ',',
                ' '
            ) ?> €
        </p>

        <p>
            <strong>
                Montant total :
            </strong>

            <?= number_format(
                (float) $commande['montant_total'],
                2,
                ',',
                ' '
            ) ?> €
        </p>

        <p>
            <strong>
                Statut actuel :
            </strong>

            <?= htmlspecialchars(
                $commande['statut'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Date de commande :
            </strong>

            <?= htmlspecialchars(
                $commande['date_creation'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>


        <h3>
            Historique de la commande
        </h3>


<?php

$requeteHistorique = $db->prepare(
    'SELECT
        ancien_statut,
        nouveau_statut,
        date_modification
     FROM historique_commandes
     WHERE commande_id = ?
     ORDER BY id ASC'
);

$requeteHistorique->execute([
    $commande['id']
]);

$historique = $requeteHistorique->fetchAll();

?>


<?php if (count($historique) === 0): ?>

        <p>
            Aucun changement de statut enregistré pour le moment.
        </p>

<?php else: ?>


<?php foreach ($historique as $etape): ?>

        <p>

            <?= $etape['ancien_statut'] === null
                ? 'Commande créée'
                : htmlspecialchars(
                    $etape['ancien_statut'],
                    ENT_QUOTES,
                    'UTF-8'
                )
            ?>

            →
            
            <?= htmlspecialchars(
                $etape['nouveau_statut'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>

            <br>

            <small>
                <?= htmlspecialchars(
                    $etape['date_modification'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </small>

        </p>

<?php endforeach; ?>

<?php endif; ?>


<?php if ($commande['statut'] === 'en_attente'): ?>

        <p>

            <a
                class="btn"
                href="modifier-commande.php?commande_id=<?= (int) $commande['id'] ?>"
            >
                Modifier la commande
            </a>

        </p>

        <p>

            <a
                class="btn"
                href="annuler-commande.php?commande_id=<?= (int) $commande['id'] ?>"
            >
                Annuler la commande
            </a>

        </p>

<?php endif; ?>


<?php if ($commande['statut'] === 'completed'): ?>

        <p>

            <a
                class="btn"
                href="avis.php"
            >
                Donner mon avis
            </a>

        </p>

<?php endif; ?>

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
