<?php require_once "inc/header.inc.php"?>

<?php
    //RECUPERATION DU SUJET
    try {
        // Connexion à MongoDB Atlas
        global $manager;

        // Définition de la requête
        $filter = ['_id' => new MongoDB\BSON\ObjectId($_GET['id'])];
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

    //RECUPERATION DES COMMENTAIRES
    try {
        // Connexion à MongoDB Atlas
        global $manager;

        // Définition de la requête
        $filter = ['parentId' => null, '$and'=> [ ['topicId' => $_GET['id']]]]; 
        $option = [];
        $read = new MongoDB\Driver\Query($filter, $option);

        //Exécution de la requête
        $cursor2 = $manager->executeQuery('forum.comments', $read); 
    }
    catch ( MongoDB\Driver\Exception\Exception $e )
    {
        echo "Une erreur est survenue: ".$e->getMessage();
        exit();
    }


    //AJOUT DE COMMENTAIRE
    if($_POST) {

        if(isset($_POST['parentId'])) {
            try {
                // Connexion à MongoDB
                global $manager;
                $date = new DateTime();
                $currentDate = $date->format('d-m-Y H:i');

                // Hydratation de l'objet
                $comment = array(
                'content' => "$_POST[contenu]",
                'date' => "$currentDate",
                'author' => [
                    'userId' => $_SESSION['user']->_id,
                    'username' => $_SESSION['user']->username
                ],
                'parentId' => "$_POST[parentId]",
                'topicId' => "$_GET[id]"
                );

                // Connexion à la base de données
                $single_insert = new MongoDB\Driver\BulkWrite();
                $single_insert->insert($comment);

                // Création d'une nouvel objet de la collection "comments"
                $manager->executeBulkWrite('forum.comments', $single_insert);
        
                }
                catch ( \MongoDB\Driver\Exception\BulkWriteException $e )
                {
                echo $e->getMessage();
            }
        }
        else {
            try {
                // Connexion à MongoDB
                global $manager;
                $date = new DateTime();
                $currentDate = $date->format('d-m-Y H:i');

                // Hydratation de l'objet
                $comment = array(
                'content' => "$_POST[contenu]",
                'date' => "$currentDate",
                'author' => [
                    'userId' => $_SESSION['user']->_id,
                    'username' => $_SESSION['user']->username
                ],
                'topicId' => "$_GET[id]"
                );

                // Connexion à la base de données
                $single_insert = new MongoDB\Driver\BulkWrite();
                $single_insert->insert($comment);

                // Création d'une nouvel objet de la collection "comments"
                $manager->executeBulkWrite('forum.comments', $single_insert);
            }
            catch ( \MongoDB\Driver\Exception\BulkWriteException $e ) {
                echo $e->getMessage();
            }
        }
    

        //MISE A JOUR DES COMMENTAIRES
        try {
            // Connexion à MongoDB Atlas
            global $manager;
            
            // Définition de la requête
            $filter = ['parentId' => null, '$and'=> [ ['topicId' => $_GET['id']]]]; 
            $option = [];
            $read = new MongoDB\Driver\Query($filter, $option);

            //Exécution de la requête
            $cursor2 = $manager->executeQuery('forum.comments', $read); 
        }
        catch ( MongoDB\Driver\Exception\Exception $e ) {
            echo "Une erreur est survenue: ".$e->getMessage();
            exit();
        }
    }

    //Mise à jour de l'affichage des commentaires
    if($_GET && isset($_GET['display'])) {

        switch ($_GET['display']) {
            case 'recent':
                $display =  ['sort' => ['date' => -1]]; // tri par date décroissante
                break;
            case 'ancient':
                    $display =  ['sort' => ['date' => 1]]; // tri par date décroissante
                    break;
            default:
                $display = [];
                break;
        }

        //Mise à jour des commentaires en fonction de l'affichage
        try {
            // Connexion à MongoDB Atlas
            global $manager;

            // Définition de la requête
            $filter = ['parentId' => null, '$and'=> [ ['topicId' => $_GET['id']]]]; 
            $option = $display;
            $read = new MongoDB\Driver\Query($filter, $option);

            //Exécution de la requête
            $cursor2 = $manager->executeQuery('forum.comments', $read); 
        }
        catch ( MongoDB\Driver\Exception\Exception $e ) {
            echo "Une erreur est survenue: ".$e->getMessage();
            exit();
        }
    }
?>

<div class="details_page">
    <?php foreach($cursor as $topic): ?>
    <div class="topic">
        <h1><?= $topic->title ?></h1>
        <h3><?= $topic->author->username ?>, <?= $topic->date ?></h3>
        <p><?= $topic->content ?></p>
    
<?php if(userConnect()): //Permettre de commenter seulement aux utilisateurs connectés ?>
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#exampleModal">
    Commenter
    </button>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Réponse à: <?=$topic->title ?></h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST">
            <div class="modal-body">
                    <input id="x" type="hidden" name="contenu">
                    <trix-editor input="x"></trix-editor>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-dark">Commenter</button>
            </div>
        </form>
        </div>
    </div>
    </div>

<?php else: ?>
    Connectez vous pour commenter !
<?php endif; ?>
</div>

<?php endforeach; ?>

<div class="comment_filter">
    <h3>Affichage:</h3>
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="material-symbols-outlined">
                settings
            </span>
        </button>
        <ul class="dropdown-menu">
            <li><a href="?id=<?=$_GET['id']?>&display=default" class="dropdown-item">Réponses emboîtées</a></li>
            <li><a href="?id=<?=$_GET['id']?>&display=recent" class="dropdown-item">Réponses en ligne, les plus récentes en premier</a></li>
            <li><a href="?id=<?=$_GET['id']?>&display=ancient" class="dropdown-item">Réponses en ligne, les plus anciennes en premier</a></li>
        </ul>
    </div>
</div>

    <div class="comment_section">


    <?php function displayComment($comment, $level = 0) { //Affichage dynamique des commentaires en fonction de leur niveau?>
        
        <div class="<?= $level == 0 || $_GET['display'] == "ancient" ||  $_GET['display'] == "recent" ? "comment" : "comment answer-$level" ?>" >
            <h3><?= $comment->author->username ?>, <?= $comment->date ?></h3>
            <div class="trix-content"><?= $comment->content ?></div>

            <?php if(userConnect()): ?>
            <!-- Button trigger modal -->
                <button type="button" class="btn btn-dark btn-reply" data-bs-toggle="modal" data-bs-target="#answerModal<?= $comment->_id ?>">
                    Répondre
                </button>
            <?php endif; ?>

            <!-- Modal -->
            <div class="modal fade" id="answerModal<?= $comment->_id ?>" tabindex="-1" aria-labelledby="answerModalLabel<?= $comment->_id ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="answerModalLabel<?= $comment->_id ?>">Réponse à: <?= $comment->author->username ?> </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                            <input type="hidden" name="parentId" value="<?= $comment->_id ?>">
                            <input id="reponse<?= $comment->_id ?>" type="hidden" name="contenu">
                            <trix-editor class="trix-content" input="reponse<?= $comment->_id ?>"></trix-editor>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Publier</button>
                    </div>
                </form>
                </div>
            </div>
            </div>
            
            </div>
            <?php //affichage des réponses
                try {
                    // Connexion à MongoDB Atlas
                    global $manager;

                    // Définition de la requête
                    $filter = ['parentId' => (string) $comment->_id];
                    $option = [];
                    $read = new MongoDB\Driver\Query($filter, $option);

                    //Exécution de la requête
                    $cursor = $manager->executeQuery('forum.comments', $read); 
                    foreach($cursor as $answer) {
                        displayComment($answer, $level+1);
                    }
                }
                catch ( MongoDB\Driver\Exception\Exception $e ) {
                    echo "Une erreur est survenue: ".$e->getMessage();
                    exit();
                }
            ?>
        <?php
    }
            //Affichage des commentaires et des réponses
            foreach($cursor2 as $comment) {
                displayComment($comment, $level = 0);
            }
        ?>
    </div>
</div>

<?php require_once "inc/footer.inc.php"?>