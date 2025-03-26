<?php require_once "inc/header.inc.php"?>

<?php

if($_POST){

    if(empty($_POST['titre'])){
        $error .= "<div class='alert alert-danger'>Le titre est obligatoire.</div>";
    }
    if(empty($_POST['contenu'])){
        $error .= "<div class='alert alert-danger'>Le contenu est obligatoire.</div>";
    }

    if(empty($error)){
        try {
            // Connexion à MongoDB
            global $manager;
            $date = new DateTime();
            $currentDate = $date->format('d-m-Y H:i');
            
            // Hydratation de l'objet
            $topic = array(
                'title' => "$_POST[titre]",
                'content' => "$_POST[contenu]",
                'date' => "$currentDate",
                'author' => [
                    'userId' => $_SESSION['user']->_id,
                    'username' => $_SESSION['user']->username
                ]
            );
    
            // Connexion à la base de données
            $single_insert = new MongoDB\Driver\BulkWrite();
            $single_insert->insert($topic);
    
            // Création d'une nouvel objet de la collection "topics"
            $manager->executeBulkWrite('forum.topics', $single_insert);
    
            $success = "<div class='alert alert-success'>Topic ajouté avec succès.</div>";
        }
        catch ( \MongoDB\Driver\Exception\BulkWriteException $e ) {
            echo $e->getMessage();
        }
    }
}

?>

<div class="new_topic">
    <h1>Nouveau Topic</h1>

    <?php if(!userConnect()): ?>
        <h3>Veuillez vous connecter pour ajouter un nouveau sujet de conversation !</h3>
        <h4><a href="connexion.php">Se connecter</a></h4>
    <?php else: ?>

        <br><?php echo $error; //affichage de la variable $error  ?>
        <?= $success ?>

        <div class="new_topic_form">
            <form method="POST" style="max-width: inherit; padding: 20px;">

                <label class="form-label" for="titre">Titre</label><br>
                <input class="form-control" id="titre" type="text" name="titre"><br>

                <label class="form-label">Contenu</label><br>
                <input id="new_topic_content" type="hidden" name="contenu">
                <trix-editor class="trix-content" input="new_topic_content" style="min-height: 10em;"></trix-editor>

                <input type="submit" class="btn btn-secondary mt-3" value="Publier">
            </form>
        </div>

    <?php endif; ?>

</div>

<?php require_once "inc/footer.inc.php"?>