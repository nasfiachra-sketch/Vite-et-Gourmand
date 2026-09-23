PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE utilisateurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    prenom TEXT NOT NULL,
    telephone TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    adresse TEXT NOT NULL,
    mot_de_passe TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user',
    actif INTEGER NOT NULL DEFAULT 1,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO utilisateurs VALUES(1,'Dupont','Marie','0600000000','marie@example.com','Bordeaux','A_MODIFIER_AVEC_UN_HASH','user',1,'2026-09-23 00:04:12');
INSERT INTO utilisateurs VALUES(2,'Cherni','Skander','9783470830','Nasfiachra@gmail.Com','9 allée de l''ile marante','$2y$10$j.E5AnxFCIsp/ujffvtsZOalbvxozKXsmGq8C/.aC3K0ldShqKpa2','user',1,'2026-09-23 00:34:32');
INSERT INTO utilisateurs VALUES(3,'cherni','skan','0783470830','nasfiichrak98@yahoo.com','9 allée de l''ile marante','$2y$10$.plSbD0wvX55sCRwVD54L.ozVONV4uVZI13Xi/JxXPT58eUUm/Mg2','user',1,'2026-09-23 00:52:04');
INSERT INTO utilisateurs VALUES(4,'Admin','Vite','0600000000','admin@vite-et-gourmand.fr','Bordeaux','$2y$10$TccN2s6TwCRnsftIOuqphuooYucDAMVeNvX5yZKhg0LpsyT5ILceq','admin',1,'2026-09-23 01:59:18');
INSERT INTO utilisateurs VALUES(5,'alia','mekki','98995346','Alia@gmail.com','lile marante','$2y$10$ETkx0hvnkwuBpIKkPN4iqOz0DiG0Lgz.B0/OMAqgGbxEAG3wxSxDK','employee',0,'2026-09-23 12:40:21');
CREATE TABLE menus (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titre TEXT NOT NULL,
    description TEXT NOT NULL,
    theme TEXT NOT NULL,
    regime TEXT NOT NULL,
    allergenes TEXT,
    minimum_personnes INTEGER NOT NULL,
    prix_minimum REAL NOT NULL,
    stock INTEGER NOT NULL DEFAULT 1,
    conditions TEXT NOT NULL,
    image TEXT,
    actif INTEGER NOT NULL DEFAULT 1
);
INSERT INTO menus VALUES(1,'Menu Classique','Un menu gourmand pour vos repas en famille.','classique','classique','Gluten, lait',4,25.0,10,'Commande minimum de 4 personnes.',NULL,1);
INSERT INTO menus VALUES(2,'Menu Végétarien','Une sélection savoureuse sans viande.','evenement','vegetarien','Gluten, lait',4,27.999999999999999999,10,'Commande minimum de 4 personnes.',NULL,1);
INSERT INTO menus VALUES(3,'Menu Noël','Un bon menu  festif pour vos repas de fin d’année .','noel','classique','Gluten, œufs, lait',6,35.0,10,'Commande minimum de 6 personnes.',NULL,1);
CREATE TABLE plats (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL,
    description TEXT,
    type TEXT NOT NULL,
    allergenes TEXT
);
INSERT INTO plats VALUES(1,'Salade fraîcheur','Salade composée de saison.','entrée','Aucun');
INSERT INTO plats VALUES(2,'Poulet rôti','Poulet rôti accompagné de légumes.','plat','Aucun');
INSERT INTO plats VALUES(3,'Tarte aux pommes','Dessert traditionnel aux pommes.','dessert','Gluten, œufs, lait');
CREATE TABLE menu_plat (
    menu_id INTEGER NOT NULL,
    plat_id INTEGER NOT NULL,
    PRIMARY KEY (menu_id, plat_id),
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (plat_id) REFERENCES plats(id) ON DELETE CASCADE
);
INSERT INTO menu_plat VALUES(1,1);
INSERT INTO menu_plat VALUES(1,2);
INSERT INTO menu_plat VALUES(3,1);
INSERT INTO menu_plat VALUES(1,3);
INSERT INTO menu_plat VALUES(2,1);
CREATE TABLE commandes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    utilisateur_id INTEGER NOT NULL,
    menu_id INTEGER NOT NULL,
    nombre_personnes INTEGER NOT NULL,
    adresse_livraison TEXT NOT NULL,
    date_livraison TEXT NOT NULL,
    heure_livraison TEXT NOT NULL,
    livraison INTEGER NOT NULL DEFAULT 0,
    distance_km REAL DEFAULT 0,
    remise REAL NOT NULL DEFAULT 0,
    montant_total REAL NOT NULL,
    statut TEXT NOT NULL DEFAULT 'en_attente',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (menu_id) REFERENCES menus(id)
);
INSERT INTO commandes VALUES(1,3,1,4,'9 allée de l''ile marante','2026-09-22','06:49',0,0.0,0.0,100.0,'completed','2026-09-23 01:50:00');
CREATE TABLE avis (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    utilisateur_id INTEGER NOT NULL,
    commande_id INTEGER NOT NULL,
    note INTEGER NOT NULL CHECK(note BETWEEN 1 AND 5),
    commentaire TEXT NOT NULL,
    statut TEXT NOT NULL DEFAULT 'en_attente',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (commande_id) REFERENCES commandes(id)
);
INSERT INTO avis VALUES(1,3,1,5,'tres bon','valide','2026-09-23 02:46:34');
CREATE TABLE historique_commandes (id INTEGER PRIMARY KEY AUTOINCREMENT, commande_id INTEGER NOT NULL, ancien_statut TEXT, nouveau_statut TEXT NOT NULL, date_modification DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (commande_id) REFERENCES commandes(id));
CREATE TABLE horaires (id INTEGER PRIMARY KEY AUTOINCREMENT, jour TEXT NOT NULL UNIQUE, heure_ouverture TEXT NOT NULL, heure_fermeture TEXT NOT NULL);
INSERT INTO horaires VALUES(1,'Lundi','09:00','19:00');
INSERT INTO horaires VALUES(2,'Mardi','09:00','19:00');
INSERT INTO horaires VALUES(3,'Mercredi','09:00','18:00');
INSERT INTO horaires VALUES(4,'Jeudi','09:00','19:00');
INSERT INTO horaires VALUES(5,'Vendredi','09:00','19:00');
INSERT INTO horaires VALUES(6,'Samedi','09:00','12:00');
INSERT INTO horaires VALUES(7,'Dimanche','09:00','14:00');
DELETE FROM sqlite_sequence;
INSERT INTO sqlite_sequence VALUES('utilisateurs',5);
INSERT INTO sqlite_sequence VALUES('menus',3);
INSERT INTO sqlite_sequence VALUES('plats',3);
INSERT INTO sqlite_sequence VALUES('commandes',1);
INSERT INTO sqlite_sequence VALUES('avis',1);
INSERT INTO sqlite_sequence VALUES('horaires',7);
COMMIT;
