<?php
require_once __DIR__ . '/config/email.php';
require_once __DIR__ . '/config/database.php';
$hasPHPMailer = false;
$smtpPath = __DIR__ . '/smtp';
if (file_exists($smtpPath . '/PHPMailer.php') && file_exists($smtpPath . '/SMTP.php') && file_exists($smtpPath . '/Exception.php')) {
    require_once $smtpPath . '/PHPMailer.php';
    require_once $smtpPath . '/SMTP.php';
    require_once $smtpPath . '/Exception.php';
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        $hasPHPMailer = true;
    }
} else {
    $autoloadPath = __DIR__ . '/vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            $hasPHPMailer = true;
        }
    }
}
$GLOBALS['EMAIL_LAST_ERROR'] = '';

function redirect_with_message($success = false, $message = '')
{
    $location = 'contact.php';
    $params = [];
    if ($success) {
        $params['success'] = '1';
    }
    if (!empty($message)) {
        $params['msg'] = urlencode($message);
    }
    if (!empty($params)) {
        $location .= '?' . http_build_query($params);
    }
    header("Location: $location");
    exit();
}

function sanitize($value)
{
    return trim(strip_tags((string)$value));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message(false, 'Invalid request method.');
}

$name    = sanitize($_POST['name'] ?? '');
$email   = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL) ?: '';
$subject = sanitize($_POST['subject'] ?? '');
$message = trim((string)($_POST['message'] ?? ''));

if ($name === '' || mb_strlen($name) < 2) {
    redirect_with_message(false, 'Please enter your name (at least 2 characters).');
}
if ($email === '') {
    redirect_with_message(false, 'Please enter a valid email address.');
}
if ($subject === '' || mb_strlen($subject) < 3) {
    redirect_with_message(false, 'Please enter a subject (at least 3 characters).');
}
if ($message === '' || mb_strlen($message) < 10) {
    redirect_with_message(false, 'Please enter a message (at least 10 characters).');
}

$adminEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@example.com';
$siteName   = defined('SITE_NAME') ? SITE_NAME : 'Printer Trooper Inc';
$fromEmail  = defined('FROM_EMAIL') ? FROM_EMAIL : ($email ?: 'no-reply@localhost');

$body = "New contact form message from {$siteName}\n\n" .
    "Name: {$name}\n" .
    "Email: {$email}\n" .
    "Subject: {$subject}\n\n" .
    "Message:\n{$message}\n";

function send_email_generic($to, $subject, $body, $replyName, $replyEmail, $fromEmail, $siteName, $hasPHPMailer)
{
    if ($hasPHPMailer && defined('SMTP_ENABLED') && SMTP_ENABLED) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->Port = SMTP_PORT;
            if (defined('SMTP_SECURE') && SMTP_SECURE) {
                $mail->SMTPSecure = SMTP_SECURE;
            }
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(false);
            if (defined('EMAIL_DEBUG') && EMAIL_DEBUG) {
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = 'error_log';
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];
            }
            $mail->SMTPAutoTLS = true;
            $fromEmailUsed = (defined('SMTP_HOST') && strtolower(SMTP_HOST) === 'smtp.gmail.com') ? SMTP_USER : $fromEmail;
            $mail->setFrom($fromEmailUsed, $siteName);
            $mail->addAddress($to);
            if ($replyEmail) {
                $mail->addReplyTo($replyEmail, $replyName);
            }
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $body;
            return $mail->send();
        } catch (Exception $e) {
            try {
                $GLOBALS['EMAIL_LAST_ERROR'] = 'PHPMailer exception: ' . $e->getMessage();
            } catch (Exception $ignored) {
            }
            if (isset($mail) && property_exists($mail, 'ErrorInfo') && !empty($mail->ErrorInfo)) {
                $GLOBALS['EMAIL_LAST_ERROR'] .= ' | ErrorInfo: ' . $mail->ErrorInfo;
            }
        }
    }
    $headers = [];
    $headers[] = "From: {$siteName} <{$fromEmail}>";
    if ($replyEmail) {
        $headers[] = "Reply-To: {$replyName} <{$replyEmail}>";
    }
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    return @mail($to, $subject, $body, implode("\r\n", $headers));
}

$fromForSend = (defined('SMTP_HOST') && strtolower(SMTP_HOST) === 'smtp.gmail.com') ? SMTP_USER : $fromEmail;
$sent = send_email_generic($adminEmail, "Contact: {$subject}", $body, $name, $email, $fromForSend, $siteName, $hasPHPMailer);

if ($sent) {
    redirect_with_message(true, 'Thanks! Your message has been sent.');
} else {
    $errorMsg = 'Failed to send message. Please try again later.';
    if (defined('EMAIL_DEBUG') && EMAIL_DEBUG) {
        $error = isset($GLOBALS['EMAIL_LAST_ERROR']) ? $GLOBALS['EMAIL_LAST_ERROR'] : '';
        if (!empty($error)) {
            $errorMsg .= ' Details: ' . $error;
        }
    }
    redirect_with_message(false, $errorMsg);
}
