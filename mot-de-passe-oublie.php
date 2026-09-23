<?php

session_start();

require 'config.php';

$message = '';
$erreur = '';

$db->exec(
    'CREATE TABLE IF NOT EXISTS tokens_mot_de_passe (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        utilisateur_id INTEGER NOT NULL,
        token TEXT NOT NULL,
        date_expiration DATETIME NOT NULL,
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
    )'
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);

    if ($email === '') {

        $erreur = 'Veuillez saisir votre adresse e-mail.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erreur = 'Adresse e-mail invalide.';

    } else {

        $requete = $db->prepare(
            'SELECT id, email
             FROM utilisateurs
             WHERE LOWER(email) = LOWER(?)
             AND actif = 1'
        );

        $requete->execute([$email]);

        $utilisateur = $requete->fetch();

        if ($utilisateur) {

            $token = bin2hex(random_bytes(32));

            $expiration = date(
                'Y-m-d H:i:s',
                time() + 3600
            );

            $suppression = $db->prepare(
                'DELETE FROM tokens_mot_de_passe
                 WHERE utilisateur_id = ?'
            );

            $suppression->execute([
                $utilisateur['id']
            ]);

            $requeteToken = $db->prepare(
                'INSERT INTO tokens_mot_de_passe
                (utilisateur_id, token, date_expiration)
                VALUES (?, ?, ?)'
            );

            $requeteToken->execute([
                $utilisateur['id'],
                $token,
                $expiration
            ]);

            $lien = 'http://localhost:8000/nouveau-mot-de-passe.php?token=' . $token;

            $sujet = 'Réinitialisation de votre mot de passe - Vite & Gourmand';

            $messageEmail =
                "Bonjour,\n\n"
                . "Pour réinitialiser votre mot de passe, cliquez sur le lien suivant :\n\n"
                . $lien
                . "\n\n"
                . "Ce lien est valable pendant 1 heure.\n\n"
                . "Vite & Gourmand";

            @mail(
                $utilisateur['email'],
                $sujet,
                $messageEmail
            );
        }

        $message =
            'Si cette adresse e-mail correspond à un compte actif, '
            . 'un lien de réinitialisation a été envoyé.';
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

    <title>Mot de passe oublié - Vite & Gourmand</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<header>

    <h1>Vite & Gourmand</h1>

    <p>
        Mot de passe oublié
    </p>

</header>

<nav>

    <a href="index.html">
        Accueil
    </a>

    <a href="menus.php">
        Nos menus
    </a>

    <a href="connexion.php">
        Connexion
    </a>

    <a href="inscription.php">
        Créer un compte
    </a>

    <a href="contact.php">
        Contact
    </a>

</nav>

<main>

<section>

    <h2>
        Réinitialiser mon mot de passe
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

        <p>
            Saisissez votre adresse e-mail pour recevoir
            un lien de réinitialisation.
        </p>

        <form
            method="POST"
            action="mot-de-passe-oublie.php"
        >

            <label for="email">
                Adresse e-mail :
            </label>

            <br>

            <input
                type="email"
                id="email"
                name="email"
                autocomplete="email"
                required
            >

            <br><br>

            <button
                class="btn"
                type="submit"
            >
                Réinitialiser mon mot de passe
            </button>

        </form>

        <p>
            <a href="connexion.php">
                Retour à la connexion
            </a>
        </p>

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
```
