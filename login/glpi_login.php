<?php
// Remplacez par l'URL de l'API REST de votre instance GLPI
// $glpi_api_url = "https://glpitest.sdis60.fr/apirest.php/";

// Chemin vers le fichier JSON
$jsonFile = '../param.json';
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        $glpi_api_url = $data['glpi_api_url'];
        $user_token = $data['user_token'];
        $api_token = $data['app_token'];
    } else {
        echo "Erreur lors du décodage du JSON: " . json_last_error_msg();
    }
} else {
    echo "Le fichier JSON n'existe pas à l'emplacement spécifié.";
}



// Token d'authentification de l'API (remplacez par votre propre token)
// $api_token = " BWhWdZSpXKqqGVIr4tLHGqdcwusiJ7dX4A9Z3XJN";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt_array($curl, [
            CURLOPT_URL => $glpi_api_url . "initSession",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: user_token $api_token",
                "App-Token: qbaZmWLrkqZP8uueCP8Kx2FlLmwiuiCqA8yiLRMj"
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'login' => $email,
                'password' => $password,
            ]),
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            header('Location: index.php?error=1');
            exit;
        } else {
            // Afficher le code HTTP et la réponse brute pour analyse
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
            $response_data = json_decode($response, true);
            if ($http_code === 200 && isset($response_data['session_token'])) {
                session_start();
                $_SESSION['token'] = $response_data['session_token'];
                $_SESSION['name'] = $email;
                $_SESSION['id'] = $response_data['users_id'];
                header('Location: ../panel/index.php');
                exit;
            } else {
                header('Location: index.php?error=1');
                exit;
            }
        }
        curl_close($curl);
        
    } else {
        header('Location: index.php?error=1');
        exit;
    }
} else {
    header('Location: index.php?error=1');
    exit;
}
?>

