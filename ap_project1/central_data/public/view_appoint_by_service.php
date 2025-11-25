<?php
session_start();
include __DIR__ . "/../config/Database.php";

if (!isset($_SESSION['role'])) {
    echo "<div style='text-align:center;margin-top:200px;color:red;'>
            <h1>Access Denied</h1><p>You must be logged in.</p></div>";
    exit;
}

$db = new Database();
$conn = $db->getConn();

$service_id = $_GET['service_id'] ?? 0;

$stmt = $conn->prepare("SELECT SERV_NAME FROM SERVICE WHERE SERV_ID = ?");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();

if (!$service) {
    echo "<div style='text-align:center;margin-top:200px;color:red;'><h1>Service not found</h1></div>";
    exit;
}

$q = $conn->prepare("
    SELECT a.APPT_ID, a.APPT_DATE, a.APPT_TIME,
           CONCAT(p.PAT_FIRST_NAME, ' ', p.PAT_LAST_NAME) AS PATIENT_NAME,
           CONCAT(d.DOC_FIRST_NAME, ' ', d.DOC_LAST_NAME) AS DOCTOR_NAME,
           st.STATUS_NAME AS APPT_STATUS
    FROM APPOINTMENT a
    INNER JOIN PATIENT p ON a.PAT_ID = p.PAT_ID
    INNER JOIN DOCTOR d ON a.DOC_ID = d.DOC_ID
    INNER JOIN STATUS st ON a.STAT_ID = st.STATUS_ID
    WHERE a.SERV_ID = ?
    ORDER BY a.APPT_DATE DESC
");

$q->bind_param("i", $service_id);
$q->execute();
$appointments = $q->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Appointments for <?= htmlspecialchars($service['SERV_NAME']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Port+Lligat+Slab&display=swap" rel="stylesheet">
<style>
body { font-family:'Port Lligat Slab',serif;background:#FFF9F9;margin:0;padding:40px; }
h1 { text-align:center;color:#333; font-family:'Port Lligat Slab';}
.btn-back {
    background:#007bff;
    color:white;
    padding:12px 25px;
    border:none;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
    font-family:'Port Lligat Slab'
}
table {
    width:100%;
    border-collapse:collapse;
    background:#fff;
    margin-top:20px;
    font-family:'Port Lligat Slab'
}
th,td {
    padding:12px;
    border-bottom:1px solid #ddd;
    text-align:center;
    font-size:18px;
    font-family:'Port Lligat Slab'
}
th { background:#f1f1f1; }
</style>
</head>
<body>

<a href="service.php" class="btn-back">← Back to Services</a>
<h1>Appointments for <?= htmlspecialchars($service['SERV_NAME']) ?></h1>

<table>
<tr>
    <th>Appointment ID</th>
    <th>Patient Name</th>
    <th>Doctor</th>
    <th>Date</th>
    <th>Time</th>
    <th>Status</th>
</tr>
<?php if ($appointments->num_rows): while ($a = $appointments->fetch_assoc()): ?>
<tr>
    <td><?= $a['APPT_ID'] ?></td>
    <td><?= htmlspecialchars($a['PATIENT_NAME']) ?></td>
    <td><?= htmlspecialchars($a['DOCTOR_NAME']) ?></td>
    <td><?= $a['APPT_DATE'] ?></td>
    <td><?= $a['APPT_TIME'] ?></td>
    <td><?= htmlspecialchars($a['APPT_STATUS']) ?></td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="6">No appointments found for this service.</td></tr>
<?php endif; ?>
</table>
</body>
</html>
