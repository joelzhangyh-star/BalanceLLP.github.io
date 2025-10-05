<?php
echo "<script>console.log('Form submission script is being executed!');</script>";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check and sanitize each field
    $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $message = isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '';
    $phone = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';

    // Validate required fields
    if (empty($name) || empty($email) || empty($message) || empty($phone)) {
        echo "All fields are required.";
        exit;
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format!";
        exit;
    }

    // Validate phone number (10 digits only)
    if (!preg_match("/^(\+65)?[689]\d{7}$/", $phone)) {
        echo "Invalid phone number format!";
        exit;
}

    // If everything is valid
    echo "Form submitted successfully!";
    // You could continue to save data, send email, etc.

    // Get file info
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];

    // Allowed file types
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB limit

    // Set the upload directory and file path
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }
    $uploadFilePath = $uploadDir . basename($fileName);

    // Check for upload errors and validate file
    if ($fileError === 0) {
        if (!in_array($fileType, $allowedTypes)) {
            echo "Invalid file type!";
            exit;
        }

        if ($fileSize > $maxFileSize) {
            echo "File size exceeds the limit of 5MB!";
            exit;
        }

        if (move_uploaded_file($fileTmpName, $uploadFilePath)) {
            echo "File uploaded successfully.";
        } else {
            echo "Error uploading file.";
            exit;
        }
    } else {
        echo "Error: " . $fileError;
        exit;
    }

    // Email address where the form data will be sent
    $to = "joelzhangyh@gmail.com"; // Replace with your email
    $subject = "New Message from $name";
    $body = "You have received a new message.\n\nName: $name\nEmail: $email\nPhone: {$_POST['Phone Number']}\nMessage:\n$message";

    // Headers for the email (multipart for attachment)
    $boundary = md5(time());
    $headers = "From: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
    
    // Body of the email
    $emailBody = "--$boundary\r\n";
    $emailBody .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $emailBody .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $emailBody .= "Name: $name\nEmail: $email\nPhone: {$_POST['Phone Number']}\nMessage: $message\r\n";
    
    // Add attachment
    $fileContent = chunk_split(base64_encode(file_get_contents($uploadFilePath)));
    $emailBody .= "--$boundary\r\n";
    $emailBody .= "Content-Type: $fileType; name=\"$fileName\"\r\n";
    $emailBody .= "Content-Disposition: attachment; filename=\"$fileName\"\r\n";
    $emailBody .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $emailBody .= $fileContent . "\r\n";
    $emailBody .= "--$boundary--";

    // Send the email
    if (mail($to, $subject, $emailBody, $headers)) {
        echo "Message sent successfully!";
    } else {
        echo "Message could not be sent. Please try again.";
    }

    // Optionally delete the uploaded file after email is sent
    unlink($uploadFilePath);
}
?>