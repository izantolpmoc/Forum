<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['image']['name'])) {
    $uploadDir = 'assets/uploads/';

    // Crée le dossier s'il n'existe pas
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = time() . '-' . basename($_FILES['image']['name']);
    $uploadFile = $uploadDir . $filename;

    // Déplacement du fichier
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
        echo json_encode(['url' => $uploadFile]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors du téléchargement.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Aucun fichier reçu ou méthode non autorisée.']);
}
