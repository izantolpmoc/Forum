<?php 

//connexion à MongoDB ATLAS
//$manager = new MongoDB\Driver\Manager('mongodb+srv://izantolpmoc:Xwch2GQQmahZXjUQ@cluster0.yobfos8.mongodb.net/test');
$manager = new MongoDB\Driver\Manager('mongodb+srv://izantolpmoc:aFL2kB3rekJI5K1Z@cluster0.jfsqt.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0');

//fonction pour vérifier si un utilisateur est connecté
function userConnect(){
    if( !isset( $_SESSION['user']) )
        return false;
    return true;
}

//-----------------------------------------
//definition des variables:

$content = '';//variable prévue pour recevoir du contenu
$success = ""; //variable prévue pour recevoir les messages de réussite
$error = '';//variable prévue pour recevoir les messages d'erreur
//------------------------------------------