<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
        $user_token = $data['user_token'];
        $app_token = $data['app_token'];
    } else {
        echo "Erreur lors du décodage du JSON: " . json_last_error_msg();
    }
} else {
    echo "Le fichier JSON n'existe pas à l'emplacement spécifié.";
}

$session_token = $_SESSION['token'];



// Fonction pour initialiser cURL avec curl_setopt_array
function initCurl($url, $headers) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Désactive la vérification SSL (à ne pas utiliser en production sans raison valide)
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers
    ]);
    return $curl;
}

// Fonction pour récupérer les messages du ticket (suivis ITIL)
function getTicketMessages($glpi_api_url, $ticket_id, $session_token, $app_token) {
    $curl = initCurl($glpi_api_url . "Ticket/$ticket_id/ITILFollowup", [
        "Session-Token: $session_token",
        "App-Token: $app_token"
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

// Fonction pour récupérer les documents associés à un message
function getMessageDocuments($glpi_api_url, $message_id, $session_token, $app_token) {
    $curl = initCurl("$glpi_api_url/ITILFollowup/$message_id/Document_Item", [
        "Session-Token: $session_token",
        "App-Token: $app_token"
    ]);

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        die("Erreur CURL : " . curl_error($curl));
    }
    curl_close($curl);

    $documents = json_decode($response, true);
    return $documents;
}

// Fonction pour récupérer les documents associés à un message
function getTicketDocument($glpi_api_url, $message_id, $session_token, $app_token) {
    $curl = initCurl("$glpi_api_url/Ticket/$message_id/Document_Item", [
        "Session-Token: $session_token",
        "App-Token: $app_token"
    ]);

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        die("Erreur CURL : " . curl_error($curl));
    }
    curl_close($curl);

    $documents = json_decode($response, true);
    return $documents;
}

function getDocumentDetails($glpi_api_url, $document_id, $session_token, $app_token) {
    $curl = initCurl("$glpi_api_url/Document/$document_id", [
        "Session-Token: $session_token",
        "App-Token: $app_token",
        "Accept: application/octet-stream"
    ]);

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        error_log("Erreur cURL lors de la récupération des détails du document: " . curl_error($curl));
        return false;
    }
    curl_close($curl);

    // Calculer la taille du fichier
    $fileSize = strlen($response);

    // Utiliser finfo pour déterminer le type MIME
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->buffer($response);

    // Déterminer l'extension du fichier à partir du type MIME
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/pdf' => 'pdf',
        'text/plain' => 'txt',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        // Ajoutez d'autres correspondances type MIME -> extension ici
    ];

    $extension = isset($extensions[$mimeType]) ? $extensions[$mimeType] : 'bin'; // 'bin' par défaut pour les fichiers binaires inconnus

    // Générer un nom de fichier basé sur l'ID du document et l'extension
    $file_name = "document_$document_id.$extension";

    return [
        'content' => $response,
        'name' => $file_name,
        'type' => $mimeType,
        'extension' => $extension,
        'size' => $fileSize
    ];
}



// Fonction pour récupérer les détails du ticket
function getTicketDetails($glpi_api_url, $ticket_id, $session_token, $app_token) {
    $curl = initCurl($glpi_api_url . "Ticket/$ticket_id", [
        "Session-Token: $session_token",
        "App-Token: $app_token"
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

// Fonction pour récupérer l'ID utilisateur
function getUserId($glpi_api_url, $session_token, $app_token) {
    $curl = initCurl($glpi_api_url . "getFullSession", [
        "Session-Token: $session_token",
        "App-Token: $app_token"
    ]);

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        die("Erreur CURL : " . curl_error($curl));
    }
    curl_close($curl);

    $session_data = json_decode($response, true);
    if (isset($session_data['session']['glpiID'])) {
        return $session_data['session']['glpiID'];
    } else {
        die("Erreur : Impossible de récupérer l'ID utilisateur. Réponse : " . $response);
    }
}

// Fonction pour vérifier si le ticket appartient à l'utilisateur
function isTicketOwnedByUser($ticketDetails, $user_id) {
    return isset($ticketDetails['users_id_recipient']) && $ticketDetails['users_id_recipient'] == $user_id;
}

// Fonction pour récupérer l'intitulé du ticket
function getTicketTitle($glpi_api_url, $ticket_id, $session_token, $app_token) {
    $curl = initCurl($glpi_api_url . "Ticket/$ticket_id", [
        "Session-Token: $session_token",
        "App-Token: $app_token"
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

// Récupération des messages du ticket
$messages = getTicketMessages($glpi_api_url, $ticket_id, $session_token, $app_token);

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


    <div class="loader" id="loader">
        <h1 class="loader-h1">Chargement</h1>
        <div class="loader-bar"></div>
    </div>

    <div class="every" id="every">

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
                                <?php echo "L'intitulé du ticket est : " . htmlspecialchars($ticket_title); ?>
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

                            // Récupérer les documents associés au message
                            $documents = getMessageDocuments($glpi_api_url, $message['id'], $session_token, $app_token);

                            if (!is_array($documents)) {
                                $documents = [];
                            }

                            $files = [];
                            foreach ($documents as $doc) {
                                $document_id = $doc['documents_id'];
                                $document_details = getDocumentDetails($glpi_api_url, $document_id, $session_token, $app_token);

                                if ($document_details !== false) {
                                    $files[] = [
                                        'name' => $document_details['name'],
                                        'content' => $document_details['content'],
                                        'type' => $document_details['type']
                                    ];
                                }
                            }
                        ?>
                            <div class="<?php echo (isset($user_id) && isset($message['users_id']) && $message['users_id'] == $user_id) ? 'line-your' : 'line-support'; ?>">
                                <div class="<?php echo (isset($user_id) && isset($message['users_id']) && $message['users_id'] == $user_id) ? 'your' : 'support'; ?>">
                                    <div class="top-message">
                                        <p class="message-content"><?php echo htmlspecialchars($clean_content); ?></p>
                                        <?php if (!empty($files)): ?>
                                            <div class="attachments">
                                                <?php foreach ($files as $file): ?>
                                                    <?php
                                                    $file_name = $file['name'] ?? 'LeFichier.txt';
                                                    $file_content = $file['content'];
                                                    $file_type = $file['type'] ?? '';
                                                    // Si c'est une image, on l'affiche, sinon on fournit un lien de téléchargement
                                                    if (strpos($file_type, 'image') !== false):
                                                    ?>
                                                                <img src="data:image/jpeg;base64,<?php echo base64_encode($file_content); ?>" class="zoomable-image" alt="<?php echo htmlspecialchars($file_name); ?>" style="max-width: 550px;"
                                                                onclick="openImageInNewTab(this.src)"
                                                                >

                                                            <p style="color: white;"><?php echo htmlspecialchars($file_name); ?></p>
                                                    <?php else: ?>
                                                        <div class="file">
                                                            <a class="file-link" href="data:application/octet-stream;base64,<?php echo base64_encode($file_content); ?>" download="<?php echo htmlspecialchars($file_name); ?>"><?php echo htmlspecialchars($file_name); ?>
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <p class="message-info"><?php echo (isset($user_id) && isset($message['users_id']) && $message['users_id'] == $user_id) ? "Vous" : "Support"; ?> - <?php echo htmlspecialchars($message['date']); ?></p>
                                        
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
                                                    <?php echo "Résolution du ticket : " . htmlspecialchars($clean_content); ?>
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

                <?php
                
                // Récupérer les documents associés au message
                $documents = getTicketDocument($glpi_api_url, $ticket_id, $session_token, $app_token);

                if (!is_array($documents)) {
                    $documents = [];
                }

                $files = [];
                foreach ($documents as $doc) {
                    $document_id = $doc['documents_id'];
                    $document_details = getDocumentDetails($glpi_api_url, $document_id, $session_token, $app_token);

                    if ($document_details !== false) {
                        $files[] = [
                            'name' => $document_details['name'],
                            'content' => $document_details['content'],
                            'type' => $document_details['type']
                        ];
                    }
                }
                
                
                ?>


                        <div class="line-support">
                                <div class="support">
                                    <div class="top-message">
                                        <?php if (!empty($files)): ?>
                                            
                                        <p class="message-content">Voici les fichiers qui ont été rataché au Ticket</p>
                                            <div class="attachments">
                                                <?php foreach ($files as $file): ?>
                                                    <?php
                                                    $file_name = $file['name'] ?? 'Fichier.txt';
                                                    $file_content = $file['content'];
                                                    $file_type = $file['type'] ?? '';
                                                    // Si c'est une image, on l'affiche, sinon on fournit un lien de téléchargement
                                                    if (strpos($file_type, 'image') !== false):
                                                        ?>
                                                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($file_content); ?>" class="zoomable-image" alt="<?php echo htmlspecialchars($file_name); ?>" style="max-width: 550px;"
                                                                    onclick="openImageInNewTab(this.src)"
                                                                    >
    
                                                                <p style="color: white;"><?php echo htmlspecialchars($file_name); ?></p>
                                                        <?php else: ?>
                                                            <div class="file">
                                                                <a class="file-link" href="data:application/octet-stream;base64,<?php echo base64_encode($file_content); ?>" download="<?php echo htmlspecialchars($file_name); ?>"><?php echo htmlspecialchars($file_name); ?>
                                                                <i class="bi bi-download"></i>
                                                            </a>
                                                            </div>
                                                        <?php endif; ?>
                                                <?php endforeach; ?>
                                                <p class="message-info">
                                                    Assistant Bob - Un moment
                                                </p>
                                            </div>
                                        <?php else: ?>
                                            <p class="message-content">Aucun fichier est rataché au Ticket.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>



            </div>

            

            <br><br><br><br><br><br>
        </main>

        <footer>
            <form class="msg-form" id="sendmsg" action="send_message.php" method="POST">
                <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket_id); ?>">
                <div class="form-inner">
                    <button type="button" id="open" class="btn-drag" <?php if ($ticketDetails['status'] >= 5) { ?> disabled <?php } ?>><i class="bi bi-plus"></i></button>
                    <input type="text" name="message" placeholder="<?php if ($ticketDetails['status'] >= 5) { echo "Le ticket est fermé"; } else { echo "Votre message"; } ?>" <?php if ($ticketDetails['status'] >= 5) { ?> disabled <?php } ?> required>
                    <button class="btn-input" type="submit" <?php if ($ticketDetails['status'] >= 5) { ?> disabled <?php } ?>><i class="bi bi-send-fill"></i></button>
                </div>
            </form>
        </footer>
    </div>

    </div>


    <!-- <script>

        document.addEventListener('DOMContentLoaded', function() {
            const zoomableImage = document.querySelector('.zoomable-image');
            const modal = document.getElementById('modal');
            const modalImage = document.getElementById('modal-image');
            const closeModal = document.querySelector('.close');

            zoomableImage.addEventListener('click', function() {
                modal.style.display = 'block';
                modalImage.src = this.src;
            });

            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });


    </script> -->

    <script>
        function openImageInNewTab(base64Image) {
            const newTab = window.open();
            newTab.document.write(`<img src="${base64Image}" style="width:100%;height:auto;">`);
            newTab.document.title = "Image";
        }
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            document.getElementById('image').addEventListener('change', function () {
                const fileName = this.files[0] ? this.files[0].name : 'Aucun fichier sélectionné';
                document.getElementById('file-name').textContent = fileName;
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
    // Simuler un délai de chargement (à remplacer par une requête AJAX réelle)
    setTimeout(function() {
        document.getElementById('loader').style.display = 'none';
        document.getElementById('every').style.display = 'block';
    }, 500); // 2 secondes de délai pour la simulation

        });
    </script>
    <script src="message.js" defer></script>
</body>
