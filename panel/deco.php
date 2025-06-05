<?php

session_start();

if (!isset($_SESSION['token'])) {
    // Si le token utilisateur n'est pas défini, redirection vers la page de connexion.
    header('Location: ../login/index.php');
    exit;
}

// Chemin vers le fichier JSON
$jsonFile = '../param.json';
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        $glpiApiUrl = $data['glpi_api_url'] . "initSession";
    } else {
        echo "Erreur lors du décodage du JSON: " . json_last_error_msg();
    }
} else {
    echo "Le fichier JSON n'existe pas à l'emplacement spécifié.";
}




// Suppression de la session GLPI avec l'API
try {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    // Configuration de la requête CURL
    curl_setopt_array($curl, [
        CURLOPT_URL => $glpiApiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'DELETE', // Méthode DELETE pour terminer la session
        CURLOPT_HTTPHEADER => [
            "Session-Token: $session_token",
            "App-Token: $app_token"
        ],
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if (curl_errno($curl)) {
        throw new Exception('Erreur CURL : ' . curl_error($curl));
    }

    curl_close($curl);

    if ($httpCode !== 200) {
        throw new Exception('Erreur API GLPI : Code HTTP ' . $httpCode . ' - Réponse : ' . $response);
    }

    // Destruction de la session locale
    session_unset();
    session_destroy();

    // Redirection vers la page de connexion
    header('Location: ../login/index.php');
    exit;

} catch (Exception $e) {
    // Gérer les erreurs (optionnel : journalisation)
    session_start();

    session_unset();
    
    session_destroy();
    
    header('Location: ../login/index.php');
    exit;
}

?>
