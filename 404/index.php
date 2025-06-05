<?php

session_start();

if (!isset($_SESSION['token']) && !isset($_SESSION['name'])) {
    header('Location: ../login/index.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
    <title>Assistance | Erreur 404</title>
</head>

<body>

    <div class="sidebar">

        <a href="https://byflash.fr/" class="logo">
            <i class="bi bi-lightning-charge-fill bi-logo"></i>
            <h2>Support</h2>
        </a>
        <ul class="side-menu">
            <li><a href="../panel/index.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
            <li><a href="../panel/ticket.php"><i class='bx bx-message-square-dots'></i>Tickets</a></li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="../panel/deco.php" class="logout">
                    <i class='bx bx-log-out-circle'></i>
                    Déconnexion
                </a>
            </li>
        </ul>
    </div>

    <div class="content">
        <nav>
            <h2><?php echo htmlspecialchars($_SESSION['name']); ?></h2>
            <i class="bi bi-person-circle bi-profil"></i>
        </nav>
        <main>
            <h1 class="erreur">404</h1>
        
        </main>
        
        
    </div>

    <script src="index.js"></script>
</body>
</html>
