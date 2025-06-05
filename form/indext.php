<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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

// Inclure le fichier PHP à exécuter à la fin du processus
if ($selectedCategory && $titreSaisi && $explicationSaisie) {
    die("AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA");
    header('Location: create_ticket.php');
    exit();
}

echo $selectedCategory . $titreSaisi . $explicationSaisie;

// Débogage : Afficher les valeurs des variables de session
ob_start(); // Démarrer la bufferisation de sortie
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="message.css">
    <title>Ticket#<?php echo $ticket_id ?? ''; ?> - Messages</title>
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
        <h2><?php echo $selectedCategory . $titreSaisi . $explicationSaisie;?></h2>
        <a href="#" class="profile">
            <i class="<?php echo $selectedCategory . $titreSaisi . $explicationSaisie;?>"></i>
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

<?php
ob_end_flush(); // Envoyer la sortie mise en tampon
?>
