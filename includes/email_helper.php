<?php
require_once __DIR__ . '/../config/email.php';

$hasPHPMailer = false;
$smtpPath = __DIR__ . '/../smtp';
if (file_exists($smtpPath . '/PHPMailer.php') && file_exists($smtpPath . '/SMTP.php') && file_exists($smtpPath . '/Exception.php')) {
    require_once $smtpPath . '/PHPMailer.php';
    require_once $smtpPath . '/SMTP.php';
    require_once $smtpPath . '/Exception.php';
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        $hasPHPMailer = true;
    }

// Send email with a single attachment (e.g., PDF). Fallback to inline HTML if PHPMailer/SMTP not available.
function send_email_with_attachment($to, $subject, $bodyHtml, $attachmentName, $attachmentBytes, $mime = 'application/pdf', $replyName = '', $replyEmail = '')
{
    $siteName = defined('SITE_NAME') ? SITE_NAME : 'Printer Trooper Inc';
    $fromEmail = defined('FROM_EMAIL') ? FROM_EMAIL : 'no-reply@localhost';

    // Try PHPMailer with SMTP first
    try {
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer') && defined('SMTP_ENABLED') && SMTP_ENABLED) {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->Port = SMTP_PORT;
            if (defined('SMTP_SECURE') && SMTP_SECURE) { $mail->SMTPSecure = SMTP_SECURE; }
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->SMTPAutoTLS = true;
            $fromEmailUsed = (defined('SMTP_HOST') && strtolower(SMTP_HOST) === 'smtp.gmail.com') ? SMTP_USER : $fromEmail;
            $mail->setFrom($fromEmailUsed, $siteName);
            $mail->addAddress($to);
            if ($replyEmail) { $mail->addReplyTo($replyEmail, $replyName); }
            $mail->Subject = $subject;
            $mail->Body = $bodyHtml;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $bodyHtml));
            if (!empty($attachmentBytes)) {
                $mail->addStringAttachment($attachmentBytes, $attachmentName, 'base64', $mime);
            }
            return $mail->send();
        }
    } catch (Exception $e) {
        try { $GLOBALS['EMAIL_LAST_ERROR'] = 'PHPMailer exception: ' . $e->getMessage(); } catch (Exception $ignored) {}
    }

    // Fallback: try without attachment using mail()
    return send_email_generic($to, $subject, $bodyHtml, $replyName, $replyEmail);
}
} else {
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            $hasPHPMailer = true;
        }
    }
}

$GLOBALS['EMAIL_LAST_ERROR'] = '';

function send_email_generic($to, $subject, $body, $replyName = '', $replyEmail = '')
{
    $siteName = defined('SITE_NAME') ? SITE_NAME : 'Printer Trooper Inc';
    $fromEmail = defined('FROM_EMAIL') ? FROM_EMAIL : 'no-reply@localhost';
    $fromForSend = (defined('SMTP_HOST') && strtolower(SMTP_HOST) === 'smtp.gmail.com' && defined('SMTP_USER')) ? SMTP_USER : $fromEmail;

    $hasPHPMailer = isset($GLOBALS['hasPHPMailerFlag']) ? (bool)$GLOBALS['hasPHPMailerFlag'] : false;
    if (!$hasPHPMailer && function_exists('class_exists') && class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        $hasPHPMailer = true;
    }

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
            $mail->isHTML(true);
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
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));
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
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    return @mail($to, $subject, $body, implode("\r\n", $headers));
}
