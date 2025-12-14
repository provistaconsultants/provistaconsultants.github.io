<?php
  /**
  * Requires the "PHP Email Form" library
  * The "PHP Email Form" library is available only in the pro version of the template
  * The library should be uploaded to: vendor/php-email-form/php-email-form.php
  * For more info and help: https://bootstrapmade.com/php-email-form/
  */

  // Receiving email address for website messages
  $receiving_email_address = 'admin@provistaconsultants.com';
    // Use Riyadh timezone for message timestamps
    date_default_timezone_set('Asia/Riyadh');

  if( file_exists($php_email_form = '../assets/vendor/php-email-form/php-email-form.php' )) {
    include( $php_email_form );
  } else {
    die( 'Unable to load the "PHP Email Form" Library!');
  }

  $contact = new PHP_Email_Form;
  $contact->ajax = true;
  
  $contact->to = $receiving_email_address;
  $contact->from_name = $_POST['name'];
  $contact->from_email = $_POST['email'];
  // Subject: indicate website source and include timestamp
  $timestamp = date('Y-m-d H:i:s');
  $safe_subject = isset($_POST['subject']) ? trim($_POST['subject']) : 'No subject';
  $contact->subject = "[Website Message] " . $safe_subject . " — " . $timestamp;

    // SMTP configuration - recommended provider: SendGrid
    // Instructions:
    // 1. Create a SendGrid account and generate an API Key with "Full Access" or "Mail Send" permission.
    // 2. Use username 'apikey' and the generated API key as the password below.
    // 3. Replace the 'password' value with your API key.
    // Example: https://docs.sendgrid.com/for-developers/sending-email/getting-started-smtp
    // SendGrid SMTP configuration (reads API key from environment variable)
    $sendgrid_key = getenv('SENDGRID_API_KEY') ?: 'SG.YOUR_SENDGRID_API_KEY_HERE';

    $contact->smtp = array(
      'host' => 'smtp.sendgrid.net',
      'username' => 'apikey',
      'password' => $sendgrid_key,
      'port' => '587',
      'secure' => 'tls'
    );

  $contact->add_message( $_POST['name'], 'From');
  $contact->add_message( $_POST['email'], 'Email');
  $contact->add_message( $_POST['message'], 'Message', 10);

  echo $contact->send();
?>
