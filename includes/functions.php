<?php
// includes/functions.php

function handleFileUpload($file, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'])
{
    if (!isset($file['name']) || empty($file['name'])) {
        return null;
    }

    // Max file size: 5MB
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return ['error' => 'File too large. Maximum size is 5MB.'];
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = basename($file['name']);
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileType, $allowedTypes)) {
        return ['error' => 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes)];
    }

    // Validate MIME type
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp'
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $detectedMime = $finfo->file($file['tmp_name']);
    $validMimes = array_values(array_intersect_key($allowedMimes, array_flip($allowedTypes)));
    if (!in_array($detectedMime, $validMimes)) {
        return ['error' => 'File MIME type mismatch. Detected: ' . $detectedMime];
    }

    // Sanitize filename
    $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
    $cleanName = preg_replace('/[^a-zA-Z0-9]/', '_', $fileNameWithoutExt);
    $cleanName = strtolower($cleanName);

    // Create unique name: timestamp_random_cleanname.ext
    $newFileName = time() . '_' . rand(1000, 9999) . '_' . $cleanName . '.' . $fileType;
    $targetPath = $targetDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $newFileName];
    } else {
        return ['error' => 'Failed to move uploaded file.'];
    }
}

function getProfileImage($imagePath)
{
    if (empty($imagePath)) {
        return 'https://api.dicebear.com/9.x/avataaars/svg?seed=' . rand();
    }
    if (strpos($imagePath, 'http') === 0) {
        return $imagePath;
    }
    // Assume relative path logic is handled by caller or we return filename
    // Actually, callers often prepend 'uploads/users/'. 
    // Let's just return the filename and let caller handle path, 
    // OR we could return full path if we knew base.
    // For now, return filename as is, but ensure it's URL encoded if we were just printing it.
    // But since this function is for logic, let's keep it simple.
    return $imagePath;
}
?>