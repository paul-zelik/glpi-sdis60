<?php

session_start();

if (!isset($_SESSION['token']) || !isset($_POST['ticket_id']) || !isset($_POST['message'])) {
    header('Location: ../login/index.php');
    exit;
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
//$glpi_api_url = "https://glpitest.sdis60.fr/apirest.php/";
 $session_token = $_SESSION['token'];
// $user_token = "BWhWdZSpXKqqGVIr4tLHGqdcwusiJ7dX4A9Z3XJN";
// $app_token = "qbaZmWLrkqZP8uueCP8Kx2FlLmwiuiCqA8yiLRMj";

// Récupération des données du formulaire
$ticket_id = $_POST['ticket_id'];
$message_content = trim($_POST['message']);


function getTicketDetails($glpi_api_url, $ticket_id, $session_token, $user_token, $app_token) {
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
        header('Location: message.php?id='. $ticket_id . '&notif=1');
        exit();
    }

    return $ticket_details;
}

// Fonction pour récupérer l'ID utilisateur de la session
function getUserId($glpi_api_url, $session_token, $user_token, $app_token) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $glpi_api_url . "getFullSession",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
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

    $session_data = json_decode($response, true);

    if (isset($session_data['session']['glpiID'])) {
        return $session_data['session']['glpiID']; // Retourne l'ID utilisateur
    } else {
        header('Location: message.php?id='. $ticket_id . '&notif=1');
        exit();
    }
}

// Fonction pour vérifier si le ticket appartient à l'utilisateur
function isTicketOwnedByUser($ticket_details, $user_id) {
    return isset($ticket_details['users_id_recipient']) && $ticket_details['users_id_recipient'] == $user_id;
}

// Récupérer l'ID utilisateur
$user_id = getUserId($glpi_api_url, $session_token, $user_token, $app_token);

// Récupérer les détails du ticket
$ticket_details = getTicketDetails($glpi_api_url, $ticket_id, $session_token, $user_token, $app_token);

// Vérifier la propriété du ticket
if (!isTicketOwnedByUser($ticket_details, $user_id)) {
    header('Location: ticket.php');
    exit;
}


// Fonction pour envoyer un nouveau message (suivi ITIL) à un ticket
function addTicketMessage($glpi_api_url, $ticket_id, $message_content, $session_token, $user_token, $app_token) {
    $curl = curl_init();

    // Corps de la requête pour ajouter un suivi
    $data = json_encode([
        "input" => [
            "itemtype" => "Ticket",
            "items_id" => $ticket_id,
            "content" => $message_content
        ]
    ]);

    curl_setopt_array($curl, [
        CURLOPT_URL => $glpi_api_url . "Ticket/" . $ticket_id . "/ITILFollowup",
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false, // Désactiver la vérification SSL (à éviter en production)
        CURLOPT_HTTPHEADER => [
            "Session-Token: $session_token",
            "App-Token: $app_token",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => $data,
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        header('Location: message.php?id='. $ticket_id . '&notif=1');
        exit();
    }

    curl_close($curl);

    $response_data = json_decode($response, true);
    if (isset($response_data['error'])) {
        header('Location: message.php?id='. $ticket_id . '&notif=1');
        exit();
    }

    return $response_data;
}

// Ajouter un nouveau message au ticket
try {
    $response = addTicketMessage($glpi_api_url, $ticket_id, $message_content, $session_token, $user_token, $app_token);
    header("Location: message.php?id=$ticket_id");
    exit();
} catch (Exception $e) {
    header('Location: message.php?id='. $ticket_id . '&notif=1');
    exit();
}


?>