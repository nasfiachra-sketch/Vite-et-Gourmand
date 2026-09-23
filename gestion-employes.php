<?php

session_start();

require 'config.php';

if (
    !isset($_SESSION['utilisateur_id']) ||
    $_SESSION['role'] !== 'admin'
) {
    die('Accès réservé à l’administrateur.');
}

$message = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = isset($_POST['action'])
        ? trim($_POST['action'])
        : '';

    if ($action === 'ajouter') {

        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $telephone = trim($_POST['telephone']);
        $email = trim($_POST['email']);
        $adresse = trim($_POST['adresse']);
        $password = $_POST['password'];

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
                    'employee'
                ]);

                $message = 'Le compte employé a été créé avec succès.';
            }
        }
    }


    if ($action === 'desactiver') {

        $employeId = (int) $_POST['employe_id'];

        $requete = $db->prepare(
            'UPDATE utilisateurs
             SET actif = 0
             WHERE id = ?
             AND role = ?'
        );

        $requete->execute([
            $employeId,
            'employee'
        ]);

        $message = 'Le compte employé a été désactivé.';
    }


    if ($action === 'activer') {

        $employeId = (int) $_POST['employe_id'];

        $requete = $db->prepare(
            'UPDATE utilisateurs
             SET actif = 1
             WHERE id = ?
             AND role = ?'
        );

        $requete->execute([
            $employeId,
            'employee'
        ]);

        $message = 'Le compte employé a été réactivé.';
    }
}


$requeteEmployes = $db->query(
    'SELECT
        id,
        nom,
        prenom,
        telephone,
        email,
        adresse,
        actif,
        date_creation
     FROM utilisateurs
     WHERE role = ?
     ORDER BY id DESC'
);

$requeteEmployes->execute([
    'employee'
]);

$employes = $requeteEmployes->fetchAll();

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
        Gestion des employés - Vite & Gourmand
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
        Gestion des employés
    </p>

</header>

<nav>

    <a href="index.html">
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

    <a href="logout.php">
        Déconnexion
    </a>

</nav>

<main>

<section>

    <h2>
        Créer un compte employé
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

    <form
        method="POST"
        action="gestion-employes.php"
    >

        <input
            type="hidden"
            name="action"
            value="ajouter"
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
            required
        >

        <p>
            Minimum 10 caractères avec une majuscule,
            une minuscule, un chiffre et un caractère spécial.
        </p>


        <button
            class="btn"
            type="submit"
        >
            Créer le compte employé
        </button>

    </form>

</div>


<h2>
    Employés existants
</h2>


<?php if (count($employes) === 0): ?>

    <div class="card">

        <p>
            Aucun compte employé n'est enregistré.
        </p>

    </div>

<?php else: ?>


<?php foreach ($employes as $employe): ?>

    <div class="card">

        <h3>
            <?= htmlspecialchars(
                $employe['prenom'] . ' ' . $employe['nom'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </h3>

        <p>
            <strong>E-mail :</strong>
            <?= htmlspecialchars(
                $employe['email'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>Téléphone :</strong>
            <?= htmlspecialchars(
                $employe['telephone'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>Adresse :</strong>
            <?= htmlspecialchars(
                $employe['adresse'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

        <p>
            <strong>Statut :</strong>

<?php if ((int) $employe['actif'] === 1): ?>

            Actif

<?php else: ?>

            Désactivé

<?php endif; ?>

        </p>


<?php if ((int) $employe['actif'] === 1): ?>

        <form
            method="POST"
            action="gestion-employes.php"
        >

            <input
                type="hidden"
                name="action"
                value="desactiver"
            >

            <input
                type="hidden"
                name="employe_id"
                value="<?= (int) $employe['id'] ?>"
            >

            <button
                class="btn"
                type="submit"
            >
                Désactiver l'employé
            </button>

        </form>

<?php else: ?>

        <form
            method="POST"
            action="gestion-employes.php"
        >

            <input
                type="hidden"
                name="action"
                value="activer"
            >

            <input
                type="hidden"
                name="employe_id"
                value="<?= (int) $employe['id'] ?>"
            >

            <button
                class="btn"
                type="submit"
            >
                Réactiver l'employé
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