<?php
// Simple application handler: saves uploaded CV and cover letter and logs the application.
date_default_timezone_set('Asia/Riyadh');

$uploadsDir = __DIR__ . '/uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

$timestamp = date('Y-m-d_H-i-s');
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$job_id = trim($_POST['job_id'] ?? '');
$job_title = trim($_POST['job_title'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$country = trim($_POST['country'] ?? '');

$allowed_ext = ['pdf','doc','docx','txt'];

function sanitize_filename($s) {
    $s = preg_replace('/[^A-Za-z0-9._-]/', '_', $s);
    return $s;
}

$save_cv = '';
if (!empty($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
    $cv = $_FILES['cv'];
    $ext = strtolower(pathinfo($cv['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed_ext)) {
        $save_cv = $timestamp . '_cv_' . sanitize_filename($cv['name']);
        move_uploaded_file($cv['tmp_name'], $uploadsDir . '/' . $save_cv);
    }
}

$save_cover = '';
if (!empty($_FILES['cover_letter']) && $_FILES['cover_letter']['error'] === UPLOAD_ERR_OK) {
    $cover = $_FILES['cover_letter'];
    $ext = strtolower(pathinfo($cover['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed_ext)) {
        $save_cover = $timestamp . '_cover_' . sanitize_filename($cover['name']);
        move_uploaded_file($cover['tmp_name'], $uploadsDir . '/' . $save_cover);
    }
}

$logFile = __DIR__ . '/applications.csv';
$fp = fopen($logFile, 'a');
if ($fp) {
  fputcsv($fp, [$timestamp, $name, $email, $mobile, $country, $job_id, $job_title, $save_cv, $save_cover, $_SERVER['REMOTE_ADDR'] ?? '']);
  fclose($fp);
}

// Send notification via SendGrid if API key is provided in environment
$sendgrid_key = getenv('SENDGRID_API_KEY') ?: null;
if ($sendgrid_key) {
  $admin_email = 'admin@provistaconsultants.com';
  $subject = "[New Application] {$job_title} — {$timestamp}";
  $body_lines = [];
  $body_lines[] = "Name: {$name}";
  $body_lines[] = "Email: {$email}";
  $body_lines[] = "Mobile: {$mobile}";
  $body_lines[] = "Country: {$country}";
  $body_lines[] = "Job: {$job_title} (ID: {$job_id})";
  $body_lines[] = "Timestamp: {$timestamp}";
  if ($save_cv) $body_lines[] = "CV: {$save_cv}";
  if ($save_cover) $body_lines[] = "Cover: {$save_cover}";
  $content = implode("\n", $body_lines);

  $payload = [
    'personalizations' => [[
      'to' => [[ 'email' => $admin_email ]],
      'subject' => $subject
    ]],
    'from' => [ 'email' => 'no-reply@provistaconsultants.com', 'name' => 'Provista Website' ],
    'content' => [[ 'type' => 'text/plain', 'value' => $content ]]
  ];

  // Attach files if present
  $attachments = [];
  if ($save_cv && file_exists($uploadsDir . '/' . $save_cv)) {
    $data = base64_encode(file_get_contents($uploadsDir . '/' . $save_cv));
    $attachments[] = [
      'content' => $data,
      'type' => mime_content_type($uploadsDir . '/' . $save_cv) ?: 'application/octet-stream',
      'filename' => $save_cv,
      'disposition' => 'attachment'
    ];
  }
  if ($save_cover && file_exists($uploadsDir . '/' . $save_cover)) {
    $data = base64_encode(file_get_contents($uploadsDir . '/' . $save_cover));
    $attachments[] = [
      'content' => $data,
      'type' => mime_content_type($uploadsDir . '/' . $save_cover) ?: 'application/octet-stream',
      'filename' => $save_cover,
      'disposition' => 'attachment'
    ];
  }
  if (!empty($attachments)) $payload['attachments'] = $attachments;

  $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $sendgrid_key,
    'Content-Type: application/json'
  ]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
  $result = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  if ($result === false || $http_code >= 400) {
    // Log SendGrid error to server log for debugging
    error_log('SendGrid error: ' . curl_error($ch) . ' response: ' . $result);
  }
  curl_close($ch);
}
// Simple confirmation page
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Application Received</title>
  <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body style="margin-top:90px;">
  <div class="container">
    <div class="card">
      <div class="card-body">
        <h3>Application received</h3>
        <p>Thank you, <?php echo htmlspecialchars($name ?: 'Applicant'); ?>. Your application for <?php echo htmlspecialchars($job_title ?: 'the position'); ?> has been received.</p>
        <p>Uploaded files are stored on the server. If you want to enable email notifications, configure your SMTP provider and I can wire that in.</p>
        <a href="/careers.html" class="btn btn-primary">Back to Careers</a>
      </div>
    </div>
  </div>
</body>
</html>
