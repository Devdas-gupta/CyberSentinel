<?php

// Directory to save snapshots
$uploadDir = __DIR__ . '/snapshots/';

// Ensure the directory exists and is writable
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) { // Secure permissions: owner read/write, others read-only
        die("Error: Failed to create snapshots directory.");
    }
}

// Set appropriate permissions for the directory
chmod($uploadDir, 0755);

// Check if a file was uploaded
if (isset($_FILES['snapshot']) && $_FILES['snapshot']['error'] === UPLOAD_ERR_OK) {
    // Sanitize the file name
    $originalFileName = basename($_FILES['snapshot']['name']);
    $safeFileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $originalFileName);

    // Generate a unique file name
    $uniqueFileName = time() . '_' . bin2hex(random_bytes(8)) . '_' . $safeFileName;
    $uploadFile = $uploadDir . $uniqueFileName;

    // Check file type
    $fileType = mime_content_type($_FILES['snapshot']['tmp_name']);
    $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];

    if (!in_array($fileType, $allowedTypes)) {
        die("Error: Invalid file type. Only PNG, JPEG, and GIF are allowed.");
    }

    // Move the uploaded file to the specified directory
    if (move_uploaded_file($_FILES['snapshot']['tmp_name'], $uploadFile)) {
        echo "Snapshot saved successfully as $uniqueFileName.";
    } else {
        die("Error: Failed to save the uploaded file.");
    }
} else {
    // Handle file upload errors
    $errorMessage = "Error: Unknown upload error.";
    if (isset($_FILES['snapshot']['error'])) {
        switch ($_FILES['snapshot']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMessage = "Error: File size exceeds the allowed limit.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMessage = "Error: The file was only partially uploaded.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMessage = "Error: No file was uploaded.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMessage = "Error: Missing a temporary folder.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errorMessage = "Error: Failed to write the file to disk.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $errorMessage = "Error: File upload stopped by a PHP extension.";
                break;
        }
    }
    die($errorMessage);
}

?>
