<?php

session_start();

require 'config.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email === '' || $password === '') {

        $erreur = 'Veuillez remplir tous les champs.';

    } else {

        $requete = $db->prepare(
            'SELECT *
             FROM utilisateurs
             WHERE LOWER(email) = LOWER(?)
             AND actif = 1'
        );

        $requete->execute([
            $email
        ]);

        $utilisateur = $requete->fetch();

        if (
            $utilisateur &&
            password_verify(
                $password,
                $utilisateur['mot_de_passe']
            )
        ) {

            session_regenerate_id(true);

            $_SESSION['utilisateur_id'] = $utilisateur['id'];
            $_SESSION['nom'] = $utilisateur['nom'];
            $_SESSION['prenom'] = $utilisateur['prenom'];
            $_SESSION['email'] = $utilisateur['email'];
            $_SESSION['role'] = $utilisateur['role'];

            if ($utilisateur['role'] === 'admin') {

                header('Location: admin.php');
                exit;
            }

            if ($utilisateur['role'] === 'employee') {

                header('Location: gestion-menus.php');
                exit;
            }

            header('Location: menus.php');
            exit;

        } else {

            $erreur =
                'Adresse e-mail ou mot de passe incorrect.';
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
        Connexion - Vite & Gourmand
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
        Connexion
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
        Se connecter
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


    <div class="card">

        <form
            method="POST"
            action="connexion.php"
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


            <label for="password">
                Mot de passe :
            </label>

            <br>

            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <br><br>


            <button
                class="btn"
                type="submit"
            >
                Se connecter
            </button>

        </form>


        <p>

            <a href="mot-de-passe-oublie.php">
                Mot de passe oublié ?
            </a>

        </p>


        <p>
            Vous n'avez pas encore de compte ?
        </p>

        <a
            class="btn"
            href="inscription.php"
        >
            Créer un compte
        </a>

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
