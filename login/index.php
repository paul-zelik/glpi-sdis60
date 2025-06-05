<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistance Sdis60</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="container">
        <div class="screen">
            <div class="screen__content">
                <form class="login" action="glpi_login.php" method="POST">
                    <div class="login__field">
                        <i class="login__icon fas fa-user bi bi-envelope-at-fill "></i>
                        <input type="text"  name="email" class="login__input" placeholder="Nom d'utilisateur">
                    </div>
                    <div class="login__field">
                        <i class="login__icon fas fa-lock bi bi-shield-fill"></i>
                        <input type="password" name="password" class="login__input" placeholder="Mot de passe"
                        style="<?php
                                if(isset($_GET['error'])) {
                                    if($_GET['error'] == 1) {
                                        ?>
                                        border-bottom-color: red;
                                        <?php
                                    }
                                }
                                ?>
                        "
                        >
                    </div>
                    <button class="button login__submit"  type="submit">
                        <span class="button__text" >Connection</span>
                        <i class="button__icon fas fa-chevron-right bi bi-unlock-fill"></i>
                    </button>				
                </form>
                <div class="social-login">
                    <h3>Assistance Sdis60</h3>
                </div>
            </div>
            <div class="screen__background">
                <span class="screen__background__shape screen__background__shape4"></span>
                <span class="screen__background__shape screen__background__shape3"></span>		
                <span class="screen__background__shape screen__background__shape2"></span>
                <span class="screen__background__shape screen__background__shape1"></span>
            </div>		
        </div>
    </div>
</body>
</html>