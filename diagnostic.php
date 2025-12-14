<?php
// diagnostic.php - Diagnostic complet
require __DIR__.'/vendor/autoload.php';

echo "🩺 DIAGNOSTIC COMPLET VETCARE\n";
echo "==============================\n\n";

// Test 1: PHP et extensions
echo "1. 🐘 ENVIRONNEMENT PHP:\n";
echo "   PHP Version: " . PHP_VERSION . "\n";
echo "   OS: " . PHP_OS . "\n";
echo "   Memory Limit: " . ini_get('memory_limit') . "\n";
echo "   Max Execution Time: " . ini_get('max_execution_time') . "s\n";

// Test 2: Extensions requises
$requiredExtensions = ['openssl', 'pdo', 'mbstring', 'xml', 'curl', 'json'];
echo "\n2. 📦 EXTENSIONS PHP:\n";
foreach ($requiredExtensions as $ext) {
    echo "   " . $ext . ": " . (extension_loaded($ext) ? "✅" : "❌") . "\n";
}

// Test 3: Fichiers de configuration
echo "\n3. 📁 FICHIERS DE CONFIGURATION:\n";
$configFiles = [
    '.env' => 'Fichier .env',
    '.env.local' => 'Fichier .env.local',
    'config/packages/mailer.yaml' => 'Configuration mailer',
    'var/log/dev.log' => 'Fichier de log',
];

foreach ($configFiles as $file => $description) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "   ✅ $description: Existe (" . $size . " bytes)\n";
        
        // Lire le contenu si c'est .env.local
        if ($file === '.env.local') {
            $content = file_get_contents($path);
            if (strpos($content, 'MAILER_DSN') !== false) {
                echo "      Contient MAILER_DSN: OUI\n";
                // Extraire le DSN
                preg_match('/MAILER_DSN=(.+)/', $content, $matches);
                if ($matches) {
                    echo "      Valeur: " . substr($matches[1], 0, 60) . "...\n";
                }
            } else {
                echo "      ❌ NE contient PAS MAILER_DSN\n";
            }
        }
    } else {
        echo "   ❌ $description: NON TROUVÉ\n";
    }
}

// Test 4: Connexion Internet
echo "\n4. 🌐 CONNEXION INTERNET:\n";
$testHosts = [
    'sandbox.smtp.mailtrap.io' => 'Mailtrap SMTP',
    'mailtrap.io' => 'Mailtrap Website',
    'google.com' => 'Google',
];

foreach ($testHosts as $host => $description) {
    $start = microtime(true);
    $connected = @fsockopen($host, 587, $errno, $errstr, 5);
    $time = round((microtime(true) - $start) * 1000, 2);
    
    if ($connected) {
        echo "   ✅ $description ($host): Connecté ({$time}ms)\n";
        fclose($connected);
    } else {
        echo "   ❌ $description ($host): Échec (Err: $errno - $errstr)\n";
    }
}

// Test 5: Ports Mailtrap
echo "\n5. 🔌 PORTS MAILTRAP:\n";
$ports = [587, 2525, 25, 465];
$host = 'sandbox.smtp.mailtrap.io';

foreach ($ports as $port) {
    $start = microtime(true);
    $fp = @fsockopen($host, $port, $errno, $errstr, 3);
    $time = round((microtime(true) - $start) * 1000, 2);
    
    if ($fp) {
        echo "   ✅ Port $port: OUVERT ({$time}ms)\n";
        
        // Lire la bannière SMTP
        $banner = fgets($fp, 1024);
        echo "      Bannière: " . trim($banner) . "\n";
        
        fclose($fp);
    } else {
        echo "   ❌ Port $port: FERMÉ ({$time}ms) - $errstr\n";
    }
}

// Test 6: Configuration Symfony
echo "\n6. ⚙️ CONFIGURATION SYMFONY:\n";
try {
    $kernel = new App\Kernel('dev', true);
    $kernel->boot();
    $container = $kernel->getContainer();
    
    echo "   ✅ Kernel Symfony: DÉMARRÉ\n";
    
    // Vérifier les services
    $services = ['mailer', 'logger'];
    foreach ($services as $service) {
        if ($container->has($service)) {
            echo "   ✅ Service $service: DISPONIBLE\n";
        } else {
            echo "   ❌ Service $service: ABSENT\n";
        }
    }
    
    // Vérifier les paramètres
    $parameters = ['kernel.environment', 'kernel.debug', 'kernel.project_dir'];
    foreach ($parameters as $param) {
        try {
            $value = $container->getParameter($param);
            echo "   ✅ Paramètre $param: $value\n";
        } catch (\Exception $e) {
            echo "   ❌ Paramètre $param: ERREUR\n";
        }
    }
    
    $kernel->shutdown();
    
} catch (\Exception $e) {
    echo "   ❌ Erreur Symfony: " . $e->getMessage() . "\n";
}

// Test 7: Test SMTP manuel
echo "\n7. 📧 TEST SMTP MANUEL:\n";
echo "   Commande à exécuter dans PowerShell:\n";
echo "   ------------------------------------\n";
echo "   \$socket = New-Object Net.Sockets.TcpClient\n";
echo "   \$socket.Connect('sandbox.smtp.mailtrap.io', 587)\n";
echo "   \$stream = \$socket.GetStream()\n";
echo "   \$writer = New-Object IO.StreamWriter \$stream\n";
echo "   \$reader = New-Object IO.StreamReader \$stream\n";
echo "   \$writer.WriteLine('EHLO test')\n";
echo "   \$writer.Flush()\n";
echo "   Write-Host \$reader.ReadLine()\n";
echo "   ------------------------------------\n";

// Test 8: Vérifier le firewall Windows
echo "\n8. 🔥 FIREWALL WINDOWS:\n";
echo "   Vérifiez que le firewall n'bloque pas PHP:\n";
echo "   1. Ouvrez 'Pare-feu Windows Defender'\n";
echo "   2. Cliquez sur 'Paramètres avancés'\n";
echo "   3. Vérifiez les règles pour 'php.exe' et 'Apache'\n";
echo "   4. Autorisez les connexions sortantes\n";

// Test 9: Solution alternative
echo "\n9. 🚀 SOLUTION ALTERNATIVE:\n";
echo "   A. Utiliser un transport local pour les tests:\n";
echo "      MAILER_DSN=file:///%kernel.project_dir%/var/mail\n";
echo "   B. Les emails seront sauvegardés dans var/mail/\n";
echo "   C. Pas besoin de Mailtrap pour le développement\n";

echo "\n==============================\n";
echo "🎯 ACTION IMMÉDIATE:\n\n";

echo "1. Créez ce .env.local ULTRA SIMPLE:\n";
echo "-------------------------------------\n";
echo "MAILER_DSN=smtp://5135a7c0a1e786:308a605f7c8c28@sandbox.smtp.mailtrap.io:587\n";
echo "MAILER_FROM=noreply@vetcare.com\n";
echo "APP_ENV=dev\n";
echo "APP_DEBUG=1\n";
echo "-------------------------------------\n";

echo "\n2. Exécutez ces commandes:\n";
echo "   php bin/console cache:clear\n";
echo "   php bin/console debug:container mailer\n";

echo "\n3. Testez avec ce script simple:\n";
?>