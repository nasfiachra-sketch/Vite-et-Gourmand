```php id="y8m1qa"
<?php

session_start();

require 'config.php';

$message = '';
$erreur = '';

if (!isset($_GET['token']) || $_GET['token'] === '') {
    die('Lien de réinitialisation invalide.');
}

$token = trim($_GET['token']);

$requete = $db->prepare(
    'SELECT
        tokens_mot_de_passe.id,
        tokens_mot_de_passe.utilisateur_id,
        tokens_mot_de_passe.date_expiration
     FROM tokens_mot_de_passe
     WHERE tokens_mot_de_passe.token = ?'
);

$requete->execute([$token]);

$tokenData = $requete->fetch();

if (!$tokenData) {
    die('Lien de réinitialisation invalide ou déjà utilisé.');
}

if (strtotime($tokenData['date_expiration']) < time()) {
    die('Ce lien de réinitialisation a expiré.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'];
    $passwordConfirm = $_POST['password-confirm'];

    if ($password === '') {

        $erreur = 'Veuillez saisir un nouveau mot de passe.';

    } elseif (strlen($password) < 10) {

        $erreur = 'Le mot de passe doit contenir au moins 10 caractères.';

    } elseif (!preg_match('/[A-Z]/', $password)) {

        $erreur = 'Le mot de passe doit contenir une majuscule.';

    } elseif (!preg_match('/[a-z]/', $password)) {

        $erreur = 'Le mot de passe doit contenir une minuscule.';

    } elseif (!preg_match('/[0-9]/', $password)) {

        $erreur = 'Le mot de passe doit contenir un chiffre.';

    } elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {

        $erreur = 'Le mot de passe doit contenir un caractère spécial.';

    } elseif ($password !== $passwordConfirm) {

        $erreur = 'Les deux mots de passe ne correspondent pas.';

    } else {

        $motDePasseHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $modification = $db->prepare(
            'UPDATE utilisateurs
             SET mot_de_passe = ?
             WHERE id = ?'
        );

        $modification->execute([
            $motDePasseHash,
            $tokenData['utilisateur_id']
        ]);

        $suppression = $db->prepare(
            'DELETE FROM tokens_mot_de_passe
             WHERE id = ?'
        );

        $suppression->execute([
            $tokenData['id']
        ]);

        $message =
            'Votre mot de passe a été modifié avec succès.';
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

    <title>Nouveau mot de passe - Vite & Gourmand</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<header>

    <h1>Vite & Gourmand</h1>

    <p>
        Nouveau mot de passe
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

    <a href="contact.php">
        Contact
    </a>

</nav>

<main>

<section>

    <h2>
        Créer un nouveau mot de passe
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
            href="connexion.php"
        >
            Se connecter
        </a>

    </div>

<?php else: ?>


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
            action="nouveau-mot-de-passe.php?token=<?= htmlspecialchars(
                $token,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

            <label for="password">
                Nouveau mot de passe :
            </label>

            <br>

            <input
                type="password"
                id="password"
                name="password"
                minlength="10"
                autocomplete="new-password"
                required
            >

            <p>
                Minimum 10 caractères avec une majuscule,
                une minuscule, un chiffre et un caractère spécial.
            </p>

            <label for="password-confirm">
                Confirmer le nouveau mot de passe :
            </label>

            <br>

            <input
                type="password"
                id="password-confirm"
                name="password-confirm"
                minlength="10"
                autocomplete="new-password"
                required
            >

            <br><br>

            <button
                class="btn"
                type="submit"
            >
                Modifier mon mot de passe
            </button>

        </form>

    </div>

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
</htm
```
