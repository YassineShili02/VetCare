/**
 * Gestion de l'upload de photos de profil
 * Supporte : Gallery, Camera, Avatar
 */

class ProfilePhotoUpload {
    constructor() {
        this.avatarModal = null;
        this.cameraStream = null;
        this.photoFile = null;
        this.userId = null;
        
        this.init();
    }
    
    init() {
        // Récupérer l'ID utilisateur
        this.userId = document.getElementById('user-id')?.value || 
                     document.querySelector('[data-user-id]')?.dataset.userId;
        
        // Initialiser les événements
        this.initAvatarUpload();
        this.initCameraCapture();
        this.initGalleryUpload();
    }
    
    /**
     * Upload depuis la galerie
     */
    initGalleryUpload() {
        const galleryBtn = document.getElementById('upload-from-gallery');
        const fileInput = document.getElementById('profile-photo-input');
        
        if (galleryBtn && fileInput) {
            galleryBtn.addEventListener('click', () => {
                fileInput.click();
            });
            
            fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    this.handleFileUpload(file);
                }
            });
        }
    }
    
    /**
     * Capture photo avec la caméra
     */
    initCameraCapture() {
        const cameraBtn = document.getElementById('capture-camera');
        const videoElement = document.getElementById('camera-preview');
        const captureBtn = document.getElementById('capture-photo');
        const canvas = document.getElementById('photo-canvas');
        const context = canvas?.getContext('2d');
        
        if (cameraBtn && videoElement) {
            cameraBtn.addEventListener('click', () => {
                this.openCameraModal();
                this.startCamera(videoElement);
            });
            
            if (captureBtn && canvas && context) {
                captureBtn.addEventListener('click', () => {
                    // Capturer la photo
                    canvas.width = videoElement.videoWidth;
                    canvas.height = videoElement.videoHeight;
                    context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
                    
                    // Convertir en fichier
                    canvas.toBlob((blob) => {
                        const file = new File([blob], `camera_${Date.now()}.jpg`, {
                            type: 'image/jpeg'
                        });
                        this.handleFileUpload(file);
                        this.closeCamera();
                    }, 'image/jpeg', 0.9);
                });
            }
        }
    }
    
    /**
     * Sélection d'un avatar prédéfini
     */
    initAvatarUpload() {
        const avatarItems = document.querySelectorAll('.avatar-item');
        
        avatarItems.forEach(avatar => {
            avatar.addEventListener('click', () => {
                const avatarUrl = avatar.dataset.avatarUrl;
                this.saveAvatarSelection(avatarUrl);
            });
        });
    }
    
    /**
     * Démarrer la caméra
     */
    async startCamera(videoElement) {
        try {
            this.cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' },
                audio: false
            });
            videoElement.srcObject = this.cameraStream;
        } catch (error) {
            console.error('Erreur caméra:', error);
            alert('Impossible d\'accéder à la caméra. Vérifiez les permissions.');
        }
    }
    
    /**
     * Traiter le fichier uploadé
     */
    handleFileUpload(file) {
        // Vérifier le type et la taille
        if (!file.type.match('image.*')) {
            alert('Veuillez sélectionner une image');
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) { // 5MB max
            alert('L\'image ne doit pas dépasser 5MB');
            return;
        }
        
        this.photoFile = file;
        this.previewImage(file);
        this.uploadToServer(file);
    }
    
    /**
     * Prévisualiser l'image
     */
    previewImage(file) {
        const reader = new FileReader();
        const preview = document.getElementById('photo-preview');
        const avatarImg = document.querySelector('.profile-avatar');
        
        reader.onload = (e) => {
            const imgUrl = e.target.result;
            
            if (preview) {
                preview.src = imgUrl;
                preview.style.display = 'block';
            }
            
            if (avatarImg) {
                avatarImg.src = imgUrl;
            }
        };
        
        reader.readAsDataURL(file);
    }
    
    /**
     * Upload vers le serveur
     */
    async uploadToServer(file) {
        const formData = new FormData();
        formData.append('profile_photo', file);
        formData.append('user_id', this.userId);
        
        try {
            const response = await fetch('/user/upload-photo', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess('Photo de profil mise à jour avec succès!');
                
                // Mettre à jour l'image dans la navbar si nécessaire
                const navAvatar = document.querySelector('.navbar-avatar');
                if (navAvatar && result.photo_url) {
                    navAvatar.src = result.photo_url;
                }
            } else {
                this.showError(result.message || 'Erreur lors de l\'upload');
            }
        } catch (error) {
            console.error('Upload error:', error);
            this.showError('Erreur réseau lors de l\'upload');
        }
    }
    
    /**
     * Sauvegarder la sélection d'avatar
     */
    async saveAvatarSelection(avatarUrl) {
        try {
            const response = await fetch('/user/save-avatar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    user_id: this.userId,
                    avatar_url: avatarUrl
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess('Avatar mis à jour avec succès!');
                
                // Mettre à jour l'affichage
                const avatarImg = document.querySelector('.profile-avatar');
                if (avatarImg) {
                    avatarImg.src = avatarUrl;
                }
                
                const navAvatar = document.querySelector('.navbar-avatar');
                if (navAvatar) {
                    navAvatar.src = avatarUrl;
                }
            }
        } catch (error) {
            this.showError('Erreur lors de la sauvegarde');
        }
    }
    
    /**
     * Ouvrir modal caméra
     */
    openCameraModal() {
        this.avatarModal = new bootstrap.Modal(document.getElementById('cameraModal'));
        this.avatarModal.show();
    }
    
    /**
     * Fermer la caméra
     */
    closeCamera() {
        if (this.cameraStream) {
            this.cameraStream.getTracks().forEach(track => track.stop());
        }
        
        if (this.avatarModal) {
            this.avatarModal.hide();
        }
    }
    
    /**
     * Afficher message succès
     */
    showSuccess(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.querySelector('.main-content .container').prepend(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    /**
     * Afficher message erreur
     */
    showError(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.querySelector('.main-content .container').prepend(alertDiv);
    }
}

// Initialiser quand le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.profile-page')) {
        new ProfilePhotoUpload();
    }
});