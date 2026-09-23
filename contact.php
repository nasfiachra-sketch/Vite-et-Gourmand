<?php

require 'config.php';
require 'email.php';

$message = '';
$erreur = '';

$nom = '';
$email = '';
$sujet = '';
$contenu = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $sujet = trim($_POST['sujet']);
    $contenu = trim($_POST['contenu']);

    if (
        $nom === '' ||
        $email === '' ||
        $sujet === '' ||
        $contenu === ''
    ) {

        $erreur =
            'Tous les champs sont obligatoires.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erreur =
            'Adresse e-mail invalide.';

    } else {

        $sujetEmail =
            'Contact depuis le site - '
            . $sujet;

        $messageEmail =
            "Nouveau message depuis le site Vite & Gourmand.\n\n"
            . "Nom : "
            . $nom
            . "\n"
            . "E-mail : "
            . $email
            . "\n"
            . "Sujet : "
            . $sujet
            . "\n\n"
            . "Message :\n"
            . $contenu;

        $envoye = envoyerEmail(
            'contact@vite-et-gourmand.fr',
            $sujetEmail,
            $messageEmail
        );

        if ($envoye) {

            $message =
                'Votre message a bien été envoyé. '
                . 'Nous vous répondrons prochainement.';

            $nom = '';
            $email = '';
            $sujet = '';
            $contenu = '';

        } else {

            $erreur =
                'Le message n’a pas pu être envoyé. '
                . 'Veuillez réessayer plus tard.';
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
        Contact - Vite & Gourmand
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
        Contact
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

    <a href="contact.php">
        Contact
    </a>

</nav>

<main>

<section>

    <h2>
        Nous contacter
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
            Vous pouvez nous contacter pour toute question
            concernant nos menus, une commande ou nos services.
        </p>

        <p>
            E-mail :
            <strong>
                contact@vite-et-gourmand.fr
            </strong>
        </p>

    </div>


    <div class="card">

        <form
            method="POST"
            action="contact.php"
        >

            <label for="nom">
                Nom :
            </label>

            <br>

            <input
                type="text"
                id="nom"
                name="nom"
                value="<?= htmlspecialchars(
                    $nom,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
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
                value="<?= htmlspecialchars(
                    $email,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                autocomplete="email"
                required
            >

            <br><br>


            <label for="sujet">
                Sujet :
            </label>

            <br>

            <input
                type="text"
                id="sujet"
                name="sujet"
                value="<?= htmlspecialchars(
                    $sujet,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                required
            >

            <br><br>


            <label for="contenu">
                Message :
            </label>

            <br>

            <textarea
                id="contenu"
                name="contenu"
                rows="8"
                required
            ><?= htmlspecialchars(
                $contenu,
                ENT_QUOTES,
                'UTF-8'
            ) ?></textarea>

            <br><br>


            <button
                class="btn"
                type="submit"
            >
                Envoyer le message
            </button>

        </form>

    </div>

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