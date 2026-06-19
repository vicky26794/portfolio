<?php
declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

$receivingEmailAddress = 'vickyraghav794@gmail.com';

function clean_input(string $value): string
{
  return trim(str_replace(["\r", "\n"], ' ', $value));
}

function escape_html(string $value): string
{
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$name = clean_input($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = clean_input($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || mb_strlen($name) < 3) {
  http_response_code(422);
  exit('Please enter a valid name.');
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  exit('Please enter a valid email address.');
}

if ($subject === '' || mb_strlen($subject) < 5) {
  http_response_code(422);
  exit('Please enter a valid subject.');
}

if ($message === '' || mb_strlen($message) < 10) {
  http_response_code(422);
  exit('Please enter a valid message.');
}

$safeName = escape_html($name);
$safeEmail = escape_html($email);
$safeSubject = escape_html($subject);
$safeMessage = nl2br(escape_html($message));
$submittedAt = date('Y-m-d H:i:s');

$emailSubject = 'Portfolio Contact: ' . $subject;
$emailBody = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>New Portfolio Contact Message</title>
</head>
<body style="margin:0;padding:24px;background-color:#f4f6fb;font-family:Arial,sans-serif;color:#1f2937;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
    <tr>
      <td style="padding:24px 28px;background:#0ea5e9;color:#ffffff;">
        <h2 style="margin:0;font-size:24px;">New Contact Form Submission</h2>
        <p style="margin:8px 0 0;font-size:14px;opacity:0.95;">You received a new message from your portfolio website.</p>
      </td>
    </tr>
    <tr>
      <td style="padding:28px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
          <tr>
            <td style="padding:10px 0;border-bottom:1px solid #e5e7eb;width:140px;font-weight:bold;">Name</td>
            <td style="padding:10px 0;border-bottom:1px solid #e5e7eb;">{$safeName}</td>
          </tr>
          <tr>
            <td style="padding:10px 0;border-bottom:1px solid #e5e7eb;width:140px;font-weight:bold;">Email</td>
            <td style="padding:10px 0;border-bottom:1px solid #e5e7eb;">{$safeEmail}</td>
          </tr>
          <tr>
            <td style="padding:10px 0;border-bottom:1px solid #e5e7eb;width:140px;font-weight:bold;">Subject</td>
            <td style="padding:10px 0;border-bottom:1px solid #e5e7eb;">{$safeSubject}</td>
          </tr>
          <tr>
            <td style="padding:10px 0;border-bottom:1px solid #e5e7eb;width:140px;font-weight:bold;">Submitted</td>
            <td style="padding:10px 0;border-bottom:1px solid #e5e7eb;">{$submittedAt}</td>
          </tr>
        </table>

        <div style="margin-top:24px;">
          <h3 style="margin:0 0 12px;font-size:18px;color:#111827;">Message</h3>
          <div style="padding:18px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;line-height:1.7;">
            {$safeMessage}
          </div>
        </div>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

$headers = [
  'MIME-Version: 1.0',
  'Content-type: text/html; charset=UTF-8',
  'From: Portfolio Contact Form <no-reply@' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . '>',
  'Reply-To: ' . $name . ' <' . $email . '>',
  'X-Mailer: PHP/' . phpversion(),
];

$mailSent = mail($receivingEmailAddress, $emailSubject, $emailBody, implode("\r\n", $headers));

if (!$mailSent) {
  http_response_code(500);
  exit('Unable to send your message right now. Please try again later.');
}

echo 'OK';
