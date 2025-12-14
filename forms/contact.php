<?php
// Contact form handler — uses the PHP Email Form helper included with the template.
// The form forwards messages to the configured receiving address and uses the
// SendGrid SMTP credentials when present via the `SENDGRID_API_KEY` environment variable.

$receiving_email_address = 'admin@provistaconsultants.com';
date_default_timezone_set('Asia/Riyadh');

// Load the helper library shipped with the template (expected path)
if (file_exists($php_email_form = '../assets/vendor/php-email-form/php-email-form.php')) {
  include($php_email_form);
} else {
  // If the helper is not present, stop early — it's required for sending.
  die('Unable to load the PHP Email Form library.');
}

$contact = new PHP_Email_Form;
$contact->ajax = true;

$contact->to = $receiving_email_address;
$contact->from_name = $_POST['name'] ?? '';
$contact->from_email = $_POST['email'] ?? '';

// Prepare a subject that includes a timestamp and the site source
$timestamp = date('Y-m-d H:i:s');
$safe_subject = isset($_POST['subject']) ? trim($_POST['subject']) : 'No subject';
$contact->subject = "[Website Message] " . $safe_subject . " — " . $timestamp;

// Configure SMTP. Using SendGrid is recommended; provide API key via env var.
$sendgrid_key = getenv('SENDGRID_API_KEY') ?: null;
$contact->smtp = array(
  'host' => 'smtp.sendgrid.net',
  'username' => 'apikey',
  'password' => $sendgrid_key,
  'port' => '587',
  'secure' => 'tls'
);

$contact->add_message($_POST['name'] ?? '', 'From');
$contact->add_message($_POST['email'] ?? '', 'Email');
$contact->add_message($_POST['message'] ?? '', 'Message', 10);

echo $contact->send();
?>
