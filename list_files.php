<?php
// Zabezpe�en� proti neopr�vn�n�mu p��stupu k soubor�m
function isPathSafe($path) {
    // Kontrola, �e cesta neobsahuje ".."
    if (strpos($path, '..') !== false) {
        return false;
    }
    
    // Dal�� bezpe�nostn� kontroly lze p�idat zde
    
    return true;
}

// Z�sk�n� parametru slo�ky z GET po�adavku
$folderPath = isset($_GET['folder']) ? $_GET['folder'] : '';

// Kontrola, �e cesta je bezpe�n�
if (!isPathSafe($folderPath)) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Neplatn� cesta']);
    exit;
}

// Funkce pro rekurzivn� proch�zen� adres��ov� struktury
function scanDirectory($dir) {
    $result = [];
    
    // Kontrola, �e adres�� existuje
    if (!file_exists($dir) || !is_dir($dir)) {
        return $result;
    }
    
    // Z�sk�n� obsahu adres��e
    $files = scandir($dir);
    
    foreach ($files as $file) {
        // P�esko�en� . a ..
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            // Jedn� se o adres��
            $result[$file] = [
                'type' => 'folder',
                'children' => scanDirectory($path)
            ];
        } else {
            // Jedn� se o soubor
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

// Skenov�n� adres��ov� struktury
$structure = [];

if ($folderPath) {
    $structure[$folderPath] = [
        'type' => 'folder',
        'children' => scanDirectory($folderPath)
    ];
} else {
    // Pokud nen� zad�na slo�ka, proch�zet ko�enov� adres��
    $structure = [
        'STL' => [
            'type' => 'folder',
            'children' => scanDirectory('STL')
        ]
    ];
}

// Vr�cen� v�sledku jako JSON
header('Content-Type: application/json');
echo json_encode($structure);