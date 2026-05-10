<?php
// Simple PHP file upload test without Laravel
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    if (!isset($_FILES['document'])) {
        echo json_encode(['error' => 'No file uploaded']);
        exit;
    }

    $uploadedFile = $_FILES['document'];
    
    // Basic file validation without MIME type detection
    if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'File upload error: ' . $uploadedFile['error']]);
        exit;
    }

    // Check file size (10MB)
    if ($uploadedFile['size'] > 10485760) {
        echo json_encode(['error' => 'File too large']);
        exit;
    }

    // Check extension
    $extension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
    if ($extension !== 'pdf') {
        echo json_encode(['error' => 'Only PDF files allowed']);
        exit;
    }

    // Check PDF signature
    $content = file_get_contents($uploadedFile['tmp_name']);
    if (substr($content, 0, 4) !== '%PDF') {
        echo json_encode(['error' => 'Invalid PDF file']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'File upload test successful',
        'file_info' => [
            'name' => $uploadedFile['name'],
            'size' => $uploadedFile['size'],
            'extension' => $extension
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Exception: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>