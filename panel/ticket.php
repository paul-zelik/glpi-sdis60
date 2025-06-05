<?php

session_start();

if (!isset($_SESSION['token']) && !isset($_SESSION['name'])) {
    header('Location: ../login/index.php');
    exit;
}

// Chemin vers le fichier JSON
$jsonFile = '../param.json';

// Vérifier si le fichier existe
if (file_exists($jsonFile)) {
    // Lire le contenu du fichier JSON
    $jsonContent = file_get_contents($jsonFile);

    // Décoder le JSON en un tableau associatif
    $data = json_decode($jsonContent, true);

    // Vérifier si le décodage a réussi
    if (json_last_error() === JSON_ERROR_NONE) {
        // Récupérer les paramètres et les affecter à des variables
        $glpi_api_url = $data['glpi_api_url'];
        $user_token = $data['user_token'];
        $app_token = $data['app_token'];
    } else {
        echo "Erreur lors du décodage du JSON: " . json_last_error_msg();
    }
} else {
    echo "Le fichier JSON n'existe pas à l'emplacement spécifié.";
}





// $glpi_api_url = "https://glpitest.sdis60.fr/apirest.php/";

// $user_token = "BWhWdZSpXKqqGVIr4tLHGqdcwusiJ7dX4A9Z3XJN";
// $app_token = "qbaZmWLrkqZP8uueCP8Kx2FlLmwiuiCqA8yiLRMj";


function initSession($glpi_api_url, $user_token, $app_token) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $glpi_api_url . "initSession",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "App-Token: $app_token"
        ]
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);

    if (isset($data['session_token'])) {
        return $data['session_token'];
    } else {
        die("Erreur : Impossible d'initialiser la session. Réponse : " . $response);
    }
}

$session_token = $_SESSION['token'];

function getStatusLabel($status) {
    $statuses = [
        1 => "Nouveau",
        2 => "En cours (attribué)",
        3 => "En cours (planifié)",
        4 => "En attente",
        5 => "Résolu",
        6 => "Fermé"
    ];
    return $statuses[$status] ?? "Inconnu";
}


function getTickets($glpi_api_url, $session_token, $user_token, $app_token) {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt_array($curl, [
        CURLOPT_URL => $glpi_api_url . "Ticket",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Session-Token: $session_token",
            "App-Token: $app_token"
        ]
    ]);

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        die("Erreur CURL : " . curl_error($curl));
    }

    curl_close($curl);

    // Afficher la réponse brute pour analyse
    $data = json_decode($response, true);

    if (is_array($data)) {
        return $data;
    } else {
        // Afficher la réponse brute en cas d'erreur
        die("Erreur lors de la récupération des tickets. Réponse brute : " . $response);
    }
}

function getMainElement($string, $separator = '>') {
    // Supprimer les espaces en début et fin de chaîne
    $string = trim($string);

    // Diviser la chaîne en éléments en utilisant le séparateur spécifié
    $elements = explode($separator, $string);

    // Supprimer les espaces en début et fin de chaque élément
    $elements = array_map('trim', $elements);

    // Retourner le premier élément
    return $elements[0];
}



function getTypeElement($type) {
    $types = [
        76 => "Appui Tech",
        81 => "Appui Tech > Caméra Piéton",
        93 => "Communication",
        94 => "Communication > Demande d'affiche",
        97 => "CTA",
        98 => "CTA > CTA My Start+",
        54 => "Formation",
        91 => "Formation",
        55 => "Formation > Application CIRIL - Formation",
        56 => "Formation > Application ESCORT - AT+ Gestion des concours",
        92 => "Formation > Logiciel Webfor",
        11 => "Informatique",
        79 => "Informatique",
        88 => "Informatique",
        9  => "Informatique > Accès Informatique",
        7  => "Informatique > Applications métier",
        87 => "Informatique > GESSI",
        8  => "Informatique > Matériel Informatique",
        80 => "Informatique > Matériel Informatique",
        63 => "Informatique > Messagerie",
        14 => "Informatique > Radio ICOM & MicroBDX",
        78 => "Informatique > RDG",
        5  => "Informatique > Réseau",
        4  => "Informatique > Sharepoint",
        6  => "Informatique > Tablettes",
        90 => "Informatique > Tablettes",
        57 => "Prévention",
        59 => "Prévention > Application ESCORT - Courrier",
        58 => "Prévention > Application ESCORT - Prévention",
        62 => "Prévision",
        64 => "Prévision",
        60 => "Prévision > Application ESCORT - Point d'eau",
        65 => "Prévision > CARTOGRAPHIE",
        67 => "Prévision > CARTOGRAPHIE > Carte thématique",
        68 => "Prévision > CARTOGRAPHIE > Chantier PRS",
        69 => "Prévision > CARTOGRAPHIE > DECI",
        70 => "Prévision > CARTOGRAPHIE > panne CARTO CTA - CODIS",
        71 => "Prévision > CARTOGRAPHIE > PARCELLAIRE nouvelle rue",
        72 => "Prévision > CARTOGRAPHIE > partage de données",
        66 => "Prévision > ETARE",
        73 => "Prévision > ETARE > Fiche ETARE",
        74 => "Prévision > ETARE > Plan ETARE",
        75 => "Prévision > ETARE > Ré-édition PDF",
        42 => "Ressources Humaines",
        48 => "Ressources Humaines > Application CIRIL - Autres demandes",
        46 => "Ressources Humaines > Application CIRIL - Dossier JSP",
        44 => "Ressources Humaines > Application CIRIL - Dossier SPP - PAT",
        43 => "Ressources Humaines > Application CIRIL - Dossier SPV",
        47 => "Ressources Humaines > Application CIRIL - Entretien professionnel",
        45 => "Ressources Humaines > Application CIRIL - Paye",
        53 => "Ressources Humaines > Application ESCORT - CRSS",
        51 => "Ressources Humaines > Application ESCORT - Logement de fonction",
        49 => "Ressources Humaines > Application ESCORT - Mission SPV",
        52 => "Ressources Humaines > Application ESCORT - Suivi médical",
        50 => "Ressources Humaines > Application ESCORT - Temps de travail SPP",
        21 => "RH",
        22 => "RH > Test",
        95 => "SSSM",
        96 => "SSSM > MediSAP",
        15 => "Téléphone",
        19 => "Téléphone > FIXE",
        18 => "Téléphone > Smartphone & GSM",
        26 => "Ticket externe CIRIL",
        31 => "Ticket externe CIRIL > Assistance formation en ligne AFEL",
        33 => "Ticket externe CIRIL > Maintenance adaptative",
        40 => "Ticket externe CIRIL > Maintenance curative et assistance technique",
        28 => "Ticket externe CIRIL > Maintenance curative et assistance technique > Incident bloquant ou critique",
        29 => "Ticket externe CIRIL > Maintenance curative et assistance technique > Incident majeur",
        30 => "Ticket externe CIRIL > Maintenance curative et assistance technique > Incident mineur",
        32 => "Ticket externe CIRIL > Maintenance préventive",
        39 => "Ticket externe ESCORT",
        41 => "Ticket externe ESCORT > Assistance technique et fonctionnelle",
        61 => "Ticket externe ESCORT > Maintenance curative et assistance technique",
        34 => "Ticket externe ESCORT > Prestation de maintenance particulière",
        38 => "Ticket externe ESCORT > Prestation de maintenance particulière > Dossier technique de l'installation",
        35 => "Ticket externe ESCORT > Prestation de maintenance particulière > Entretien de la base ORACLE",
        37 => "Ticket externe ESCORT > Prestation de maintenance particulière > Fourniture des mises à jour de la documentation",
        36 => "Ticket externe ESCORT > Prestation de maintenance particulière > Optimisation des performances applicatives",
        12 => "Transmissions",
        10 => "Transmissions > BIP POCSAG",
        13 => "Transmissions > Radio ANTARES",
        20 => "Transmissions > Transmissions",
    ];

    if (isset($types[$type])) {
        $elef = getMainElement($types[$type]);
        return "<p class=\"type black\">{$elef}</p>";
    } else {
        return "<p class=\"type gray\">Inconnu</p>";
    }
}


$tickets = getTickets($glpi_api_url, $_SESSION['token'], $user_token, $app_token);




?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="ticket.css">
    <title>Responsive Dashboard Design #2 | AsmrProg</title>
</head>




<body>


    <div class="loader" id="loader">
        <h1 class="loader-h1">Chargement</h1>
        <div class="loader-bar"></div>
    </div>

    <div class="every" id="every">

    <!-- End of Sidebar -->

    <div class="sidebar">
        <a href="#" class="logo">
            <i class="bi bi-lightning-charge-fill bi-logo"></i>
            <h2>Support</h2>
        </a>
        <ul class="side-menu">
            <li><a href="index.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
            <li  class="active"><a href="#"><i class='bx bx-message-square-dots'></i>Tickets</a></li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="deco.php" class="logout">
                    <i class='bx bx-log-out-circle'></i>
                    Déconnexion
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="content">
        <!-- Navbar -->
        <nav>
            
            <h2><?php echo htmlspecialchars($_SESSION['name']); ?></h2>
            <a href="#" class="profile">
                <i class="bi bi-person-circle bi-profil"></i>
            </a>
        </nav>

        <!-- End of Navbar -->

        <?php
        
        if (isset($_GET['notif'])) {
            switch ($_GET['notif']) {
                case 0:
                    echo "<p class=\"notification is-success\" role=\"alert\">Action réalisé sans erreur(s) !<button class=\"delete\" type=\"button\" ><i class=\"bi bi-x-circle-fill\"></i></button></p>";
                    break;
                case 1:
                    echo "<p class=\"notification is-wrong\" role=\"alert\">Une erreur c'est produite !<button class=\"delete\" type=\"button\" ><i class=\"bi bi-x-circle-fill\"></i></button></p>";
                    break;
                default: 
                    break;
            }
        }
        
        ?>

        <main>
            <div class="header">
                <div class="left">
                    <h1>Vos tickets</h1>
                    <ul class="breadcrumb">
                        <li>
                        <?php echo htmlspecialchars($_SESSION['name']); ?>                      </li>
                    </ul>
                </div>
                <a href="../form/index.php" class="report">
                    <span>Nouveau</span>
                </a>
            </div>

          
            <div class="bottom-data">
                <div class="orders">
                    <div class="header">
                        <i class="bi bi-ticket bi-ticket-perforated"></i>
                        <h3>Ticket</h3>
                        <a href="#"><i class='bx bx-plus'></i></a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Tickets</th>
                                <th>Type</th>
                                <th>Titre</th>
                                <th>Status</th>
                                <th>Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($tickets)) {
                                foreach ($tickets as $ticket) {
                                    $ticket_id = $ticket['id'] ?? 'Inconnu';
                                    $ticket_category = $ticket['itilcategories_id'];
                                    $ticket_name = $ticket['name'] ?? 'Sans titre';
                                    $element_name = getTypeElement($ticket_category);
                                    $ticket_status = getStatusLabel($ticket['status'] ?? 0);
                                    echo "<tr><td>Ticket#" . $ticket_id . "</td><td>" . $element_name . "</td><td>" . $ticket_name .  "</td><td>" .  $ticket_status . "</td><td style=\"display: flex; justify-content: center;\" class=\"a-btn\"><a style=\"background-color: #000;border-radius: 5px;padding-left: 5px;padding-right: 5px;\"class=\"a-messages\" href=\"message.php?id=" . $ticket_id . "\"><i class=\"bi bi-eye-fill\"></i></a></td></tr>";
                                }
                            } else {
                                echo "<td>Aucun ticket trouvé pour cet utilisateur.</td> ";
                            }                
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </main>

    </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
    // Simuler un délai de chargement (à remplacer par une requête AJAX réelle)
    setTimeout(function() {
        document.getElementById('loader').style.display = 'none';
        document.getElementById('every').style.display = 'block';
    }, 500); // 2 secondes de délai pour la simulation

        });
    </script>

    <script src="ticket.js"></script>
</body>

</html>