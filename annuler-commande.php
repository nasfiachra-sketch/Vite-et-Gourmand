<?php

session_start();

require 'config.php';

if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}

if (
    !isset($_GET['commande_id']) ||
    !is_numeric($_GET['commande_id'])
) {
    die('Commande introuvable.');
}

$commandeId = (int) $_GET['commande_id'];

$requete = $db->prepare(
    'SELECT statut
     FROM commandes
     WHERE id = ?
     AND utilisateur_id = ?'
);

$requete->execute([
    $commandeId,
    $_SESSION['utilisateur_id']
]);

$commande = $requete->fetch();

if (!$commande) {
    die('Commande introuvable.');
}

if ($commande['statut'] !== 'en_attente') {
    die('Cette commande ne peut plus être annulée.');
}

$ancienStatut = $commande['statut'];

$miseAJour = $db->prepare(
    'UPDATE commandes
     SET statut = ?
     WHERE id = ?
     AND utilisateur_id = ?
     AND statut = ?'
);

$miseAJour->execute([
    'annulee',
    $commandeId,
    $_SESSION['utilisateur_id'],
    'en_attente'
]);

if ($miseAJour->rowCount() === 1) {

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
        'annulee'
    ]);

    header('Location: compte.php');
    exit;
}

die('La commande n’a pas pu être annulée.');