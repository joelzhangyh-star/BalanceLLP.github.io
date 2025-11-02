<?php
header('Content-Type: text/html; charset=UTF-8');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

echo "<script>console.log('Form submission script is being executed!');</script>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check and sanitize each field
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $message = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description'])) : '';
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

    // Validate phone number (Singapore format)
    if (!preg_match("/^(\+65)?[689]\d{7}$/", $phone)) {
        echo "Invalid phone number format!";
        exit;
    }

    echo "Form submitted successfully!<br>";

    // File upload
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];

    $allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $maxFileSize = 5 * 1024 * 1024;

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $uploadFilePath = $uploadDir . uniqid('', true) . '-' . basename($fileName);

    if ($fileError === 0) {
        if (!in_array($fileType, $allowedTypes)) {
            echo "Invalid file type!";
            exit;
        }

        if ($fileSize > $maxFileSize) {
            echo "File size exceeds the 5MB limit!";
            exit;
        }

        if (move_uploaded_file($fileTmpName, $uploadFilePath)) {
            echo "File uploaded successfully.<br>";
        } else {
            echo "Error uploading file.";
            exit;
        }
    } else {
        echo "Error: " . $fileError;
        exit;
    }

    // Send email via PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USER');
        $mail->Password = getenv('SMTP_PASS');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('joelzhangyh@gmail.com', 'Website Form');
        $mail->addReplyTo($email, $name);
        $mail->addAddress('joelzhangyh@gmail.com');

        $mail->Subject = "New Message from $name";
        $mail->Body = "Name: $name\nEmail: $email\nPhone: $phone\nMessage:\n$message";
        $mail->addAttachment($uploadFilePath, $fileName);

        $mail->send();
        echo "Message sent successfully!";
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }

    // Delete uploaded file after sending
    unlink($uploadFilePath);
}
?>
