<?php
// Script pour créer les avatars manquants
$avatarDir = __DIR__ . '/front/images/avatars/user/';

// Créer le dossier si nécessaire
if (!is_dir($avatarDir)) {
    mkdir($avatarDir, 0777, true);
}

for ($i = 1; $i <= 12; $i++) {
    $image = imagecreate(100, 100);
    
    $colors = [
        imagecolorallocate($image, 52, 152, 219),   // Bleu
        imagecolorallocate($image, 46, 204, 113),   // Vert
        imagecolorallocate($image, 231, 76, 60),    // Rouge
        imagecolorallocate($image, 155, 89, 182),   // Violet
        imagecolorallocate($image, 241, 196, 15),   // Jaune
        imagecolorallocate($image, 26, 188, 156),   // Turquoise
        imagecolorallocate($image, 211, 84, 0),     // Orange
        imagecolorallocate($image, 52, 73, 94),     // Gris foncé
        imagecolorallocate($image, 230, 126, 34),   // Carotte
        imagecolorallocate($image, 39, 174, 96),    // Vert foncé
        imagecolorallocate($image, 142, 68, 173),   // Violet foncé
        imagecolorallocate($image, 192, 57, 43),    // Rouge foncé
    ];
    
    $backgroundColor = $colors[$i-1];
    $textColor = imagecolorallocate($image, 255, 255, 255);
    
    imagefill($image, 0, 0, $backgroundColor);
    imagestring($image, 5, 40, 40, $i, $textColor);
    
    imagepng($image, $avatarDir . "user-$i.png");
    imagedestroy($image);
    
    echo "Avatar $i créé\n";
}

echo "✅ Tous les avatars ont été créés !\n";
echo "Dossier : " . $avatarDir . "\n";
?>