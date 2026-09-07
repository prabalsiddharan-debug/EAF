<?php
// admin/export.php - Excel CSV Exporter with Event Facility & Pricing columns

require_once __DIR__ . '/../api/db.php';
$pdo = getDBConnection();

$filter_facility = $_GET['facility'] ?? 'All';

$query = "SELECT * FROM `registrations` WHERE 1=1";
$params = [];

if ($filter_facility !== 'All' && !empty($filter_facility)) {
    $query .= " AND `event_facility` = :facility";
    $params[':facility'] = $filter_facility;
}

$query .= " ORDER BY `id` ASC";

$registrations = [];
if ($pdo) {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $registrations = $stmt->fetchAll();
}

$filename = 'EAF_Registrations_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fputs($output, "\xEF\xBB\xBF");

// CSV Header Row
fputcsv($output, [
    'Registration ID',
    'FName',
    'Mobile',
    'Email',
    'Company',
    'Company Type',
    'GST',
    'Event Facility',
    'Address',
    'UPI ID',
    'Registration Fee',
    'Facility Fee',
    'Total Amount',
    'Payment Status',
    'Attendance Type',
    'Seat Number',
    'Created At'
]);

foreach ($registrations as $row) {
    fputcsv($output, [
        $row['registration_id'],
        $row['fname'],
        $row['mobile'],
        $row['email'],
        $row['company_name'],
        $row['company_type'],
        $row['gst_no'] ?: 'N/A',
        $row['event_facility'],
        $row['address'],
        $row['upi_id'],
        number_format($row['registration_fee'], 2, '.', ''),
        number_format($row['facility_fee'], 2, '.', ''),
        number_format($row['total_amount'], 2, '.', ''),
        $row['payment_status'],
        $row['attendance_type'],
        $row['seat_number'] ?: 'N/A',
        $row['created_at']
    ]);
}

fclose($output);
exit;
