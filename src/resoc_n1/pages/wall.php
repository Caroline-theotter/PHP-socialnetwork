<?php
session_start();
?>
<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>ReSoC - Mur</title> 
        <meta name="author" content="Julien Falconnet">
        <link rel="stylesheet" href="style.css"/>
    </head>
    <body>
        <header>
            <img src="resoc.jpg" alt="Logo de notre réseau social"/>
            <nav id="menu">
                <a href="news.php">Actualités</a>
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
                    <p>Sur cette page vous trouverez tous les message de l'utilisatrice : <?php echo $user['alias']?></p>
   
                <?php
                    if ($_SESSION['connected_id']!=$user['id']){
                ?>
                    <form action="wall.php?user_id=<?php echo $userId?>" method="post">
                        <input type="submit" value="Suivre" name="bouton">
                    </form>

                <?php
                    $mysqli = new mysqli("localhost:8889", "root", "root", "socialnetwork");

                    $enCoursDeTraitement = isset($_POST['bouton']); 

                        if ($enCoursDeTraitement){
                            $following_id = $_SESSION['connected_id'];
                            $followedId = $userId;
                        
                            $followedId = intval($mysqli->real_escape_string($followedId));
                            $following_id = $mysqli->real_escape_string($following_id);
                        
                            $lInstructionSql = "INSERT INTO `followers` "
                                . "(`id`, `followed_user_id`, `following_user_id`) "
                                . "VALUES (NULL, "
                                . "" . $followedId . ", "
                                . "" . $following_id . ")" ;
                    
                            $ok = $mysqli->query($lInstructionSql);
                            if ( ! $ok)
                            {
                                echo "Vous suivez déjà " . $user['alias']." !";
                            } else
                            {
                                echo "Bravo ! Vous suivez ". $user['alias']." !";
                            }
                        }
                    } 
?>
                </section>
            </aside>

            <main>
                <?php 
                if ($_SESSION['connected_id']==$user['id']){
                ?>
                <article>
                    <h2>Poster un message</h2>
                    <?php
                    $mysqli = new mysqli("localhost:8889", "root", "root", "socialnetwork");
                    $enCoursDeTraitement = isset($_POST['message']); 
                   
                    if ($enCoursDeTraitement){
                        $authorId = $_SESSION['connected_id'];
                        $postContent = $_POST['message'];
                        
                        $authorId = intval($mysqli->real_escape_string($authorId));
                        $postContent = $mysqli->real_escape_string($postContent);
                        
                        $lInstructionSql = "INSERT INTO `posts` "
                                . "(`id`, `user_id`, `content`, `created`, `parent_id`) "
                                . "VALUES (NULL, "
                                . "" . $authorId . ", "
                                . "'" . $postContent . "', "
                                . "NOW(), "
                                . "NULL);"
                                . "";
                        
                        $ok = $mysqli->query($lInstructionSql);
                        if ( ! $ok)
                        {
                            echo "Impossible d'ajouter le message: " . $mysqli->error;
                        } else
                        {
                            echo "Message posté";
                        }
                    }
                    ?>      
                    
                    <form action="wall.php?user_id=<?php echo $_SESSION['connected_id']?>" method="post">
                        <dt><label for='message'>Message</label></dt>
                        <dd><textarea name='message'></textarea></dd>
                        </dl>
                        <input type='submit'>
                    </form>
                <?php 
                }
                ?>
                </article>    

                <?php
                $laQuestionEnSql = "SELECT `posts`.`content`,"
                        . "`posts`.`created`,"
                        . "`users`.`alias` as author_name,  "
                        . "count(DISTINCT `likes`.`id`) as like_number,  "
                        . "GROUP_CONCAT(DISTINCT `tags`.`label`) AS taglist "
                        . "FROM `posts`"
                        . "JOIN `users` ON  `users`.`id`=`posts`.`user_id`"
                        . "LEFT JOIN `posts_tags` ON `posts`.`id` = `posts_tags`.`post_id`  "
                        . "LEFT JOIN `tags`       ON `posts_tags`.`tag_id`  = `tags`.`id` "
                        . "LEFT JOIN `likes`      ON `likes`.`post_id`  = `posts`.`id` "
                        . "WHERE `posts`.`user_id`='" . intval($userId) . "' "
                        . "GROUP BY `posts`.`id`"
                        . "ORDER BY `posts`.`created` DESC  ";

                $lesInformations = $mysqli->query($laQuestionEnSql);
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }

                while ($post = $lesInformations->fetch_assoc()){
                ?>                
                <article>
                    <h3>
                        <time datetime='2020-02-01 11:12:13' ><?php echo $post['created']?></time>
                    </h3>
                        <address>par <?php echo $post['author_name']?></address>
                    <div>
                        <p><?php echo $post['content']?></p>
                    </div>                                            
                    <footer>
                        <small>♥ <?php echo $post['like_number']?></small>
                        <a href="">#<?php echo $post['taglist']?></a>
                    </footer>
                    </article>
                <?php } ?>
            </main>
        </div>
    </body>
</html>
