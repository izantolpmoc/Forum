<?php require_once "inc/header.inc.php"?>

<?php
    if(userConnect()){ //restriction si on est connecté
        echo "<script>window.location.href='index.php';</script>";
        exit;
    }
?>

<?php
    if($_POST){

        //Contrôle des saisies de l'utilisateur
        //--------
        //Contrôle la taille du pseudo (entre 3 et 15 caractères):
        if( strlen($_POST['pseudo']) <= 2 || strlen($_POST['pseudo']) > 15){
            $error .= "<div class='alert alert-danger'>Votre pseudo doit contenir entre 4 et 15 caractères.</div>";
        }

        try {
            // Connexion à MongoDB Atlas
            global $manager;

            // Définition de la requête
            $filter = ['username' => "$_POST[pseudo]"];
            $option = [];
            $read = new MongoDB\Driver\Query($filter, $option);

            //Exécution de la requête
            $cursor = $manager->executeQuery('forum.users', $read);

            foreach($cursor as $document) {
                echo '<br/>';
                if($document->username == $_POST['pseudo']){
                    $error.= "<div class='alert alert-danger'>Pseudo indisponible.</div>";
                }
            }
        }
        catch ( MongoDB\Driver\Exception\Exception $e ) {
            echo "Une erreur est survenue : ".$e->getMessage();
            exit();
        }

        // Boucle sur toutes les saisies de l'internaute afin de les passer dans les fonctions htmlentities() et addslashes():
        foreach($_POST as $indice => $valeur){
            $_POST[$indice] = htmlentities( addslashes($valeur));
        }

        //cryptage du mdp:
        $_POST['mdp'] = password_hash($_POST['mdp'], PASSWORD_DEFAULT);

        if( empty( $error ) ){
            try {
                // Connexion à MongoDB
                global $manager;

                // Hydratation de l'objet
                $user = array(
                'name' => "$_POST[nom]",
                'firstname' => "$_POST[prenom]",
                'username' => "$_POST[pseudo]",
                'password' => "$_POST[mdp]"
                );

                // Connexion à la base de données
                $single_insert = new MongoDB\Driver\BulkWrite();
                $single_insert->insert($user);

                // Création d'une nouvel objet de la collection "users"
                $manager->executeBulkWrite('forum.users', $single_insert) ;
                $success = "<div class='alert alert-success'>Inscription réalisée avec succès.</div>";

                //redirection vers la page de connexion
                echo "<script>window.location.href='connexion.php';</script>";
                exit;
            }
            catch ( \MongoDB\Driver\Exception\BulkWriteException $e ) {
                echo $e->getMessage();
            }
        }

    }
?>

<div class="signup">
    <h1>Inscription</h1>

    <br><?php echo $error; //affichage de la variable $error  ?>
    <?= $success ?>
    <div class="signup_form">
        <form method="POST">
            <label class="form-label" for="pseudo">Pseudo</label><br>
            <input class="form-control" id="pseudo" type="text" name="pseudo" required minlength="3" maxlength="15"><br>

            <label class="form-label" for="mdp">Mot de passe</label><br>
            <input class="form-control" type="text" id="mdp" name="mdp" required><br>

            <label class="form-label" for="nom">Nom</label><br>
            <input class="form-control" type="text" id="nom" name="nom" required><br>

            <label class="form-label" for="prenom">Prénom</label><br>
            <input class="form-control" type="text" id="prenom" name="prenom" required><br>

            <button type="submit" class="btn btn-secondary mt-3">S'inscrire</button>
        </form>
    </div>
</div>

<?php require_once "inc/footer.inc.php"?>