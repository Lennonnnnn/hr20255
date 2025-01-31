<?php
session_start();
require_once '../db/db_conn.php';  // Replace with your actual DB connection

// Handle the image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    
    // Define the directory where you want to save the uploaded files
    $targetDir = "../uploads/profile_pictures/";  // Create this directory on your server if it doesn't exist
    
    // Construct the full file path
    $targetFile = $targetDir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is a valid image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        echo json_encode(['success' => false, 'message' => 'File is not an image.']);
        exit;
    }

    // Limit file size to 2MB
    if ($file["size"] > 2000000) {
        echo json_encode(['success' => false, 'message' => 'Sorry, your file is too large.']);
        exit;
    }

    // Allow only certain file formats
    if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
        echo json_encode(['error' => false, 'message' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.']);
        exit;
    }

    // Try to upload the file
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        // Save the file path to the database
        $adminId = $_SESSION['a_id'];  // Get the employee ID from session
        $sql = "UPDATE admin_register SET pfp = ? WHERE a_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $targetFile, $adminId);
        
        if ($stmt->execute()) {
            // Redirect back to the profile page after deletion
            header('Location: ../admin/profile.php');
            exit(); // Ensure to stop script execution after redirect
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating profile picture.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Sorry, there was an error uploading your file.']);
    }
}
?>
