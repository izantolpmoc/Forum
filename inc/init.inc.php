<?php 

//connexion à MongoDB ATLAS
$env = parse_ini_file(".env");
$mongoUri = $env['MONGODB_URI'];
$manager = new MongoDB\Driver\Manager($mongoUri);

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