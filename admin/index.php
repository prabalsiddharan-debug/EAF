<?php
// admin/index.php - Admin Management Panel for EAF Registrations

require_once __DIR__ . '/../api/db.php';
$pdo = getDBConnection();

$filter_facility = $_GET['facility'] ?? 'All';

$query = "SELECT * FROM `registrations` WHERE 1=1";
$params = [];

if ($filter_facility !== 'All' && !empty($filter_facility)) {
    $query .= " AND `event_facility` = :facility";
    $params[':facility'] = $filter_facility;
}

$query .= " ORDER BY `id` DESC";

$registrations = [];
if ($pdo) {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $registrations = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EAF Admin — Registrations Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --navy:#082b63; --blue:#1769d1; --gold:#d9a441; --light:#f5f8fc; --ink:#172033; --line:#dce4ef; }
        body { font-family: Manrope, sans-serif; margin: 0; background: var(--light); color: var(--ink); line-height: 1.5; }
        header { background: var(--navy); color: #fff; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { font-size: 20px; margin: 0; }
        .container { max-width: 1400px; margin: 30px auto; padding: 0 20px; }
        .card { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 10px 30px rgba(4,26,61,0.06); border: 1px solid var(--line); }
        .filter-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .filter-group { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 14px; }
        select, button, .btn-export { font-family: inherit; font-size: 13px; padding: 9px 14px; border-radius: 8px; border: 1px solid var(--line); outline: none; }
        .btn-export { background: var(--gold); color: #111; text-decoration: none; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; }
        .btn-export:hover { background: #c29235; }
        .table-wrap { overflow-x: auto; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
        th, td { padding: 12px 14px; border-bottom: 1px solid var(--line); white-space: nowrap; }
        th { background: #edf3fa; color: var(--navy); font-weight: 800; letter-spacing: 0.04em; }
        tr:hover { background: #f8fafc; }
        .badge { padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; display: inline-block; }
        .badge-seated { background: #e9f8ef; color: #16834b; }
        .badge-standby { background: #fff4e5; color: #b76e00; }
        .badge-facility { background: #eef5ff; color: var(--blue); }
        .amount-highlight { font-weight: 800; color: var(--navy); }
    </style>
</head>
<body>
    <header>
        <h1>EAF Entrepreneurs Awareness Day 2026 — Admin Panel</h1>
        <span>Total Registrations: <b><?= count($registrations) ?></b></span>
    </header>

    <div class="container">
        <div class="card">
            <div class="filter-bar">
                <form method="GET" class="filter-group">
                    <label for="facility">Filter by Event Facility:</label>
                    <select name="facility" id="facility" onchange="this.form.submit()">
                        <option value="All" <?= $filter_facility === 'All' ? 'selected' : '' ?>>All Facilities</option>
                        <option value="Standee (₹500)" <?= $filter_facility === 'Standee (₹500)' ? 'selected' : '' ?>>Standee (₹500)</option>
                        <option value="Show Table (₹500)" <?= $filter_facility === 'Show Table (₹500)' ? 'selected' : '' ?>>Show Table (₹500)</option>
                        <option value="Standee + Show Table (₹500 + ₹500)" <?= $filter_facility === 'Standee + Show Table (₹500 + ₹500)' ? 'selected' : '' ?>>Standee + Show Table (₹500 + ₹500)</option>
                        <option value="Not Required" <?= $filter_facility === 'Not Required' ? 'selected' : '' ?>>Not Required</option>
                    </select>
                </form>

                <a href="export.php?facility=<?= urlencode($filter_facility) ?>" class="btn-export">
                    Export Excel CSV ↓
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Reg ID</th>
                            <th>Full Name</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Company Type</th>
                            <th>GST No</th>
                            <th>Event Facility</th>
                            <th>Reg Fee</th>
                            <th>Facility Fee</th>
                            <th>Total Amount</th>
                            <th>Attendance</th>
                            <th>Seat No</th>
                            <th>UPI ID</th>
                            <th>Screenshot</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registrations)): ?>
                            <tr>
                                <td colspan="16" style="text-align: center; color: #8ea3bf; padding: 30px;">
                                    No registrations found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registrations as $r): ?>
                                <tr>
                                    <td><b><?= htmlspecialchars($r['registration_id']) ?></b></td>
                                    <td><?= htmlspecialchars($r['fname']) ?></td>
                                    <td><?= htmlspecialchars($r['mobile']) ?></td>
                                    <td><?= htmlspecialchars($r['email']) ?></td>
                                    <td><?= htmlspecialchars($r['company_name']) ?></td>
                                    <td><?= htmlspecialchars($r['company_type']) ?></td>
                                    <td><?= htmlspecialchars($r['gst_no'] ?: 'N/A') ?></td>
                                    <td><span class="badge badge-facility"><?= htmlspecialchars($r['event_facility']) ?></span></td>
                                    <td>₹<?= number_format($r['registration_fee'], 2) ?></td>
                                    <td>₹<?= number_format($r['facility_fee'], 2) ?></td>
                                    <td><span class="amount-highlight">₹<?= number_format($r['total_amount'], 2) ?></span></td>
                                    <td>
                                        <span class="badge <?= $r['attendance_type'] === 'SEATED' ? 'badge-seated' : 'badge-standby' ?>">
                                            <?= htmlspecialchars($r['attendance_type']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($r['seat_number'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($r['upi_id']) ?></td>
                                    <td>
                                        <?php if ($r['screenshot_path']): ?>
                                            <a href="../<?= htmlspecialchars($r['screenshot_path']) ?>" target="_blank">View Proof</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($r['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
