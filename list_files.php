<?php
// Zabezpeèení proti neoprávnìnému pøístupu k souborùm
function isPathSafe($path) {
    // Kontrola, že cesta neobsahuje ".."
    if (strpos($path, '..') !== false) {
        return false;
    }
    
    // Další bezpeènostní kontroly lze pøidat zde
    
    return true;
}

// Získání parametru složky z GET požadavku
$folderPath = isset($_GET['folder']) ? $_GET['folder'] : '';

// Kontrola, že cesta je bezpeèná
if (!isPathSafe($folderPath)) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Neplatná cesta']);
    exit;
}

// Funkce pro rekurzivní procházení adresáøové struktury
function scanDirectory($dir) {
    $result = [];
    
    // Kontrola, že adresáø existuje
    if (!file_exists($dir) || !is_dir($dir)) {
        return $result;
    }
    
    // Získání obsahu adresáøe
    $files = scandir($dir);
    
    foreach ($files as $file) {
        // Pøeskoèení . a ..
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            // Jedná se o adresáø
            $result[$file] = [
                'type' => 'folder',
                'children' => scanDirectory($path)
            ];
        } else {
            // Jedná se o soubor
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            
            // Ponechat pouze STL a PDF soubory
            if ($extension === 'stl' || $extension === 'pdf') {
                $result[$file] = [
                    'type' => 'file',
                    'extension' => $extension
                ];
            }
        }
    }
    
    return $result;
}

// Skenování adresáøové struktury
$structure = [];

if ($folderPath) {
    $structure[$folderPath] = [
        'type' => 'folder',
        'children' => scanDirectory($folderPath)
    ];
} else {
    // Pokud není zadána složka, procházet koøenový adresáø
    $structure = [
        'STL' => [
            'type' => 'folder',
            'children' => scanDirectory('STL')
        ]
    ];
}

// Vrácení výsledku jako JSON
header('Content-Type: application/json');
echo json_encode($structure);