<?php

session_start();

require 'config.php';
require 'email.php';

if (
    !isset($_SESSION['utilisateur_id']) ||
    $_SESSION['role'] !== 'admin'
) {
    die('Accès réservé à l’administrateur.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        isset($_POST['action']) &&
        $_POST['action'] === 'statut_commande'
    ) {

        $commandeId = (int) $_POST['commande_id'];
        $nouveauStatut = trim($_POST['statut']);

        $statutsAutorises = [
            'en_attente',
            'acceptee',
            'en_preparation',
            'en_livraison',
            'livree',
            'attente_materiel',
            'completed',
            'annulee'
        ];

        if (in_array($nouveauStatut, $statutsAutorises, true)) {

            $verification = $db->prepare(
                'SELECT
                    commandes.statut,
                    utilisateurs.prenom,
                    utilisateurs.email,
                    menus.titre
                 FROM commandes
                 INNER JOIN utilisateurs
                    ON commandes.utilisateur_id = utilisateurs.id
                 INNER JOIN menus
                    ON commandes.menu_id = menus.id
                 WHERE commandes.id = ?'
            );

            $verification->execute([
                $commandeId
            ]);

            $commandeExistante = $verification->fetch();

            if ($commandeExistante) {

                $ancienStatut = $commandeExistante['statut'];

                $requete = $db->prepare(
                    'UPDATE commandes
                     SET statut = ?
                     WHERE id = ?'
                );

                $requete->execute([
                    $nouveauStatut,
                    $commandeId
                ]);

                if ($ancienStatut !== $nouveauStatut) {

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
                        $ancienStatut,
                        $nouveauStatut
                    ]);


                    $sujet =
                        'Mise à jour de votre commande - Vite & Gourmand';

                    $messageEmail =
                        "Bonjour "
                        . $commandeExistante['prenom']
                        . ",\n\n"
                        . "Le statut de votre commande n°"
                        . $commandeId
                        . " a été mis à jour.\n\n"
                        . "Menu : "
                        . $commandeExistante['titre']
                        . "\n"
                        . "Ancien statut : "
                        . $ancienStatut
                        . "\n"
                        . "Nouveau statut : "
                        . $nouveauStatut
                        . "\n\n"
                        . "Merci pour votre confiance.\n\n"
                        . "Vite & Gourmand";

                    envoyerEmail(
                        $commandeExistante['email'],
                        $sujet,
                        $messageEmail
                    );
                }

                $message =
                    'Le statut de la commande a été mis à jour.';
            }
        }
    }


    if (
        isset($_POST['action']) &&
        $_POST['action'] === 'avis'
    ) {

        $avisId = (int) $_POST['avis_id'];
        $statutAvis = trim($_POST['statut']);

        if (
            in_array(
                $statutAvis,
                ['valide', 'refuse'],
                true
            )
        ) {

            $requete = $db->prepare(
                'UPDATE avis
                 SET statut = ?
                 WHERE id = ?'
            );

            $requete->execute([
                $statutAvis,
                $avisId
            ]);

            $message =
                'Le statut de l’avis a été mis à jour.';
        }
    }
}


$requeteCommandes = $db->query(
    'SELECT
        commandes.id,
        commandes.nombre_personnes,
        commandes.adresse_livraison,
        commandes.date_livraison,
        commandes.heure_livraison,
        commandes.montant_total,
        commandes.statut,
        commandes.date_creation,
        menus.titre,
        utilisateurs.nom,
        utilisateurs.prenom,
        utilisateurs.email
     FROM commandes
     INNER JOIN menus
        ON commandes.menu_id = menus.id
     INNER JOIN utilisateurs
        ON commandes.utilisateur_id = utilisateurs.id
     ORDER BY commandes.id DESC'
);

$commandes = $requeteCommandes->fetchAll();


$requeteAvis = $db->query(
    'SELECT
        avis.id,
        avis.note,
        avis.commentaire,
        avis.statut,
        avis.date_creation,
        utilisateurs.nom,
        utilisateurs.prenom,
        menus.titre
     FROM avis
     INNER JOIN utilisateurs
        ON avis.utilisateur_id = utilisateurs.id
     INNER JOIN commandes
        ON avis.commande_id = commandes.id
     INNER JOIN menus
        ON commandes.menu_id = menus.id
     ORDER BY avis.id DESC'
);

$avis = $requeteAvis->fetchAll();

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
        Administration - Vite & Gourmand
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
        Administration
    </p>

</header>

<nav>

    <a href="index.php">
        Accueil
    </a>

    <a href="menus.php">
        Nos menus
    </a>

    <a href="admin.php">
        Administration
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

    <a href="gestion-employes.php">
        Gestion des employés
    </a>

    <a href="logout.php">
        Déconnexion
    </a>

</nav>

<main>

<section>

    <h2>
        Gestion des commandes
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


<?php if (count($commandes) === 0): ?>

    <div class="card">

        <p>
            Aucune commande enregistrée.
        </p>

    </div>

<?php else: ?>


<?php foreach ($commandes as $commande): ?>

    <div class="card">

        <h3>
            Commande n°<?= (int) $commande['id'] ?>
        </h3>

        <p>
            <strong>
                Client :
            </strong>

            <?= htmlspecialchars(
                $commande['prenom'] . ' ' . $commande['nom'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                E-mail :
            </strong>

            <?= htmlspecialchars(
                $commande['email'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

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
                Personnes :
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
                Montant :
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

        <form
            method="POST"
            action="admin.php"
        >

            <input
                type="hidden"
                name="action"
                value="statut_commande"
            >

            <input
                type="hidden"
                name="commande_id"
                value="<?= (int) $commande['id'] ?>"
            >

            <label
                for="statut-<?= (int) $commande['id'] ?>"
            >
                Modifier le statut :
            </label>

            <br>

            <select
                id="statut-<?= (int) $commande['id'] ?>"
                name="statut"
            >

                <option value="en_attente">
                    En attente
                </option>

                <option value="acceptee">
                    Acceptée
                </option>

                <option value="en_preparation">
                    En préparation
                </option>

                <option value="en_livraison">
                    En livraison
                </option>

                <option value="livree">
                    Livrée
                </option>

                <option value="attente_materiel">
                    En attente de matériel
                </option>

                <option value="completed">
                    Terminée
                </option>

                <option value="annulee">
                    Annulée
                </option>

            </select>

            <br><br>

            <button
                class="btn"
                type="submit"
            >
                Mettre à jour
            </button>

        </form>

    </div>

<?php endforeach; ?>

<?php endif; ?>


    <h2>
        Validation des avis clients
    </h2>


<?php if (count($avis) === 0): ?>

    <div class="card">

        <p>
            Aucun avis client pour le moment.
        </p>

    </div>

<?php else: ?>


<?php foreach ($avis as $unAvis): ?>

    <div class="card">

        <h3>
            <?= htmlspecialchars(
                $unAvis['prenom'] . ' ' . $unAvis['nom'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </h3>

        <p>
            <strong>
                Menu :
            </strong>

            <?= htmlspecialchars(
                $unAvis['titre'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Note :
            </strong>

            <?= (int) $unAvis['note'] ?>/5
        </p>

        <p>
            <strong>
                Commentaire :
            </strong>

            <?= htmlspecialchars(
                $unAvis['commentaire'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>
                Statut :
            </strong>

            <?= htmlspecialchars(
                $unAvis['statut'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>


<?php if ($unAvis['statut'] === 'en_attente'): ?>

        <form
            method="POST"
            action="admin.php"
        >

            <input
                type="hidden"
                name="action"
                value="avis"
            >

            <input
                type="hidden"
                name="avis_id"
                value="<?= (int) $unAvis['id'] ?>"
            >

            <button
                class="btn"
                type="submit"
                name="statut"
                value="valide"
            >
                Valider l'avis
            </button>

            <button
                class="btn"
                type="submit"
                name="statut"
                value="refuse"
            >
                Refuser l'avis
            </button>

        </form>

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