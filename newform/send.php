<?php

session_start();

if (!isset($_SESSION['token']) || !isset($_SESSION['name'])) {
    header('Location: ../login/index.php');
    exit();
}





$ticket_id = $_GET['id'] ?? null;
if (!$ticket_id) {
    die("Erreur : ID du ticket non spécifié.");
}

// Chemin vers le fichier JSON
$jsonFile = '../param.json';
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        $glpi_api_url = $data['glpi_api_url'];
 = $data['user_token'];
        $app_token = $data['app_token'];
    } else {
        echo "Erreur lors du décodage du JSON: " . json_last_error_msg();
    }
} else {
    echo "Le fichier JSON n'existe pas à l'emplacement spécifié.";
}



// $glpi_api_url = "https://glpitest.sdis60.fr/apirest.php/";
$session_token = $_SESSION['token'];
/ = "BWhWdZSpXKqqGVIr4tLHGqdcwusiJ7dX4A9Z3XJN";
// $app_token = "qbaZmWLrkqZP8uueCP8Kx2FlLmwiuiCqA8yiLRMj";

// Fonction pour récupérer les messages du ticket (suivis ITIL)
function getTicketMessages($glpi_api_url, $ticket_id, $session_token, $app_token) {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Désactive la vérification SSL (à ne pas utiliser en production sans raison valide)

    curl_setopt_array($curl, [
        CURLOPT_URL => $glpi_api_url . "Ticket/$ticket_id/ITILFollowup",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Session-Token: $session_token",
            "App-Token: $app_token"
        ]
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        die("Erreur CURL : " . curl_error($curl));
    }

    curl_close($curl);

    $messages = json_decode($response, true);
    if (isset($messages['error'])) {
        die("Erreur : " . $messages['error']);
    }

    return $messages;
}

function getAttachedFiles($document_link, $session_token, $app_token) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Désactive la vérification SSL (à ne pas utiliser en production sans raison valide)
    curl_setopt_array($curl, [
        CURLOPT_URL => $document_link,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Session-Token: $session_token",
            // "Authorization: user_toke",
            "App-Token: $app_token",
            "Content-Type: application/json",
            "Accept: application/octet-stream"
        ]
    ]);
    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        die("Erreur CURL : " . curl_error($curl));
    }
    curl_close($curl);
    
    // Décode le JSON et retourne les fichiers attachés
    $files = json_decode($response, true);
    return $files; // Retourne les fichiers attachés, si disponibles
}



// Fonction pour récupérer les détails du ticket
function getTicketDetails($glpi_api_url, $ticket_id, $session_token, $app_token) {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Désactive la vérification SSL (à ne pas utiliser en production sans raison valide)

    curl_setopt_array($curl, [
        CURLOPT_URL => $glpi_api_url . "Ticket/$ticket_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Session-Token: $session_token",
            "App-Token: $app_token"
        ]
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        die("Erreur CURL : " . curl_error($curl));
    }

    curl_close($curl);

    $ticketDetails = json_decode($response, true);
    if (isset($ticketDetails['error'])) {
        die("Erreur : " . $ticketDetails['error']);
    }

    return $ticketDetails;
}


// Récupération des messages du ticket
$messages = getTicketMessages($glpi_api_url, $ticket_id, $session_token, $app_token);


function getUserId($glpi_api_url, $session_token, $app_token) {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Désactive la vérification SSL (à ne pas utiliser en production sans raison valide)

    curl_setopt_array($curl, [
        CURLOPT_URL => $glpi_api_url . "getFullSession",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Session-Token: $session_token",
            "App-Token: $app_token"
        ]
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        die("Erreur CURL : " . curl_error($curl));
    }

    curl_close($curl);

    $session_data = json_decode($response, true);

    if (isset($session_data['session']['glpiID'])) {
        return $session_data['session']['glpiID']; // Retourne l'ID utilisateur
    } else {
        die("Erreur : Impossible de récupérer l'ID utilisateur. Réponse : " . $response);
    }
}


// Fonction pour vérifier si le ticket appartient à l'utilisateur
function isTicketOwnedByUser($ticketDetails, $user_id) {
    return isset($ticketDetails['users_id_recipient']) && $ticketDetails['users_id_recipient'] == $user_id;
}

function getTicketTitle($glpi_api_url, $ticket_id, $session_token, $app_token) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $glpi_api_url . "Ticket/$ticket_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false, // Désactiver la vérification SSL (à éviter en production)
        CURLOPT_HTTPHEADER => [
            "Session-Token: $session_token",
            "App-Token: $app_token",
        ],
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        die("Erreur CURL : " . curl_error($curl));
    }

    curl_close($curl);

    $ticket_details = json_decode($response, true);

    if (isset($ticket_details['error'])) {
        die("Erreur : " . $ticket_details['error']);
    }

    if (!isset($ticket_details['name'])) {
        die("Erreur : Impossible de récupérer l'intitulé du ticket.");
    }

    return $ticket_details['name'];
}


$ticket_title = getTicketTitle($glpi_api_url, $ticket_id, $session_token, $app_token);

// Appel de la fonction pour récupérer l'ID utilisateur
$user_id = getUserId($glpi_api_url, $session_token, $app_token);

// Récupération des détails du ticket
$ticketDetails = getTicketDetails($glpi_api_url, $ticket_id, $session_token, $app_token);

// Vérification de la propriété du ticket
if (!isTicketOwnedByUser($ticketDetails, $user_id)) {
    header('Location: panel/index.php');
    exit;
}

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
                    <h1>Ticket#<?php echo htmlspecialchars($ticket_id); ?></h1>
                </div>
                <a href="ticket.php" class="report">
                    <span>Retour</span>
                </a>
            </div>

            <div class="drag-drop hidden" id="form1">
                <form action="send_message_with_images.php?ticket_id=<?php echo $ticket_id; ?>" method="POST" enctype="multipart/form-data">
                <div class="drag-top">
                    <h1 class="h1-drag-top"> Vos images : </h1>
                    <button type="button" id="close" class="btn-close"><i class="bi bi-x-lg"></i></button>
                </div>

                <div class="drag-middle">
                    <div class="file-input-wrapper">
                        <input type="file" name="image" id="image" hidden required>
                        <label for="image" class="custom-file-label"><i class="bi bi-plus-lg"></i></label>
                    </div>
                    
                </div>
                <span id="file-name">Aucun fichier sélectionné</span>
                <div class="drag-bottom">
                    <button class="drag-submit" type="submit">Envoyer</button>
                </div>
                </form>
            </div>
            

            <div class="message-list">

                        <div class="line-support">
                            <div class="support">
                                <div class="top-message">
                                    <p class="message-content">
                                        <?php echo "L'intitulé du ticket est : " . htmlspecialchars($ticket_title);?>
                                    </p>
                                    <p class="message-info">
                                        Assistant Bob - Un moment
                                    </p>
                                </div>
                            </div>
                        </div>

                        <?php if (is_array($messages)): ?>
    <?php foreach ($messages as $message): ?>
        <?php
        if (!is_array($message)) {
            continue; // Ignorez les entrées non valides
        }
        if ($message['is_private'] != 1) {
            // Décodage des entités HTML et suppression des balises HTML
            $clean_content = strip_tags(html_entity_decode($message['content'], ENT_QUOTES | ENT_HTML5));

            // Extraire l'ID du document depuis le message, si présent
            preg_match('/-bf14-FILEID=(\d+)/', $message['content'], $matches);
            $document_id = $matches[1] ?? null; // Si un ID est trouvé, on le récupère
            $files = [];
            if ($document_id) {
                // Récupérer les fichiers attachés via la requête API avec l'ID du document
                $document_link = "https://glpitest.sdis60.fr/apirest.php/Document/{$document_id}";
                $files = getAttachedFiles($document_link, $session_token, $app_token);
            }
        ?>
            <div class="<?php echo (isset($user_id) && isset($message['users_id']) && $message['users_id'] == $user_id) ? 'line-your' : 'line-support'; ?>">
                <div class="<?php echo (isset($user_id) && isset($message['users_id']) && $message['users_id'] == $user_id) ? 'your' : 'support'; ?>">
                    <div class="top-message">
                        <p class="message-content"><?php echo htmlspecialchars($clean_content); ?></p>
                        <p class="message-info"><?php echo (isset($user_id) && isset($message['users_id']) && $message['users_id'] == $user_id) ? "Vous" : "Support"; ?> - <?php echo htmlspecialchars($message['date']); ?></p>
                        <?php if (!empty($files)): ?>
                            <div class="attachments">
                                <?php foreach ($files as $file): ?>
                                    <?php
                                    $file_name = $file['name'] ?? 'Fichier inconnu';
                                    $file_url = $file['url'] ?? '#';
                                    $file_type = $file['type'] ?? '';
                                    // Si c'est une image, on l'affiche, sinon on fournit un lien de téléchargement
                                    if (strpos($file_type, 'image') !== false):
                                    ?>
                                        <div class="file">
                                            <img src="<?php echo $file_url; ?>" alt="<?php echo htmlspecialchars($file_name); ?>" style="max-width: 200px;">
                                            <p><?php echo htmlspecialchars($file_name); ?></p>
                                        </div>
                                    <?php else: ?>
                                        <div class="file">
                                            <a href="<?php echo $file_url; ?>" download><?php echo htmlspecialchars($file_name); ?></a>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php endforeach; ?>
<?php else: ?>
    <p>Aucun message trouvé pour ce ticket.</p>
<?php endif; ?>

            <?php
            
            if (isset($ticket_id)) {
                $status_resolu = 5; // Remplacez par la valeur réelle du statut "Résolu"
            

                if ($ticketDetails['status'] >= $status_resolu) {
            
                    // URL de la requête GET pour récupérer la solution du ticket
                    $solution_url = $glpi_api_url . "Ticket/$ticket_id/ITILSolution";
            
                    // Initialisation de la session cURL pour récupérer la solution du ticket
                    $ch = curl_init();
            
                    $glpi_api_url = "https://glpitest.sdis60.fr/apirest.php/";
                    $app_token = "qbaZmWLrkqZP8uueCP8Kx2FlLmwiuiCqA8yiLRMj";
                    $session_token = $_SESSION['token'];

                    $solution_url = $glpi_api_url . "Ticket/$ticket_id/ITILSolution";

                    $headers = [
                        "Content-Type: application/json",
                        "App-Token: $app_token",
                        "Session-Token: $session_token"
                    ];

                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_URL, $solution_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
                    // Exécution de la requête et récupération de la réponse
                    $solution_response = curl_exec($ch);
                    if (curl_errno($ch)) {
                        die("Erreur CURL : " . curl_error($ch));
                    }
                    curl_close($ch);
            
                    // Décodage de la réponse JSON
                    $solutions = json_decode($solution_response, true);
            
                    // Vérifier et afficher la solution
                    if (is_array($solutions) && !empty($solutions)) {
                        foreach ($solutions as $solution) {
                            if (isset($solution['content'])) {
                                $clean_content = strip_tags(html_entity_decode($solution['content'], ENT_QUOTES | ENT_HTML5));
                                ?>

                                <div class="line-support">
                                    <div class="support">
                                        <div class="top-message">
                                            <p class="message-content">
                                                <?php echo "Résolution du ticket : " . htmlspecialchars($clean_content);?>
                                            </p>
                                            <p class="message-info">
                                                Assistant Bob - Un moment
                                            </p>
                                        </div>
                                    </div>
                                </div>


                                <?php
                            }
                        }
                    }
                }
            } else {
                die("Erreur : Ticket non trouvé.");
            }
            
            
            ?>


            </div>
        </main>

        <footer>
        <form class="msg-form" id="sendmsg" action="send_message.php" method="POST">
            <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket_id); ?>">
            <div class="form-inner">
            
                <button type="button" id="open" class="btn-drag"  <?php if($ticketDetails['status'] >= 5) { ?> disabled <?php }?>><i class="bi bi-plus"></i></button>

                <input type="text" name="message" placeholder="<?php if($ticketDetails['status'] >= 5) { echo "Le ticket est fermé"; } else { echo "Votre message"; }?>"  <?php if($ticketDetails['status'] >= 5) { ?> disabled <?php }?> required>

                <button class="btn-input" type="submit"  <?php if($ticketDetails['status'] >= 5) { ?> disabled <?php }?>><i class="bi bi-send-fill"></i></button>
            </div>
        </form>

        </footer>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            document.getElementById('image').addEventListener('change', function () {
                const fileName = this.files[0] ? this.files[0].name : 'Aucun fichier sélectionné';
                document.getElementById('file-name').textContent = fileName;
            });
        });

    </script>
    <script src="message.js" defer></script>
</body>

</html>
