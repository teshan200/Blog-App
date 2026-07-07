<?php
/**
 * Image upload endpoint for EasyMDE.
 *
 * Accepts POST requests with a file in the "image" field.
 * Uses the shared upload_image() helper, then returns JSON
 * suitable for EasyMDE's built-in uploader.
 *
 * Response format: { "data": { "filePath": "uploads/abc.jpg" } }
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST required.']);
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'You must be logged in to upload images.']);
    exit;
}

$result = upload_image($_FILES['image'] ?? []);

// If the result is a path starting with "uploads/", it succeeded.
if (is_string($result) && strncmp($result, 'uploads/', 8) === 0) {
    echo json_encode(['data' => ['filePath' => $result]]);
} else {
    http_response_code(400);
    echo json_encode(['error' => $result]);
}
