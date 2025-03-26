<?php require_once "inc/header.inc.php"?>

<?php

//S'il existe une 'action' dans l'URL ET que cette a'action' est égale à déconnexion alors on détruit le fichier de session
if( isset( $_GET['action']) && $_GET['action'] == "deconnexion"){
    session_destroy();
}

//--------------------------
// restriction d'accès à la page si on EST connecté
if( userConnect()){
    echo "<script>window.location.href='index.php';</script>";
    exit;
}


if ($_POST) {

    try {
        // Connexion à MongoDB Atlas
        global $manager;
        
        // Définition de la requête
        $filter = ['username' => "$_POST[pseudo]"];
        $option = [];
        $read = new MongoDB\Driver\Query($filter, $option);
        
        //Exécution de la requête
        $cursor = $manager->executeQuery('forum.users', $read);

        // Vérifier si le curseur est vide
        if (!$cursor->isDead()) {

            foreach ($cursor as $document) {

                if ($document->username == $_POST['pseudo']) {
                    if (password_verify($_POST['mdp'], $document->password)) {
                        //password_verify(arg1, arg2); retourne true ou false et permet de comparer une chaine à une chaine cryptée
                        //arg1 : le mot de passe saisi par l'utilisateur
                        //arg2 la chaine cryptée par la fonction password_hash(), ici le mdp en bdd

                        echo "<div class='alert alert-success'> Salut à toi " . $document->firstname . "!</div>";

                        //insertion des infos ($user) de la personne qui se connecte dans le fichier de session
                        $_SESSION['user'] = $document;

                        //redirection vers la page profil:
                        echo "<script>window.location.href='connexion.php';</script>";

                        exit; //permet de quitter à cet endroit précis le script courant et donc de ne pas interpréter le code qui suit cette instruction.

                    } else { //Sinon, c'est que le mdp n'est pas valide
                        $error .= "<div class='alert alert-danger'> Mot de passe incorrect. </div>";
                    }
                }
            }
        } else {
            // Aucun utilisateur trouvé
            $error .= "<div class='alert alert-danger'> Aucun utilisateur trouvé. </div>";
        }
    } catch (MongoDB\Driver\Exception\Exception $e) {
        echo "Une erreur est survenue: " . $e->getMessage();
        exit();
    }
}

?>

<div class="connexion">
    <h1>Connexion</h1>

    <br><?= $error?><br>

    <form class="connexion_form" method="POST">
        <div class="conteneur2">
            <label class="form-label" for="pseudo">Pseudo:</label><br>
            <input class="form-control" id="pseudo" type="text" name="pseudo" placeholder="Votre pseudo" required>
        </div><br>
        <div class="conteneur2">
            <label class="form-label" for="mdp">Mot de passe:</label><br>
            <input class="form-control" id="mdp" type="password" name="mdp" placeholder="Votre mot de passe" required>
        </div>

        <button type="submit" class="btn btn-secondary mt-3">Se connecter</button>
    </form>
</div>

<?php require_once "inc/footer.inc.php"?>