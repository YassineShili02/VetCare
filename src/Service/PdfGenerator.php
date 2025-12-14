<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    public function generatePdf(string $html): string
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans'); // Police Unicode
        $options->set('isRemoteEnabled', true); // Permet l'accès aux URL et chemins absolus
        $options->set('chroot', realpath($_SERVER['DOCUMENT_ROOT'])); // Sécurise l'accès aux fichiers

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait'); // Taille et orientation
        $dompdf->render();

        return $dompdf->output();
    }
}
