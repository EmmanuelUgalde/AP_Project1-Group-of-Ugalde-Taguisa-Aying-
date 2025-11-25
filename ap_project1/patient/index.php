<?php
session_start();
include "../central_data/config/Database.php";

$db = new Database();
$conn = $db->getConn();

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];
$userId = $_SESSION['user_id'] ?? null;

$fullname = $username;
if ($userId) {
    $stmt = $conn->prepare("
        SELECT CONCAT(P.PAT_FIRST_NAME, ' ', P.PAT_LAST_NAME) AS fullname
        FROM USER U
        LEFT JOIN PATIENT P ON U.PAT_ID = P.PAT_ID
        WHERE U.USER_ID = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $fullname = $row['fullname'] ?: $username;
    }
    $stmt->close();
}

$staffCount = $conn->query("SELECT COUNT(*) AS total FROM STAFF")->fetch_assoc()['total'];
$specializationCount = $conn->query("SELECT COUNT(*) AS total FROM SPECIALIZATION")->fetch_assoc()['total'];
$serviceCount = $conn->query("SELECT COUNT(*) AS total FROM SERVICE")->fetch_assoc()['total'];
$medicalCount = $conn->query("SELECT COUNT(*) AS total FROM MEDICAL")->fetch_assoc()['total'];
$userCount = $conn->query("SELECT COUNT(*) AS total FROM USER")->fetch_assoc()['total'];
$paymentCount = $conn->query("SELECT COUNT(*) AS total FROM PAYMENT")->fetch_assoc()['total'];
$pay_methCount = $conn->query("SELECT COUNT(*) AS total FROM PAYMENT_METHOD")->fetch_assoc()['total'];
$pay_statCount = $conn->query("SELECT COUNT(*) AS total FROM PAYMENT_STATUS")->fetch_assoc()['total'];
$statusCount = $conn->query("SELECT COUNT(*) AS total FROM STATUS")->fetch_assoc()['total'];
$patientCount = $conn->query("SELECT COUNT(*) AS total FROM PATIENT")->fetch_assoc()['total'];
$appointmentCount = $conn->query("SELECT COUNT(*) AS total FROM APPOINTMENT")->fetch_assoc()['total'];

$monthlyPatients = array_fill(0, 12, 0);
$patResult = $conn->query("
    SELECT MONTH(PAT_CREATED_AT) AS month, COUNT(*) AS total
    FROM PATIENT
    WHERE YEAR(PAT_CREATED_AT) = YEAR(CURDATE())
    GROUP BY MONTH(PAT_CREATED_AT)
");
if ($patResult) {
    while ($row = $patResult->fetch_assoc()) {
        $monthlyPatients[$row['month'] - 1] = (int)$row['total'];
    }
$appointments = [];
if ($userId) {
    $stmt = $conn->prepare("
        SELECT A.APPT_ID, A.APPT_DATE, D.DOC_FIRST_NAME AS doctor_first, 
               D.DOC_LAST_NAME AS doctor_last, SE.SERV_NAME AS service_name,
               ST.STATUS_NAME AS status
        FROM APPOINTMENT A
        LEFT JOIN DOCTOR D ON A.DOC_ID = D.DOC_ID
        LEFT JOIN SERVICE SE ON A.SERV_ID = SE.SERV_ID
        LEFT JOIN STATUS ST ON A.STAT_ID = ST.STATUS_ID
        WHERE A.PAT_ID = (SELECT PAT_ID FROM USER WHERE USER_ID = ?)
        ORDER BY A.APPT_DATE DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}  
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Urban Medical Hospital</title>
    <link href="https://fonts.googleapis.com/css2?family=Port+Lligat+Slab&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --brand-serif: 'Port Lligat Slab', Georgia, 'Times New Roman', serif; }
        body { background-color: #e9eef5ff; margin: 0; padding: 0; }

        .page-container { padding-top: 150px; }

        .head {
            font-size: 80px;
            text-align: center;
            font-family: "Raleway", sans-serif;
            font-weight: 100;
            margin-top: 50px;
            margin-bottom: 10px;
        }

        .sub-headline {
            font-family: "Raleway", sans-serif;
            color: #007bff !important;
            font-size: 50px;
            text-align: center;
        }

        .user-stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            justify-content: center;
            max-width: 1760px;
            margin: 30px auto 0;
        }

        .user-stat-box {
            width: 400px;
            height: 200px; 
            background-color: #f4f4f9; 
            border: 3px solid #ffffffff;
            border-radius: 8px; 
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1); 
            display: flex;
            flex-direction: column; 
            justify-content: center; 
            align-items: left; 
            text-align: left;
        }

        .user-stat-box:hover {
            background-color: #e8efff;
            transform: scale(1.03);
            transition: 0.3s ease;
            cursor: pointer;
        }

        .user-stat-box a {
            display: block;  
            text-decoration: none !important;
            color: inherit; 
        }

        .box-label{
            font-family: var(--brand-serif);
            font-size: 35px;
            color: #666;
            margin-bottom: 5px;
            margin-left: 30px;
            margin-top: -30px;
        }

        .box-number {
            font-family: var(--brand-serif);
            font-size: 40px;
            font-weight: bold;
            margin-left: 30px;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
        }

        .appointments-section {
            margin: 80px auto;
            width: 90%;
            max-width: 1500px;
            background-color: #f4f4f9;
            border: 3px solid #ffffff;
            border-radius: 10px;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        .appointments-section h2 {
            font-family: var(--brand-serif);
            font-size: 50px;
            margin-bottom: 20px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 15px 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            font-size: 18px;
            color: #333;
        }
        th {
            background-color: #007bff;
            color: white;
        }

        .status {
            padding: 6px 10px;
            border-radius: 5px;
            font-weight: 600;
        }
        .status.pending { background: #fff4e5; color: #b86b00; }
        .status.confirmed { background: #e8fff0; color: #0a7b3a; }
        .status.canceled { background: #ffecec; color: #b00000; }

        footer {
            width: 100%;
            background-color: #0041c7;
            color: white;
            padding: 30px 110px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        footer h2 {
            font-family: var(--brand-serif);
            font-weight: 400;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

<?php include "../central_data/includes/header.php"; ?>

<div class="page-container">
    <h1 class="head">WELCOME <?php echo htmlspecialchars($username); ?>!</h1>
    <h2 class="sub-headline">Patient Dashboard</h2>

    <div class="user-stats-container">
            <div class="user-stat-box">
                <a href="../central_data/public/patient.php">
                    <div class="box-label">Total Patients</div>
                    <div class="box-number"><?php echo $patientCount; ?></div>
                </a>
            </div>
            <div class="user-stat-box">
                <a href="../central_data/public/appointment.php">
                    <div class="box-label">Total Appointments</div>
                    <div class="box-number"><?php echo $appointmentCount; ?></div>
                </a>
            </div>
            <div class="user-stat-box">
                <a href="../central_data/public/users.php">
                    <div class="box-label">View Users</div>
                    <div class="box-number"><?php echo $userCount; ?></div>
                </a>
            </div>
        </div>

    <div class="appointments-section">
        <h2>Your Appointment History</h2>

        <?php if (count($appointments) === 0): ?>
            <p style="font-size:25px; color:#666;">You currently have no appointments booked.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Doctor</th>
                    <th>Service</th>
                    <th>Time Slot</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $i => $row): 
                    $status = strtolower($row['status']);
                    $statusClass = in_array($status, ['confirmed','canceled']) ? $status : 'pending';
                ?>
                <tr>
                    <td><?php echo $i+1; ?></td>
                    <td><?php echo date('M d, Y h:i A', strtotime($row['appointment_date'])); ?></td>
                    <td><?php echo htmlspecialchars($row['doctor_first'] . ' ' . $row['doctor_last']); ?></td>
                    <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['start_time'] . ' - ' . $row['end_time']); ?></td>
                    <td><span class="status <?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <footer>
        <h2>&copy; 2025 Urban Medical Hospital by Hollys Group. All Rights Reserved.</h2>
    </footer>
</div>
</body>
</html>