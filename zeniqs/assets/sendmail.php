<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

class ContactForm
{
    private $smtpHost = 'smtp.gmail.com';
    private $smtpUsername = 'keertim324@gmail.com'; // Gmail for sending
    private $smtpPassword = 'yfbi gjux ltus rjgd';   // App password (not your Gmail password!)
    private $smtpPort = 587;
    private $smtpEncryption = 'tls';

    private $recipient;
    private $fromName;
    private $fromEmail;

    public function __construct($recipient, $fromName, $fromEmail)
    {
        $this->recipient = $recipient;
        $this->fromName = $fromName;
        $this->fromEmail = $fromEmail;
    }

    public function sendEmail($name, $email, $company, $phone, $areaOfInterest, $message)
    {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0; // Set to 2 for debugging
        $mail->Debugoutput = 'html';

        try {
            // SMTP settings
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
            $mail->SMTPSecure = $this->smtpEncryption;
            $mail->Port = $this->smtpPort;

            // FROM & TO
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($this->recipient);
            $mail->addReplyTo($email, $name);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = "New Contact Form Submission - " . $areaOfInterest;
            $mail->Body = $this->buildEmailContent($name, $email, $company, $phone, $areaOfInterest, $message);

            $mail->send();
            echo "✅ Thank you! Your message has been sent.";
        } catch (Exception $e) {
            echo "❌ Mailer Error: " . $mail->ErrorInfo;
        }
    }

    private function buildEmailContent($name, $email, $company, $phone, $areaOfInterest, $message)
    {
        return "
            <h2>New Contact Form Submission</h2>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Company:</strong> $company</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Area of Interest:</strong> $areaOfInterest</p>
            <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
        ";
    }
}

// Config (Admin who receives the email)
$recipient = "jyotisannapanavar@gmail.com";
$fromName = "Zeniqs Website";
$fromEmail = "keertim324@gmail.com";

$contactForm = new ContactForm($recipient, $fromName, $fromEmail);

// Handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"] ?? ""));
    $email = filter_var(trim($_POST["email"] ?? ""), FILTER_SANITIZE_EMAIL);
    $company = strip_tags(trim($_POST["company"] ?? ""));
    $phone = strip_tags(trim($_POST["phone"] ?? ""));
    $areaOfInterest = strip_tags(trim($_POST["area_of_interest"] ?? ""));
    $message = strip_tags(trim($_POST["message"] ?? ""));

    // Only name, email, and message are required (phone & company are optional)
    if (empty($name) || empty($email) || empty($message)) {
        echo "⚠️ Name, Email, and Message are required fields.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "⚠️ Invalid email address.";
        exit;
    }

    $contactForm->sendEmail($name, $email, $company, $phone, $areaOfInterest, $message);
} else {
    echo "❌ Invalid request method.";
}
