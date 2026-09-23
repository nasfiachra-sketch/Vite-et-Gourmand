<?php

session_start();

require 'config.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}

$message = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $commandeId = (int) $_POST['commande_id'];
    $note = (int) $_POST['note'];
    $commentaire = trim($_POST['commentaire']);

    if ($note < 1 || $note > 5) {
        $erreur = 'La note doit être comprise entre 1 et 5.';
    } elseif ($commentaire === '') {
        $erreur = 'Le commentaire est obligatoire.';
    } else {

        $verification = $db->prepare(
            'SELECT id
             FROM commandes
             WHERE id = ?
             AND utilisateur_id = ?
             AND statut = ?'
        );

        $verification->execute([
            $commandeId,
            $_SESSION['utilisateur_id'],
            'completed'
        ]);

        $commande = $verification->fetch();

        if (!$commande) {

            $erreur = 'Cette commande ne permet pas encore de déposer un avis.';

        } else {

            $requete = $db->prepare(
                'INSERT INTO avis
                (utilisateur_id, commande_id, note, commentaire, statut)
                VALUES (?, ?, ?, ?, ?)'
            );

            $requete->execute([
                $_SESSION['utilisateur_id'],
                $commandeId,
                $note,
                $commentaire,
                'en_attente'
            ]);

            $message = 'Merci ! Votre avis a été envoyé pour validation.';
        }
    }
}

$requeteCommandes = $db->prepare(
    'SELECT commandes.id, menus.titre
     FROM commandes
     INNER JOIN menus ON commandes.menu_id = menus.id
     WHERE commandes.utilisateur_id = ?
     AND commandes.statut = ?
     ORDER BY commandes.id DESC'
);

$requeteCommandes->execute([
    $_SESSION['utilisateur_id'],
    'completed'
]);

$commandes = $requeteCommandes->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Donner un avis - Vite & Gourmand</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header>
    <h1>Vite & Gourmand</h1>
    <p>Donner votre avis</p>
</header>

<nav>
    <a href="index.html">Accueil</a>
    <a href="menus.php">Nos menus</a>
    <a href="compte.php">Mon compte</a>
    <a href="logout.php">Déconnexion</a>
</nav>

<main>

<section>

    <h2>Votre avis</h2>

<?php if ($message !== ''): ?>

    <div class="card">
        <p>
            <strong>
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </strong>
        </p>
    </div>

<?php endif; ?>

<?php if ($erreur !== ''): ?>

    <div class="card">
        <p>
            <strong>
                <?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?>
            </strong>
        </p>
    </div>

<?php endif; ?>

<?php if (count($commandes) === 0): ?>

    <div class="card">

        <p>
            Vous pourrez laisser un avis lorsque votre commande sera terminée.
        </p>

    </div>

<?php else: ?>

<?php foreach ($commandes as $commande): ?>

    <div class="card">

        <h3>
            Commande n°<?= (int) $commande['id'] ?>
        </h3>

        <p>
            Menu :
            <?= htmlspecialchars($commande['titre'], ENT_QUOTES, 'UTF-8') ?>
        </p>

        <form method="POST" action="avis.php">

            <input
                type="hidden"
                name="commande_id"
                value="<?= (int) $commande['id'] ?>"
            >

            <label for="note-<?= (int) $commande['id'] ?>">
                Note :
            </label>

            <br>

            <select
                id="note-<?= (int) $commande['id'] ?>"
                name="note"
                required
            >
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Très bien</option>
                <option value="3">3 - Bien</option>
                <option value="2">2 - Moyen</option>
                <option value="1">1 - Mauvais</option>
            </select>

            <br><br>

            <label for="commentaire-<?= (int) $commande['id'] ?>">
                Commentaire :
            </label>

            <br>

            <textarea
                id="commentaire-<?= (int) $commande['id'] ?>"
                name="commentaire"
                rows="5"
                required
            ></textarea>

            <br><br>

            <button class="btn" type="submit">
                Envoyer mon avis
            </button>

        </form>

    </div>

<?php endforeach; ?>

<?php endif; ?>

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

</body>
</html>