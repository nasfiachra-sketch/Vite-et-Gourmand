<?php

session_start();

require 'config.php';
require 'email.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}

if (!isset($_GET['menu_id']) || !is_numeric($_GET['menu_id'])) {
    die('Menu introuvable.');
}

$menuId = (int) $_GET['menu_id'];

$requete = $db->prepare(
    'SELECT * FROM menus WHERE id = ? AND actif = 1'
);

$requete->execute([$menuId]);

$menu = $requete->fetch();

if (!$menu) {
    die('Menu introuvable.');
}

$requeteUtilisateur = $db->prepare(
    'SELECT nom, prenom, telephone, email, adresse
     FROM utilisateurs
     WHERE id = ? AND actif = 1'
);

$requeteUtilisateur->execute([
    $_SESSION['utilisateur_id']
]);

$utilisateur = $requeteUtilisateur->fetch();

if (!$utilisateur) {
    session_destroy();
    header('Location: connexion.php');
    exit;
}

$erreur = '';
$confirmation = '';

$nombrePersonnes = (int) $menu['minimum_personnes'];
$adresseLivraison = $utilisateur['adresse'];
$dateLivraison = '';
$heureLivraison = '';
$distanceKm = 0;
$livraison = 5;
$remise = 0;
$montantAvantRemise = 0;
$montantTotal = 0;
$commandeId = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombrePersonnes = (int) $_POST['nombre_personnes'];
    $adresseLivraison = trim($_POST['adresse_livraison']);
    $dateLivraison = trim($_POST['date_livraison']);
    $heureLivraison = trim($_POST['heure_livraison']);
    $distanceKm = (float) $_POST['distance_km'];

    $minimumPersonnes = (int) $menu['minimum_personnes'];
    $prixParPersonne = (float) $menu['prix_minimum'];

    if ($nombrePersonnes < $minimumPersonnes) {

        $erreur = 'Le nombre minimum de personnes pour ce menu est de '
            . $minimumPersonnes . '.';

    } elseif ($adresseLivraison === '') {

        $erreur = 'L’adresse de livraison est obligatoire.';

    } elseif ($dateLivraison === '') {

        $erreur = 'La date de livraison est obligatoire.';

    } elseif ($heureLivraison === '') {

        $erreur = 'L’heure de livraison est obligatoire.';

    } elseif ($distanceKm < 0) {

        $erreur = 'La distance ne peut pas être négative.';

    } else {

        /*
         * Frais de livraison :
         * 5 € de base.
         * Puis 0,59 € par kilomètre.
         */
        $livraison = 5 + ($distanceKm * 0.59);

        $montantAvantRemise =
            ($prixParPersonne * $nombrePersonnes) + $livraison;

        /*
         * Remise de 10 % si le client commande
         * au moins 5 personnes de plus que le minimum.
         */
        if ($nombrePersonnes >= ($minimumPersonnes + 5)) {

            $remise = $montantAvantRemise * 0.10;
        }

        $montantTotal = $montantAvantRemise - $remise;

        $requeteCommande = $db->prepare(
            'INSERT INTO commandes
            (
                utilisateur_id,
                menu_id,
                nombre_personnes,
                adresse_livraison,
                date_livraison,
                heure_livraison,
                livraison,
                distance_km,
                remise,
                montant_total,
                statut
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $requeteCommande->execute([
            $_SESSION['utilisateur_id'],
            $menuId,
            $nombrePersonnes,
            $adresseLivraison,
            $dateLivraison,
            $heureLivraison,
            $livraison,
            $distanceKm,
            $remise,
            $montantTotal,
            'en_attente'
        ]);

        $commandeId = $db->lastInsertId();

        /*
         * Enregistrement de la création de la commande
         * dans l'historique des statuts.
         */
        $historique = $db->prepare(
            'INSERT INTO historique_commandes
            (
                commande_id,
                ancien_statut,
                nouveau_statut
            )
            VALUES (?, ?, ?)'
        );

        $historique->execute([
            $commandeId,
            null,
            'en_attente'
        ]);

        /*
         * Envoi de l'e-mail de confirmation.
         */
        $sujet = 'Confirmation de votre commande - Vite & Gourmand';

        $messageEmail =
            "Bonjour " . $utilisateur['prenom'] . ",\n\n"
            . "Votre commande n°" . $commandeId
            . " a bien été enregistrée.\n\n"
            . "Menu : " . $menu['titre'] . "\n"
            . "Nombre de personnes : " . $nombrePersonnes . "\n"
            . "Date de livraison : " . $dateLivraison . "\n"
            . "Heure de livraison : " . $heureLivraison . "\n"
            . "Adresse de livraison : " . $adresseLivraison . "\n\n"
            . "Livraison : "
            . number_format($livraison, 2, ',', ' ')
            . " €\n"
            . "Remise : "
            . number_format($remise, 2, ',', ' ')
            . " €\n"
            . "Montant total : "
            . number_format($montantTotal, 2, ',', ' ')
            . " €\n\n"
            . "Votre commande est actuellement en attente de validation.\n\n"
            . "Merci pour votre confiance.\n\n"
            . "Vite & Gourmand";

        envoyerEmail(
            $utilisateur['email'],
            $sujet,
            $messageEmail
        );

        $confirmation =
            'Votre commande a bien été enregistrée. '
            . 'Un e-mail de confirmation a été envoyé.';
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

    <title>
        Commander - Vite & Gourmand
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
        Passer une commande
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

    <a href="contact.php">
        Contact
    </a>

</nav>

<main>

<section>

    <h2>
        Votre commande
    </h2>


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


<?php if ($confirmation !== ''): ?>

    <div class="card">

        <h3>
            Commande enregistrée
        </h3>

        <p>
            <?= htmlspecialchars(
                $confirmation,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            Numéro de commande :
            <strong>
                <?= (int) $commandeId ?>
            </strong>
        </p>

        <p>
            Prix des menus :
            <strong>
                <?= number_format(
                    $prixParPersonne * $nombrePersonnes,
                    2,
                    ',',
                    ' '
                ) ?> €
            </strong>
        </p>

        <p>
            Livraison :
            <strong>
                <?= number_format(
                    $livraison,
                    2,
                    ',',
                    ' '
                ) ?> €
            </strong>
        </p>

        <p>
            Total avant remise :
            <strong>
                <?= number_format(
                    $montantAvantRemise,
                    2,
                    ',',
                    ' '
                ) ?> €
            </strong>
        </p>

        <p>
            Remise :
            <strong>
                <?= number_format(
                    $remise,
                    2,
                    ',',
                    ' '
                ) ?> €
            </strong>
        </p>

        <p>
            Montant total :
            <strong>
                <?= number_format(
                    $montantTotal,
                    2,
                    ',',
                    ' '
                ) ?> €
            </strong>
        </p>

        <a
            class="btn"
            href="compte.php"
        >
            Voir mon compte
        </a>

    </div>

<?php else: ?>

    <div class="card">

        <h3>
            Menu sélectionné
        </h3>

        <p>
            <strong>
                <?= htmlspecialchars(
                    $menu['titre'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </strong>
        </p>

        <p>
            Prix :
            <?= number_format(
                (float) $menu['prix_minimum'],
                2,
                ',',
                ' '
            ) ?> € / personne
        </p>

        <p>
            Minimum :
            <?= (int) $menu['minimum_personnes'] ?>
            personnes
        </p>

    </div>


    <div class="card">

        <h3>
            Vos informations
        </h3>

        <p>
            <strong>Nom :</strong>
            <?= htmlspecialchars(
                $utilisateur['nom'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>Prénom :</strong>
            <?= htmlspecialchars(
                $utilisateur['prenom'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>Téléphone :</strong>
            <?= htmlspecialchars(
                $utilisateur['telephone'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>E-mail :</strong>
            <?= htmlspecialchars(
                $utilisateur['email'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>Adresse :</strong>
            <?= htmlspecialchars(
                $utilisateur['adresse'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

    </div>


    <div class="card">

        <h3>
            Informations de livraison
        </h3>

        <form
            method="POST"
            action="commande.php?menu_id=<?= $menuId ?>"
        >

            <label for="nombre_personnes">
                Nombre de personnes :
            </label>

            <br>

            <input
                type="number"
                id="nombre_personnes"
                name="nombre_personnes"
                min="<?= (int) $menu['minimum_personnes'] ?>"
                value="<?= (int) $nombrePersonnes ?>"
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
                    $adresseLivraison,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >

            <br><br>


            <label for="distance_km">
                Distance de livraison (km) :
            </label>

            <br>

            <input
                type="number"
                id="distance_km"
                name="distance_km"
                min="0"
                step="0.1"
                value="<?= htmlspecialchars(
                    $distanceKm,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >

            <p>
                Livraison : 5 € + 0,59 € par kilomètre.
            </p>

            <br>


            <label for="date_livraison">
                Date de livraison :
            </label>

            <br>

            <input
                type="date"
                id="date_livraison"
                name="date_livraison"
                value="<?= htmlspecialchars(
                    $dateLivraison,
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
                value="<?= htmlspecialchars(
                    $heureLivraison,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >

            <br><br>

            <button
                class="btn"
                type="submit"
            >
                Valider la commande
            </button>

        </form>

    </div>

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
