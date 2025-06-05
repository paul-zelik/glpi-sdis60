<?php
session_start();

if (!isset($_SESSION['token']) || !isset($_SESSION['name'])) {
    header('Location: ../login/index.php');
    exit();
}

function extrairePrenom($chaine) {
    $parties = explode('.', $chaine);
    return isset($parties[0]) ? ucfirst(strtolower($parties[0])) : null;
}

$nomUtilisateur = $_SESSION['name'];
$prenom = extrairePrenom($nomUtilisateur);

// Utilisation de variables de session pour stocker les données du formulaire
if (!isset($_SESSION['selectedCategory'])) {
    $_SESSION['selectedCategory'] = '';
}
if (!isset($_SESSION['titreSaisi'])) {
    $_SESSION['titreSaisi'] = '';
}
if (!isset($_SESSION['explicationSaisie'])) {
    $_SESSION['explicationSaisie'] = '';
}

// Mise à jour des variables de session avec les données du formulaire
if (isset($_GET['category']) && $_GET['category'] !== "Choisissez Ici") {
    $_SESSION['selectedCategory'] = htmlspecialchars($_GET['category']);
}
if (isset($_POST['message']) && !empty(trim($_POST['message']))) {
    $_SESSION['titreSaisi'] = htmlspecialchars(trim($_POST['message']));
} elseif (isset($_GET['message']) && !empty(trim($_GET['message']))) {
    $_SESSION['titreSaisi'] = htmlspecialchars(trim($_GET['message']));
}
if (isset($_POST['explication']) && !empty(trim($_POST['explication']))) {
    $_SESSION['explicationSaisie'] = htmlspecialchars(trim($_POST['explication']));
}

$selectedCategory = $_SESSION['selectedCategory'];
$titreSaisi = $_SESSION['titreSaisi'];
$explicationSaisie = $_SESSION['explicationSaisie'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="message.css">
    <title>Ticket#<?php echo $ticket_id; ?> - Messages</title>
</head>

<body>

<?php
if (isset($_GET['notif'])) {
    switch ($_GET['notif']) {
        case 0:
            echo "<p class=\"notification is-success\" role=\"alert\">Action réalisée sans erreur(s) !<button class=\"delete\" type=\"button\" ><i class=\"bi bi-x-circle-fill\"></i></button></p>";
            break;
        case 1:
            echo "<p class=\"notification is-wrong\" role=\"alert\">Une erreur s'est produite !<button class=\"delete\" type=\"button\" ><i class=\"bi bi-x-circle-fill\"></i></button></p>";
            break;
        default:
            break;
    }
}
?>

<div class="sidebar">
    <a href="#" class="logo">
        <i class="bi bi-lightning-charge-fill bi-logo"></i>
        <h2>Support</h2>
    </a>
    <ul class="side-menu">
        <li><a href="index.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
        <li class="active"><a href="ticket.php"><i class='bx bx-message-square-dots'></i>Tickets</a></li>
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

<div class="content">
    <nav>
        <h2><?php echo htmlspecialchars($_SESSION['name']); ?></h2>
        <a href="#" class="profile">
            <i class="bi bi-person-circle bi-profil"></i>
        </a>
    </nav>

    <main>
        <div class="header">
            <div class="left">
                <h1>Création de ticket?</h1>
            </div>
            <a href="ticket.php" class="report">
                <span>Retour</span>
            </a>
        </div>

        <div class="message-list">
            <div class="line-support">
                <div class="support">
                    <div class="top-message">
                        <p class="message-content">
                            Salut <?php echo htmlspecialchars($prenom); ?>, j'avais compris que tu voulais créer un ticket. Je me présente, je suis Bob l'assistant des informaticiens du service informatique. Je vais essayer de comprendre ton problème et de le classer. Pour cela, est-ce que tu pourrais me dire, avec l'aide du menu déroulant ci-dessous, à quel service ta problématique doit être adressée ?
                        </p>
                        <div class="form-div-select">
                            <select id="selecter" class="form1-select" name="category">
                                <option selected>Choisissez Ici</option>
                                <option value="Transmission">Transmission</option>
                                <option value="Informatique">Informatique</option>
                                <option value="Telephone">Téléphone</option>
                                <option value="Prévision">Prévision</option>
                                <option value="Ressources Humaines">Ressources Humaines</option>
                                <option value="Formation">Formation</option>
                                <option value="Communication">Communication</option>
                                <option value="CTA">CTA</option>
                                <option value="Appui Tech">Appui Tech</option>
                                <option value="RH">RH</option>
                                <option value="SSSM">SSSM</option>
                            </select>
                        </div>
                        <p class="message-info">
                            Assistant Bob - Un moment
                        </p>
                    </div>
                </div>
            </div>

            <script>
            document.getElementById("selecter").addEventListener("change", function() {
                let selectedValue = this.value;
                if (selectedValue !== "Choisissez Ici") {
                    window.location.href = "?category=" + encodeURIComponent(selectedValue);
                }
            });
            </script>

            <?php if ($selectedCategory && $selectedCategory !== "Choisissez Ici"): ?>
                <div class="line-support">
                    <div class="support">
                        <div class="top-message">
                            <p class="message-content">
                                Merci <?php echo htmlspecialchars($prenom); ?>, d'accord je vois que ta problématique doit être adressée au service <?php echo htmlspecialchars($selectedCategory); ?>.
                                <br><br>
                                Est-ce que tu pourrais me donner un titre à ta problématique ? Pour cela, utilise la zone de texte qui se trouve juste en dessous.
                            </p>
                            <p class="message-info">
                                Assistant Bob - Un moment
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($titreSaisi): ?>
                <div class="line-your">
                    <div class="your">
                        <div class="top-message">
                            <p class="message-content">
                                <?php echo $titreSaisi; ?>
                            </p>
                            <p class="message-info">
                                <?php echo htmlspecialchars($prenom); ?> - Maintenant
                            </p>
                        </div>
                    </div>
                </div>

                <div class="line-support">
                    <div class="support">
                        <div class="top-message">
                            <p class="message-content">
                                Merci <?php echo htmlspecialchars($prenom); ?>, tu as donné le titre suivant :
                                <strong><?php echo $titreSaisi; ?></strong>.
                                <br><br>
                                Peux-tu me donner une explication claire et précise de ton problème s'il te plaît ?
                            </p>
                            <p class="message-info">
                                Assistant Bob - Maintenant
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($explicationSaisie): ?>
                <div class="line-your">
                    <div class="your">
                        <div class="top-message">
                            <p class="message-content">
                                <?php echo $explicationSaisie; ?>
                            </p>
                            <p class="message-info">
                                <?php echo htmlspecialchars($prenom); ?> - Maintenant
                            </p>
                        </div>
                    </div>
                </div>

                <div class="line-support">
                    <div class="support">
                        <div class="top-message">
                            <p class="message-content">
                                Merci pour ton explication, <?php echo htmlspecialchars($prenom); ?>.
                                <br><br>
                                Je vais maintenant transmettre ces informations à l'équipe concernée.
                            </p>
                            <p class="message-info">
                                Assistant Bob - Maintenant
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <br><br><br>

            <?php
            
            
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
                    return "$elef";
                } else {
                    return "Inconnu";
                }
            }


            
            // Chemin vers le fichier JSON
            $jsonFile = '../param.json';
            if (file_exists($jsonFile)) {
                $jsonContent = file_get_contents($jsonFile);
                $data = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
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
            $session = $_SESSION['token'];

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = $_SESSION['titreSaisi'] ?? null;
                $description = $_SESSION['explicationSaisie'] ?? null;

                if (empty($description)) {
                    header("Location: ../panel/index.php?notif=1");
                    exit();
                }

                $input_description = $description;
                $model_file = "resultat.json";
                $csv_file = "categories.csv";  // Ajout du fichier CSV comme argument
                $cate = $_SESSION['selectedCategory'];

                $command = escapeshellcmd("python predict.py \"$input_description\" \"$model_file\" \"$cate\"");
                $output = shell_exec($command);
                if ($output === null) {
                    header("Location: ../panel/index.php?notif=1");
                    exit();
                }


                $category = trim($output);

                if (empty($category)) {
                    header("Location: ../panel/index.php?notif=1");
                    exit();
                }

                function createTicket($glpi_api_url, $session_token, $user_token, $app_token, $category, $title, $description) {
                    $curl = curl_init();

                    $data = [
                        'input' => [
                            'name' => $title,
                            'content' => $description,
                            'itilcategories_id' => $category // ID de la catégorie sélectionnée
                        ]
                    ];

                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

                    curl_setopt_array($curl, [
                        CURLOPT_URL => $glpi_api_url . "Ticket",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => json_encode($data),
                        CURLOPT_HTTPHEADER => [
                            "Content-Type: application/json",
                            "Session-Token: $session_token",
                            "App-Token: $app_token"
                        ]
                    ]);

                    $response = curl_exec($curl);

                    if (curl_errno($curl)) {
                        header("Location: ../panel/index.php?notif=1");
                        exit();
                    }

                    // die($response . "+++++++++++++++++++" . $result['id']);
                    // curl_close($curl);
                    // exit;

                    $result = json_decode($response, true);


                    
                    if (isset($result['id'])) {
                        return $result['id']; // Retourner l'ID du ticket créé
                    } else {
                        header("Location: ../panel/index.php?notif=1");
                        exit();
                    }
                }

                // Appel de la fonction pour créer un ticket
                $session_token = $_SESSION['token']; // Assurez-vous que le token est défini
                $ticket_id = createTicket($glpi_api_url, $session_token, $user_token, $app_token, $category, $title, $description);

                // Rediriger vers une page de confirmation
                ?>

                <div class="line-support">
                    <div class="support">
                        <div class="top-message">
                            <p class="message-content">
                                Merci pour ton explication, <?php echo htmlspecialchars($prenom); ?>.
                                <br><br>
                                Après réfléxion avec mes autres amis robot. On a trouvé que ton ticket devait aller dans la catégorie : <strong><?php echo $type;?></strong>
                                <br><br>
                                Bonne journée!
                            </p>
                            <p class="message-info">
                                Assistant Bob - Maintenant
                            </p>
                        </div>
                    </div>
                </div>

                <?php
                header("Location: ../panel/index.php?notif=0");
                exit();
                
            }
            
            
            
            ?>


        </div>
    </main>

        


    <footer>
        <form class="msg-form" id="sendmsg" method="POST" action="">
            <input type="hidden" name="ticket_id" value="">
            <input type="hidden" name="titre" value="<?php echo $titreSaisi; ?>">
            <div class="form-inner">
                <button type="button" id="open" class="btn-drag" disabled><i class="bi bi-plus"></i></button>
                <input type="text" name="<?php echo $titreSaisi ? 'explication' : 'message'; ?>" id="message" placeholder="<?php echo $titreSaisi ? 'Votre explication' : 'Votre réponse'; ?>" <?php echo !$selectedCategory || $selectedCategory == "Choisissez Ici" ? 'disabled' : ''; ?> required>
                <button class="btn-input" type="submit"><i class="bi bi-send-fill"></i></button>
            </div>
        </form>
    </footer>
</div>
</body>

</html>
