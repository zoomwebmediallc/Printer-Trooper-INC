<?php
require_once __DIR__ . '/config/email.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Appointment.php';

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
function redirect_with_message($success = false, $message = '') {
    $location = 'appointment.php';
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
    header("Location: {$location}");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message(false, 'Invalid request method.');
}

$fullName = trim($_POST['fullName'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$service  = trim($_POST['service'] ?? '');
$date     = trim($_POST['date'] ?? '');
$time     = trim($_POST['time'] ?? '');
$message  = trim($_POST['message'] ?? '');

$errors = [];
if ($fullName === '') $errors[] = 'Full Name is required.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid Email is required.';
if ($phone === '') $errors[] = 'Phone Number is required.';
if ($service === '') $errors[] = 'Service Type is required.';
if ($date === '') $errors[] = 'Preferred Date is required.';
if ($time === '') $errors[] = 'Preferred Time is required.';

if (!empty($errors)) {
    redirect_with_message(false, implode(' ', $errors));
}

try {
    $db = (new Database())->getConnection();
    $appointment = new Appointment($db);
    $appointment->create([
        'fullName' => $fullName,
        'email'    => $email,
        'phone'    => $phone,
        'service'  => $service,
        'date'     => $date,
        'time'     => $time,
        'message'  => $message,
    ]);
} catch (Exception $e) {
    // Continue, but include a notice in the UI message
}

$adminEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'Sahiba@printerstore.com';
$siteName   = defined('SITE_NAME') ? SITE_NAME : 'Printer Trooper Inc';
$fromEmail  = defined('FROM_EMAIL') ? FROM_EMAIL : 'Sahiba@printerstore.com';

$subject = "New Appointment Request - {$siteName}";
$bodyLines = [
    "You have received a new appointment request:",
    "",
    "Name: {$fullName}",
    "Email: {$email}",
    "Phone: {$phone}",
    "Service: {$service}",
    "Date: {$date}",
    "Time: {$time}",
    "",
    "Additional Notes:",
    $message !== '' ? $message : '(none)'
];
$body = implode("\r\n", $bodyLines);

$customerSubject = "Your Appointment Request - {$siteName}";
$customerBodyLines = [
    "Hello {$fullName},",
    "",
    "Thank you for booking an appointment with {$siteName}.",
    "Here are the details we received:",
    "",
    "Service: {$service}",
    "Date: {$date}",
    "Time: {$time}",
    "",
    "We will contact you shortly to confirm your booking.",
    "",
    "Regards,",
    "{$siteName} Team"
];
$customerBody = implode("\r\n", $customerBodyLines);
function send_email_generic($to, $subject, $body, $replyName, $replyEmail, $fromEmail, $siteName, $hasPHPMailer) {
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
            try { $GLOBALS['EMAIL_LAST_ERROR'] = 'PHPMailer exception: ' . $e->getMessage(); } catch (Exception $ignored) {}
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
$sentAdmin = send_email_generic($adminEmail, $subject, $body, $fullName, $email, $fromForSend, $siteName, $hasPHPMailer);
$sentCustomer = send_email_generic($email, $customerSubject, $customerBody, $siteName, $fromForSend, $fromForSend, $siteName, $hasPHPMailer);

if ($sentAdmin) {
    $msg = 'Your appointment request has been sent successfully.';
    if (!$sentCustomer) {
        $msg .= ' Note: Customer confirmation email could not be sent.';
    }
    redirect_with_message(true, $msg);
} else {
    $errorMsg = 'Failed to send email. Please try again later.';
    if (defined('EMAIL_DEBUG') && EMAIL_DEBUG) {
        $error = isset($GLOBALS['EMAIL_LAST_ERROR']) ? $GLOBALS['EMAIL_LAST_ERROR'] : '';
        $errorMsg .= ' Check SMTP credentials and FROM address (should match SMTP account when using Gmail).';
        if (!empty($error)) {
            $errorMsg .= ' Details: ' . $error;
        }
    }
    redirect_with_message(false, $errorMsg);
}
