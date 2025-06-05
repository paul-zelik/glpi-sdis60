<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['token'])) {
    header('Location: ../login/index.php');
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
        exit();
    }
} else {
    echo "Le fichier JSON n'existe pas à l'emplacement spécifié.";
    exit();
}


$session = $_SESSION['token'];

$title = $_SESSION['titreSaisi'] ?? null;
$description = $_SESSION['explicationSaisie'] ?? null;


if (empty($description)) {
    
    header('Location: ../panel/index.php?notif=1');
    exit();
}


$input_description = $description;
$model_file = "resultat.json";
$cate = $_SESSION['selectedCategory'];


$command = escapeshellcmd("python3 predict.py \"$input_description\" \"$model_file\" \"$cate\"");
$output = shell_exec($command);
if ($output === null) {
    header('Location: ../panel/index.php?notif=1');
    exit();
}

$category = trim($output);

if (empty($category)) {

    header('Location: ../panel/index.php?notif=1');
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

        header('Location: ../panel/index.php?notif=1');
        exit();
    }

    $result = json_decode($response, true);

    if (isset($result['id'])) {
        return $result['id']; // Retourner l'ID du ticket créé
    } else {

        header('Location: ../panel/index.php?notif=1');
        exit();
    }
}




unset($_SESSION['titreSaisi']);
unset($_SESSION['explicationSaisie']);
unset($_SESSION['selectedCategory']);
header('Location: ../panel/index.php?notif=0');
exit();

?>
