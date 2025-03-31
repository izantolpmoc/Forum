// Description: Upload image to server using the Trix editor

document.addEventListener('trix-attachment-add', function (event) {
    const attachment = event.attachment;
    
    if (attachment.file) {
    const formData = new FormData();
    formData.append('image', attachment.file);

    fetch('upload.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(result => {
        if (result.url) {
        // Corriger l'URL pour qu'elle soit accessible depuis le navigateur
        const publicUrl = result.url.startsWith('assets/') ? result.url : `assets/uploads/${result.url}`;
        attachment.setAttributes({
            url: publicUrl,
            href: publicUrl,
        });
        } else {
        console.error(result.error);
        }
    })
    .catch(error => {
        console.error('Erreur lors du téléchargement:', error);
    });
    }
});
