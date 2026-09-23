<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: connexion.php');
    exit;
}

$filtres = array(
    'menu_id' => isset($_GET['menu_id']) ? (int) $_GET['menu_id'] : 0,
    'date_debut' => isset($_GET['date_debut']) ? $_GET['date_debut'] : '',
    'date_fin' => isset($_GET['date_fin']) ? $_GET['date_fin'] : ''
);

$menus = $db->query("
    SELECT id, titre
    FROM menus
    ORDER BY titre
")->fetchAll();

$where = array();
$params = array();

if ($filtres['menu_id'] > 0) {
    $where[] = 'c.menu_id = :menu_id';
    $params[':menu_id'] = $filtres['menu_id'];
}

if ($filtres['date_debut'] !== '') {
    $where[] = 'date(c.date_livraison) >= :date_debut';
    $params[':date_debut'] = $filtres['date_debut'];
}

if ($filtres['date_fin'] !== '') {
    $where[] = 'date(c.date_livraison) <= :date_fin';
    $params[':date_fin'] = $filtres['date_fin'];
}

$sql = "
    SELECT
        m.id,
        m.titre,
        COUNT(c.id) AS nombre_commandes,
        COALESCE(SUM(c.montant_total), 0) AS chiffre_affaires
    FROM menus m
    LEFT JOIN commandes c ON c.menu_id = m.id
";

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= "
    GROUP BY m.id, m.titre
    ORDER BY chiffre_affaires DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$statistiques = $stmt->fetchAll();

/*
 * Stockage des statistiques dans un fichier JSON.
 * Ce fichier constitue notre stockage non relationnel.
 */
$statsNoSQL = array();

foreach ($statistiques as $stat) {
    $statsNoSQL[] = array(
        'menu_id' => (int) $stat['id'],
        'menu' => $stat['titre'],
        'commandes' => (int) $stat['nombre_commandes'],
        'chiffre_affaires' => round((float) $stat['chiffre_affaires'], 2)
    );
}

if (!is_dir('nosql')) {
    mkdir('nosql', 0755, true);
}

file_put_contents(
    'nosql/statistiques.json',
    json_encode($statsNoSQL, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

/*
 * Lecture des données depuis le fichier non relationnel
 * pour alimenter le graphique.
 */
$donneesGraphique = json_decode(
    file_get_contents('nosql/statistiques.json'),
    true
);

$totalCommandes = 0;
$totalCA = 0;

foreach ($statistiques as $stat) {
    $totalCommandes += (int) $stat['nombre_commandes'];
    $totalCA += (float) $stat['chiffre_affaires'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Vite & Gourmand</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stats-container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
        }

        .filtres {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .filtres form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: end;
        }

        .filtres label {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filtres input,
        .filtres select,
        .filtres button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .filtres button {
            cursor: pointer;
        }

        .cartes-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .carte-stat {
            flex: 1;
            min-width: 200px;
            padding: 20px;
            border-radius: 10px;
            background: #f5f5f5;
        }

        .carte-stat strong {
            display: block;
            font-size: 28px;
            margin-top: 8px;
        }

        .graphique {
            margin: 30px 0;
        }

        .barre-ligne {
            margin-bottom: 18px;
        }

        .barre-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .barre-fond {
            background: #e5e5e5;
            height: 28px;
            border-radius: 6px;
            overflow: hidden;
        }

        .barre {
            height: 100%;
            background: #777;
            min-width: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f5f5f5;
        }
    </style>
</head>

<body>

<header>
    <h1>Vite & Gourmand</h1>

    <nav>
        <a href="admin.php">Administration</a>
        <a href="gestion-menus.php">Menus</a>
        <a href="gestion-plats.php">Plats</a>
        <a href="gestion-horaires.php">Horaires</a>
        <a href="gestion-employes.php">Employés</a>
        <a href="statistiques.php">Statistiques</a>
        <a href="logout.php">Déconnexion</a>
    </nav>
</header>

<main class="stats-container">

    <h2>Statistiques</h2>

    <section class="filtres">
        <h3>Filtrer les statistiques</h3>

        <form method="get">

            <label>
                Menu
                <select name="menu_id">
                    <option value="0">Tous les menus</option>

                    <?php foreach ($menus as $menu): ?>
                        <option
                            value="<?php echo (int) $menu['id']; ?>"
                            <?php echo $filtres['menu_id'] == $menu['id'] ? 'selected' : ''; ?>
                        >
                            <?php echo htmlspecialchars($menu['titre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Date de début
                <input
                    type="date"
                    name="date_debut"
                    value="<?php echo htmlspecialchars($filtres['date_debut']); ?>"
                >
            </label>

            <label>
                Date de fin
                <input
                    type="date"
                    name="date_fin"
                    value="<?php echo htmlspecialchars($filtres['date_fin']); ?>"
                >
            </label>

            <button type="submit">Filtrer</button>

            <a href="statistiques.php">Réinitialiser</a>

        </form>
    </section>

    <section class="cartes-stats">

        <div class="carte-stat">
            <span>Nombre de commandes</span>
            <strong><?php echo $totalCommandes; ?></strong>
        </div>

        <div class="carte-stat">
            <span>Chiffre d'affaires</span>
            <strong><?php echo number_format($totalCA, 2, ',', ' '); ?> €</strong>
        </div>

    </section>

    <section class="graphique">

        <h3>Commandes par menu</h3>

        <?php
        $maxCommandes = 0;

        foreach ($donneesGraphique as $donnee) {
            if ($donnee['commandes'] > $maxCommandes) {
                $maxCommandes = $donnee['commandes'];
            }
        }
        ?>

        <?php if (empty($donneesGraphique)): ?>

            <p>Aucune donnée disponible.</p>

        <?php else: ?>

            <div
                role="img"
                aria-label="Graphique représentant le nombre de commandes par menu"
            >

                <?php foreach ($donneesGraphique as $donnee): ?>

                    <?php
                    $largeur = 0;

                    if ($maxCommandes > 0) {
                        $largeur = ($donnee['commandes'] / $maxCommandes) * 100;
                    }
                    ?>

                    <div class="barre-ligne">

                        <div class="barre-label">
                            <span>
                                <?php echo htmlspecialchars($donnee['menu']); ?>
                            </span>

                            <strong>
                                <?php echo $donnee['commandes']; ?> commande(s)
                            </strong>
                        </div>

                        <div class="barre-fond">
                            <div
                                class="barre"
                                style="width: <?php echo $largeur; ?>%;"
                            ></div>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

    <section>

        <h3>Détail par menu</h3>

        <table>
            <thead>
                <tr>
                    <th>Menu</th>
                    <th>Commandes</th>
                    <th>Chiffre d'affaires</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($statistiques as $stat): ?>

                    <tr>
                        <td>
                            <?php echo htmlspecialchars($stat['titre']); ?>
                        </td>

                        <td>
                            <?php echo (int) $stat['nombre_commandes']; ?>
                        </td>

                        <td>
                            <?php
                            echo number_format(
                                (float) $stat['chiffre_affaires'],
                                2,
                                ',',
                                ' '
                            );
                            ?> €
                        </td>
                    </tr>

                <?php endforeach; ?>

            </tbody>
        </table>

    </section>

</main>

<footer>
    <p>
        Lundi - Dimanche : 9h00 - 18h00
    </p>

    <p>
        <a href="mentions-legales.php">Mentions légales</a> |
        <a href="cgv.php">CGV</a>
    </p>
</footer>

</body>
</html>
