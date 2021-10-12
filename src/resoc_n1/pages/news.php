<?php
session_start()
?>
<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Actualités</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
    <?php
            $userId = $_GET['user_id'];
           
            $mysqli = new mysqli("localhost:8889", "root", "root", "socialnetwork");
            ?>
    <header>
        <img src="resoc.jpg" alt="Logo de notre réseau social"/>
        <nav id="menu">
            <a href="news.php?user_id=<?php echo $_SESSION['connected_id']?>">Actualités</a>
            <a href="wall.php?user_id=<?php echo $_SESSION['connected_id']?>">Mur</a>
            <a href="feed.php?user_id=<?php echo $_SESSION['connected_id']?>">Flux</a>
            <a href="tags.php?tag_id=1">Mots-clés</a>
        </nav>
        <nav id="user">
            <a href="#">Profil</a>
            <ul>
                <li><a href="settings.php?user_id=<?php echo $_SESSION['connected_id']?>">Paramètres</a></li>
                <li><a href="followers.php?user_id=<?php echo $_SESSION['connected_id']?>">Mes suiveurs</a></li>
                <li><a href="subscriptions.php?user_id=<?php echo $_SESSION['connected_id']?>">Mes abonnements</a></li>
                <?php
                 $laQuestionEnSql = "SELECT * FROM `users` WHERE id=" . intval($userId);
                 $lesInformations = $mysqli->query($laQuestionEnSql);
                 $user = $lesInformations->fetch_assoc();
                if($user['id']==NULL){
                    ?>
                    <li><a href="login.php">Connexion</a></li>
                    <?php } else if ($_SESSION['connected_id']==$user['id']) { ?> 
                    <li><a href="logout.php">Déconnexion</a></li>
                    <?php
                    }
                    ?>
            </ul>
        </nav>
    </header>

    <div id="wrapper">
             <?php
            $userId = $_GET['user_id'];
           
            $mysqli = new mysqli("localhost:8889", "root", "root", "socialnetwork");
            ?>
        <aside>
        <?php
                $laQuestionEnSql = "SELECT * FROM `users` WHERE id=" . intval($userId);
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $user = $lesInformations->fetch_assoc();
                ?>
            <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
            <section>
                <h3>Présentation</h3>
                <p>Sur cette page vous trouverez les derniers messages de
                    tous les utilisatrices du site.</p>
            </section>
        </aside>
        
        <main>
        <?php
        print_r($user['id']);
        if ($_SESSION['connected_id']==$user['id']){
            $mysqli = new mysqli("localhost:8889", "root", "root", "socialnetwork");
            $enCoursDeTraitement = isset($_POST['bouton_like']); 
            print_r($_POST);
            if ($enCoursDeTraitement) {
                $liker_id = $_POST['liker_id'];
                $post_id = $_POST['post_id'];

                $liker_id = intval($mysqli->real_escape_string($liker_id));
                $post_id = $mysqli->real_escape_string($post_id);
        
                $lInstructionSql = "INSERT INTO `likes` "
                . "(`id`, `user_id`, `post_id`) "
                . "VALUES (NULL, "
                . "" . $liker_id . ", "
                . "" . $post_id . ")" ;
        
                $ok = $mysqli->query($lInstructionSql);
                    if ( ! $ok) {
                        echo "Vous avez déjà liké ! ";
                    } else {
                        echo "Bravo vous avez liké ! ";
                    }
            } 
        } 
        
        $mysqli = new mysqli("localhost:8889", "root", "root", "socialnetwork");

        if ($mysqli->connect_errno)
        {
            echo("Échec de la connexion : " . $mysqli->connect_error);
            echo("<p>Indice: Vérifiez les parametres de <code>new mysqli(...</code></p>");
            exit();
        }

        $laQuestionEnSql = "SELECT `posts`.`content`,"
                . "`posts`.`created`,"
                . "`posts`.`id` as post_identifiant, " 
                . "`users`.`id`,  "
                . "`users`.`alias` as author_name,  "
                . "count(DISTINCT `likes`.`id`) as like_number,  "
                . "GROUP_CONCAT(DISTINCT `tags`.`label`) AS taglist "
                . "FROM `posts`"
                . "JOIN `users` ON  `users`.`id`=`posts`.`user_id`"
                . "LEFT JOIN `posts_tags` ON `posts`.`id` = `posts_tags`.`post_id`  "
                . "LEFT JOIN `tags`       ON `posts_tags`.`tag_id`  = `tags`.`id` "
                . "LEFT JOIN `likes`      ON `likes`.`post_id`  = `posts`.`id` "
                . "GROUP BY `posts`.`id`"
                . "ORDER BY `posts`.`created` DESC  "
                . "LIMIT 15";

        $lesInformations = $mysqli->query($laQuestionEnSql);
            if ( ! $lesInformations)
            {
                echo("Échec de la requete : " . $mysqli->error);
                echo("<p>Indice: Vérifiez les la requete  SQL suivante dans phpmyadmin<code>$laQuestionEnSql</code></p>");
                exit();
            }

            while ($post = $lesInformations->fetch_assoc()){
        ?>
            <article>
                <h3>
                    <time><?php echo $post['created']?></time>
                </h3>
                <address><a href="wall.php?user_id=<?php echo $post['id']?>"><?php echo $post['author_name']?></a></address>                        <div>
                    <p><?php echo $post['content'] ?></p>
                </div>
                    <footer>
                        <form method="post">
                            <input type="hidden" name="post_id" value=<?php echo $post['post_identifiant']?>>
                            <input type="hidden" name="liker_id" value=<?php echo $_SESSION['connected_id']?>>
                            <input type="submit" value="♥" name="bouton_like">
                        </form>
                        <small>♥<?php echo $post['like_number']?></small>
                        <a href=""><?php echo $post['taglist']?></a>
                    </footer>
            </article>
            <?php
            }
            ?>
        </main>
    </div>
    </body>
</html>
