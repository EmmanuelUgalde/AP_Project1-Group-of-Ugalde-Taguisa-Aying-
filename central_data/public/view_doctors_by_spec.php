<?php
session_start();
require_once __DIR__ . "/../config/Database.php";

$db = new Database();
$conn = $db->getConn();

$spec_id = $_GET['spec_id'] ?? 0;

$stmt = $conn->prepare("SELECT SPEC_NAME FROM SPECIALIZATION WHERE SPEC_ID = ?");
$stmt->bind_param("i", $spec_id);
$stmt->execute();
$spec = $stmt->get_result()->fetch_assoc();

if (!$spec) {
    die("Specialization not found");
}

$q = $conn->prepare("
    SELECT DOC_ID, DOC_FIRST_NAME, DOC_MIDDLE_INIT, DOC_LAST_NAME
    FROM DOCTOR
    WHERE SPEC_ID = ?
    ORDER BY DOC_LAST_NAME ASC
");
$q->bind_param("i", $spec_id);
$q->execute();
$doctors = $q->get_result();
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

<a href="specialization.php" class="btn-back">← Back to Specialization</a>
<h1>Doctors Specializing in <?= htmlspecialchars($spec['SPEC_NAME']) ?></h1>

<table>
<tr>
    <th>Doctor ID</th>
    <th>Name</th>
</tr>

<?php if ($doctors->num_rows): ?>
    <?php while ($d = $doctors->fetch_assoc()): ?>
    <tr>
        <td><?= $d['DOC_ID'] ?></td>
        <td><?= $d['DOC_FIRST_NAME'] . " " . $d['DOC_MIDDLE_INIT'] . " " . $d['DOC_LAST_NAME'] ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="2">No doctors found under this specialization.</td></tr>
<?php endif; ?>

</table>

</body>
</html>
