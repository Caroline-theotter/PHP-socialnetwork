<?php
session_start();
session_unset();
session_destroy();
echo "Vous êtes déconnecté(e)"
?>
<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Connexion</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
        <header>
            <img src="resoc.jpg" alt="Logo de notre réseau social"/>
        </header>

        <div id="wrapper" >
            <aside>
                <h2>Présentation</h2>
                <p>Bienvenue sur notre réseau social.</p>
            </aside>
            <main>
                <article>
                    <h2>Connexion</h2>
                    <?php
                    $enCoursDeTraitement = isset($_POST['email']);

                    if ($enCoursDeTraitement){
                        $emailAVerifier = $_POST['email'];
                        $passwdAVerifier = $_POST['motpasse'];
                       
                        $mysqli = new mysqli("localhost:8889", "root", "root", "socialnetwork");

                        $emailAVerifier = $mysqli->real_escape_string($emailAVerifier);
                        $passwdAVerifier = $mysqli->real_escape_string($passwdAVerifier);

                        $passwdAVerifier = md5($passwdAVerifier);
                       
                        $lInstructionSql = "SELECT * "
                                . "FROM `users` "
                                . "WHERE "
                                . "`email` LIKE '" . $emailAVerifier . "' "
                                . "";

                        $res = $mysqli->query($lInstructionSql);
                        $user = $res->fetch_assoc();
                        if ( ! $user OR $user["password"] != $passwdAVerifier)
                        {
                            echo "La connexion a échoué. ";
                            
                        } else
                        {
                            echo "Votre connexion est un succès : " . $user['alias'] . ".";
                            // Etape 7 : Se souvenir que l'utilisateur s'est connecté pour la suite
                            // documentation: https://www.php.net/manual/fr/session.examples.basic.php
                            $_SESSION['connected_id']=$user['id'];
                            header("Location: http://localhost:8888/PC-resoc-php-caroline_flora_noemie/src/resoc_n1/pages/news.php?user_id=".$_SESSION['connected_id']);
                            die();
                        }
                    }
                    ?>                     
                    <form action="login.php" method="post">
                        <input type='hidden'name='???' value='achanger'>
                        <dl>
                            <dt><label for='email'>E-Mail</label></dt>
                            <dd><input type='email'name='email'></dd>
                            <dt><label for='motpasse'>Mot de passe</label></dt>
                            <dd><input type='password'name='motpasse'></dd>
                        </dl>
                        <input type='submit'>
                    </form>
                    <p>
                        Pas de compte?
                        <a href='registration.php'>Inscrivez-vous.</a>
                    </p>

                </article>
            </main>
        </div>
    </body>
</html>
