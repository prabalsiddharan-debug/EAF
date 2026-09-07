<?php
// api/register.php - Registration API handler with server-side pricing & seating logic

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

require_once __DIR__ . '/db.php';

// Helper function to respond with JSON
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Extract & sanitize POST fields
$fname        = trim($_POST['fname'] ?? '');
$mobile       = trim($_POST['mobile'] ?? '');
$email        = trim($_POST['email'] ?? '');
$company_name = trim($_POST['company_name'] ?? '');
$company_type = trim($_POST['company_type'] ?? '');
$gst_no       = trim($_POST['gst_no'] ?? '');
$facility     = trim($_POST['event_facility'] ?? '');
$address      = trim($_POST['address'] ?? '');
$upi_id       = trim($_POST['upi_id'] ?? '');

// 1. Basic Form Validations
if (empty($fname) || strlen($fname) < 2) {
    sendError('Please enter your full name.');
}

$digitsOnly = preg_replace('/\D/', '', $mobile);
if (!preg_match('/^[6-9]\d{9}$/', $digitsOnly)) {
    sendError('Please enter a valid 10-digit mobile number.');
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendError('Please enter a valid email address.');
}

if (empty($company_name)) {
    sendError('Please enter your company name.');
}

if (empty($company_type)) {
    sendError('Please select a company type.');
}

if (empty($address) || strlen($address) < 5) {
    sendError('Please enter your address.');
}

if (empty($upi_id) || !preg_match('/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/', $upi_id)) {
    sendError('Please enter a valid UPI ID (e.g. name@upi).');
}

// 2. Validate Event Facility & SERVER-SIDE PRICE CALCULATION
$facility_pricing = [
    'Standee (₹500)'                   => 500.00,
    'Show Table (₹500)'                 => 500.00,
    'Standee + Show Table (₹500 + ₹500)'=> 1000.00,
    'Not Required'                      => 0.00
];

if (empty($facility) || !array_key_exists($facility, $facility_pricing)) {
    sendError('Please select a valid event facility.');
}

$registration_fee = 500.00; // Fixed base registration fee (₹500)
$facility_fee     = $facility_pricing[$facility];
$total_amount     = $registration_fee + $facility_fee;

// 3. Screenshot File Upload Validation
if (!isset($_FILES['payment_screenshot']) || $_FILES['payment_screenshot']['error'] !== UPLOAD_ERR_OK) {
    sendError('Payment screenshot is required.');
}

$file = $_FILES['payment_screenshot'];
$maxBytes = 5 * 1024 * 1024; // 5 MB
if ($file['size'] > $maxBytes) {
    sendError('File size must be under 5MB.');
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

if (!in_array($ext, $allowedExts) && !in_array($file['type'], $allowedTypes)) {
    sendError('Please upload a valid JPG, PNG, or WEBP image.');
}

// Save screenshot file
$uploadDir = __DIR__ . '/../uploads/screenshots/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$newFileName = 'pay_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$targetPath  = $uploadDir . $newFileName;
$dbScreenshotPath = 'uploads/screenshots/' . $newFileName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    sendError('Failed to save payment screenshot. Please try again.');
}

// 4. Database Connection & Seat Allocation Logic
$pdo = getDBConnection();

// Fallback if PDO connection failed (allows demo/local JSON return if DB server offline)
$existingCount = 0;
if ($pdo) {
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM `registrations`");
    $existingCount = (int)$stmtCount->fetchColumn();
}

if ($existingCount >= 250) {
    sendError('Registration capacity reached (maximum 250 attendees).');
}

$attendance_type = 'SEATED';
$seat_number     = null;

if ($existingCount < 100) {
    $attendance_type = 'SEATED';
    $seat_number     = sprintf('%02d', $existingCount + 1);
} else {
    $attendance_type = 'STANDBY';
    $seat_number     = null;
}

$regNumStr = sprintf('%03d', $existingCount + 1);
$registration_id = 'EAF2026-' . $regNumStr;

// 5. Save Record into MySQL
if ($pdo) {
    try {
        $stmtInsert = $pdo->prepare("INSERT INTO `registrations` (
            `registration_id`, `fname`, `mobile`, `email`, `company_name`, `company_type`, `gst_no`,
            `event_facility`, `address`, `upi_id`, `screenshot_path`,
            `registration_fee`, `facility_fee`, `total_amount`,
            `attendance_type`, `seat_number`, `payment_status`, `created_at`
        ) VALUES (
            :reg_id, :fname, :mobile, :email, :company_name, :company_type, :gst_no,
            :event_facility, :address, :upi_id, :screenshot_path,
            :reg_fee, :facility_fee, :total_amount,
            :attendance, :seat_no, 'PENDING', NOW()
        )");

        $stmtInsert->execute([
            ':reg_id'          => $registration_id,
            ':fname'           => $fname,
            ':mobile'          => $mobile,
            ':email'           => $email,
            ':company_name'    => $company_name,
            ':company_type'    => $company_type,
            ':gst_no'          => $gst_no,
            ':event_facility'  => $facility,
            ':address'         => $address,
            ':upi_id'          => $upi_id,
            ':screenshot_path' => $dbScreenshotPath,
            ':reg_fee'         => $registration_fee,
            ':facility_fee'    => $facility_fee,
            ':total_amount'    => $total_amount,
            ':attendance'      => $attendance_type,
            ':seat_no'         => $seat_number
        ]);
    } catch (Exception $e) {
        // If error saving to DB, return clean message
        sendError('Database error. Could not store registration details.', 500);
    }
}

// 6. Send Confirmation Email (Requirement 10)
$mailSubject = "EAF Entrepreneurs Awareness Day 2026 - Registration Confirmation ({$registration_id})";
$mailBody = "Dear {$fname},\n\n"
          . "Thank you for registering for EAF Entrepreneurs Awareness Day 2026.\n\n"
          . "REGISTRATION SUMMARY:\n"
          . "--------------------------------\n"
          . "Registration ID: {$registration_id}\n"
          . "Attendance Type: {$attendance_type}\n"
          . ($seat_number ? "Seat Number: {$seat_number}\n" : "")
          . "Event Facility: {$facility}\n"
          . "Registration Fee: ₹" . number_format($registration_fee, 2) . "\n"
          . "Event Facility Fee: ₹" . number_format($facility_fee, 2) . "\n"
          . "Total Amount: ₹" . number_format($total_amount, 2) . "\n\n"
          . "EVENT DETAILS:\n"
          . "Date: 26 September 2026\n"
          . "Venue: TERIGE BHAVANA, 11 Block, 2nd Stage, Naagarabhavi, Bengaluru\n\n"
          . "We look forward to seeing you at the event.\n\n"
          . "Best regards,\n"
          . "Entrepreneurs Awareness Forum (EAF)";

$mailHeaders = "From: EAF <connect.eaf@gmail.com>\r\n"
             . "Reply-To: connect.eaf@gmail.com\r\n"
             . "X-Mailer: PHP/" . phpversion();

@mail($email, $mailSubject, $mailBody, $mailHeaders);

// 7. Success Response JSON
echo json_encode([
    'success'          => true,
    'registration_id'  => $registration_id,
    'attendance_type'  => $attendance_type,
    'seat_number'      => $seat_number,
    'event_facility'   => $facility,
    'registration_fee' => $registration_fee,
    'facility_fee'     => $facility_fee,
    'total_amount'     => $total_amount,
    'message'          => 'Registration submitted successfully.'
]);
