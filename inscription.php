<?php

require 'config.php';

$message = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);
    $password = $_POST['password'];
    $passwordConfirm = $_POST['password-confirm'];

    if (
        $nom === '' ||
        $prenom === '' ||
        $telephone === '' ||
        $email === '' ||
        $adresse === '' ||
        $password === ''
    ) {

        $erreur = 'Tous les champs sont obligatoires.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erreur = 'Adresse e-mail invalide.';

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

        $verification = $db->prepare(
            'SELECT id
             FROM utilisateurs
             WHERE LOWER(email) = LOWER(?)'
        );

        $verification->execute([
            $email
        ]);

        if ($verification->fetch()) {

            $erreur = 'Cette adresse e-mail est déjà utilisée.';

        } else {

            $motDePasseHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $requete = $db->prepare(
                'INSERT INTO utilisateurs
                (
                    nom,
                    prenom,
                    telephone,
                    email,
                    adresse,
                    mot_de_passe,
                    role,
                    actif
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)'
            );

            $requete->execute([
                $nom,
                $prenom,
                $telephone,
                $email,
                $adresse,
                $motDePasseHash,
                'user'
            ]);

            $sujet = 'Bienvenue chez Vite & Gourmand';

            $messageEmail =
                "Bonjour " . $prenom . ",\n\n"
                . "Bienvenue chez Vite & Gourmand !\n\n"
                . "Votre compte client a bien été créé.\n"
                . "Vous pouvez maintenant vous connecter et découvrir nos menus.\n\n"
                . "À bientôt,\n"
                . "Vite & Gourmand";

            @mail(
                $email,
                $sujet,
                $messageEmail
            );

            $message =
                'Votre compte a été créé avec succès. '
                . 'Un e-mail de bienvenue a été envoyé.';
        }
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
        Créer un compte - Vite & Gourmand
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
        Créer votre compte
    </p>

</header>

<nav>

    <a href="index.php">
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

    <a href="contact.html">
        Contact
    </a>

</nav>

<main>

<section>

    <h2>
        Créer un compte client
    </h2>


<?php if ($message !== ''): ?>

    <div class="card">

        <p>
            <?= htmlspecialchars(
                $message,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
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
            action="inscription.php"
        >

            <label for="nom">
                Nom :
            </label>

            <br>

            <input
                type="text"
                id="nom"
                name="nom"
                required
            >

            <br><br>


            <label for="prenom">
                Prénom :
            </label>

            <br>

            <input
                type="text"
                id="prenom"
                name="prenom"
                required
            >

            <br><br>


            <label for="telephone">
                Téléphone :
            </label>

            <br>

            <input
                type="tel"
                id="telephone"
                name="telephone"
                required
            >

            <br><br>


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


            <label for="adresse">
                Adresse :
            </label>

            <br>

            <input
                type="text"
                id="adresse"
                name="adresse"
                required
            >

            <br><br>


            <label for="password">
                Mot de passe :
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
                Confirmer le mot de passe :
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
                Créer mon compte
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