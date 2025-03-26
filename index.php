<?php require_once "inc/header.inc.php" ?>
<?php
    $currentFilter = null;

    //Récupération des topics
    try {
        // Connexion à MongoDB Atlas
        global $manager;

        // Définition de la requête
        $filter = [];
        $option = [];
        $read = new MongoDB\Driver\Query($filter, $option);

        //Exécution de la requête
        $cursor = $manager->executeQuery('forum.topics', $read);
    }
    catch ( MongoDB\Driver\Exception\Exception $e )
    {
        echo "Une erreur est survenue: ".$e->getMessage();
        exit();
    }

    //Récupération des utilisateurs
    try {
        // Connexion à MongoDB Atlas
        global $manager;

        // Définition de la requête
        $filter = [];
        $option = [];
        $read = new MongoDB\Driver\Query($filter, $option);

        //Exécution de la requête
        $cursorUsers = $manager->executeQuery('forum.users', $read);
    }
    catch ( MongoDB\Driver\Exception\Exception $e ) {
        echo "Une erreur est survenue: ".$e->getMessage();
        exit();
    }

    //filtrer par utilisateur
    if($_GET && isset($_GET['userId'])) {
        try {
            // Connexion à MongoDB Atlas
            global $manager;

            // Définition de la requête
            $filter = ["author.userId" => new MongoDB\BSON\ObjectId($_GET['userId'])];
            $option = [];
            $read = new MongoDB\Driver\Query($filter, $option);

            //Exécution de la requête
            $cursor = $manager->executeQuery('forum.topics', $read);
            $currentFilter = $_GET['username'];
            if($cursor->isDead()) {
                // Afficher le message "Aucun résultat"
                $content = "Cet utilisateur n'a pas posté de topic.";
            }
        }
        catch ( MongoDB\Driver\Exception\Exception $e )
        {
            echo "Une erreur est survenue: ".$e->getMessage();
            exit();
        }
   }
?>
<main>
    <div class="presentation">
        <h1>Forum: LP PROJET WEB - 2022/2023</h1>
        <a class="box" href="addTopic.php">
            <div class="btn btn-one">
                <span>Créer un topic</span>
            </div>
        </a>
    </div>

    <div class="filter">
        <h3>Filtrer par:</h3>
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?= $currentFilter ? $currentFilter : "Utilisateur" ?>
            </button>
            <ul class="dropdown-menu">
                <li><a href="index.php" class="dropdown-item">Tous</a></li>
                <?php foreach ($cursorUsers as $user): ?>
                    <li><a class="dropdown-item" href="?userId=<?= $user->_id?>&username=<?= $user->username ?>"><?= $user->username ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="posts">

<?= $content //affichage du message d'information ?>

<?php foreach ($cursor as $topic): ?>

    <?php
        try {
            // Connexion à MongoDB Atlas
            global $manager;

            // Définition de la requête
            $filter = array("topicId" => (string) $topic->_id);
            $option = array();
            $read = new MongoDB\Driver\Query($filter, $option);
            
            //Exécution de la requête
            $cursorComments = $manager->executeQuery('forum.comments', $read);
            //Comptage du nombre de commentaires
            
            $commentCount = count(iterator_to_array($cursorComments));

            //Récupération des IDs des utilisateurs ayant posté des commentaires
            $userIds = $manager->executeCommand('forum', new MongoDB\Driver\Command(array(
                'distinct' => 'comments',
                'key' => 'author.userId',
                'query' => array('topicId' => (string) $topic->_id)
                )));
            
            //Comptage du nombre d'utilisateurs
            $userCount = 0;
            foreach ($userIds as $doc) {
                $userCount += count($doc->values);
            }
        } catch ( MongoDB\Driver\Exception\Exception $e ) {
            echo "Une erreur est survenue: ".$e->getMessage();
            exit();
        }
    ?>

        <div class="card" style="width: 18rem;">
            <div class="card-body">
                <h5 class="card-title"><?= $topic->title ?></h5>
                <h6 class="card-subtitle mb-2 text-muted"><?= $topic->author->username ?>, <span class="post_date"><?= $topic->date ?></span></h6>
                <h6 class="icons">
                    <div>
                        <span class="material-symbols-outlined">
                            forum
                        </span><?= $commentCount ?> 
                    </div>
                    <div>
                        <span class="material-symbols-outlined">
                            group
                        </span><?= $userCount ?>
                    </div>
                </h6>
                <div class="card-text ellipsis trix-content"><?= $topic->content ?></div>
                <a href="topicDetails.php?id=<?= $topic->_id ?>&display=default" class="card-link">Voir le détail</a>
            </div>
        </div>
<?php endforeach; ?>

    </div>

</main>
<?php require_once "inc/footer.inc.php" ?>