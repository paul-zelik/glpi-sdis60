<?php
session_start();

// Vérification de l'authentification et de la présence des données requises
if (!isset($_SESSION['token']) || !isset($_GET['ticket_id']) || empty($_FILES['image'])) {
    header('Location: message.php?id=' . $_GET['ticket_id'] . '&notif=1');
    exit();
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

// Configuration de l'API GLPI
$session_token = $_SESSION['token'];
$ticket_id = $_GET['ticket_id'];

function attachFileToFollowup($glpi_api_url, $followup_id, $file, $session_token, $app_token) {
    $curl = curl_init();

    echo "\n\n                    -------------  1";

    // Vérifiez si le fichier est valide
    if (is_array($file['tmp_name'])) {
        // Si plusieurs fichiers sont envoyés, il faut les traiter un par un
        echo "2";
        foreach ($file['tmp_name'] as $index => $tmp_name) {
            $form_data = [
                'uploadManifest' => json_encode([
                    "input" => [
                        "itemtype" => "ITILFollowup",
                        "items_id" => $followup_id
                    ]
                ]),
                'file' => new CURLFile(
                    $tmp_name,  // chemin du fichier temporaire
                    $file['type'][$index],  // type MIME du fichier
                    $file['name'][$index]   // nom du fichier
                )
            ];

            echo "3";

            curl_setopt_array($curl, [
                CURLOPT_URL => $glpi_api_url . "Document",
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false, // À éviter en production
                CURLOPT_HTTPHEADER => [
                    "Session-Token: $session_token",
                    "App-Token: $app_token",
                ],
                CURLOPT_POSTFIELDS => $form_data,
            ]);

            echo "4";

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                die("GGGGGGGGGGGGGGGGGG");
                header('Location: message.php?id=' . $ticket_id . '&notif=1');
                exit();
            }

            echo "5";

            $response_data = json_decode($response, true);
            if (isset($response_data['error'])) {
                die("HHHHHHHHHHHHHHHHHH");
                header('Location: message.php?id=' . $ticket_id . '&notif=1');
                exit();
            }

            return $response_data;  // Retourner la réponse du premier fichier traité
        }
    } else {
        // Si un seul fichier est envoyé
        $form_data = [
            'uploadManifest' => json_encode([
                "input" => [
                    "itemtype" => "ITILFollowup",
                    "items_id" => $followup_id
                ]
            ]),
            'file' => new CURLFile(
                $file['tmp_name'],  // chemin du fichier temporaire
                $file['type'],      // type MIME du fichier
                $file['name']       // nom du fichier
            )
        ];

        echo "6";

        curl_setopt_array($curl, [
            CURLOPT_URL => $glpi_api_url . "Document",
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false, // À éviter en production
            CURLOPT_HTTPHEADER => [
                "Session-Token: $session_token",
                "App-Token: $app_token",
            ],
            CURLOPT_POSTFIELDS => $form_data,
        ]);

        echo "7";

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            die("FFFFFFFFFFFFFFFFFFFFFFF");
            header('Location: message.php?id=' . $ticket_id . '&notif=1');
            exit();
        }

        echo "8";

        curl_close($curl);

        $response_data = json_decode($response, true);
        if (isset($response_data['error'])) {
            die("EEEEEEEEEEEEEEEEEEEEE");
            header('Location: message.php?id=' . $ticket_id . '&notif=1');
            exit();
        }

        echo "9";

        echo $response_data;

        foreach ($response_data as $ele) {
            echo $ele;
        }

        return $response_data;  // Retourner la réponse du fichier traité
    }
}

// Fonction pour ajouter un message (suivi ITIL) au ticket
function addMessageToTicket($glpi_api_url, $ticket_id, $message_content, $session_token, $app_token) {
    $curl = curl_init();

    $data = [
        "input" => [
            "itemtype" => "Ticket",
            "items_id" => $ticket_id,
            "content" => $message_content,
        ]
    ];

    curl_setopt_array($curl, [
        CURLOPT_URL => $glpi_api_url . "Ticket/" . $ticket_id . "/ITILFollowup",
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false, // À éviter en production
        CURLOPT_HTTPHEADER => [
            "Session-Token: $session_token",
            "App-Token: $app_token",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        die("CCCCCCCCCCCCCCCCCCCCCCCCC");
        header('Location: message.php?id=' . $ticket_id . '&notif=1');
        exit();
    }

    curl_close($curl);

    $response_data = json_decode($response, true);
    if (isset($response_data['error'])) {
        die("DDDDDDDDDDDDDDDDDDDD");
        header('Location: message.php?id=' . $ticket_id . '&notif=1');
        exit();
    }

    return $response_data;
}

// Étape 1 : Ajouter un message ITIL
$message_content = "Un fichier a été joint à la discussion.";
$followup_response = addMessageToTicket($glpi_api_url, $ticket_id, $message_content, $session_token, $app_token);
$followup_id = $followup_response['id'] ?? null;

if (!$followup_id) {
    die("AAAAAAAAAAAAAA");
    header('Location: message.php?id=' . $ticket_id . '&notif=1');
    exit();
}

echo $followup_id;
// Étape 2 : Attacher le fichier au message de suivi
$document_response = attachFileToFollowup($glpi_api_url, $followup_id, $_FILES['image'], $session_token, $app_token);
$document_id = $document_response['id'] ?? null;

echo $document_id;

if (!$document_id) {
    
    echo "\n \n ssssssssssssssssssssssss";
    die("BBBBBBBBBBBBBBBBBBB");
    header('Location: message.php?id=' . $ticket_id . '&notif=1');
    exit();
}

// die("                         ---     " . $followup_id);
header('Location: message.php?id=' . $ticket_id . '&notif=0');
exit();
?>
