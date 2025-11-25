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

$patientCount        = $conn->query("SELECT COUNT(*) AS total FROM PATIENT")->fetch_assoc()['total'];
$staffCount          = $conn->query("SELECT COUNT(*) AS total FROM STAFF")->fetch_assoc()['total'];
$specializationCount = $conn->query("SELECT COUNT(*) AS total FROM SPECIALIZATION")->fetch_assoc()['total'];
$serviceCount        = $conn->query("SELECT COUNT(*) AS total FROM SERVICE")->fetch_assoc()['total'];
$medicalCount        = $conn->query("SELECT COUNT(*) AS total FROM MEDICAL")->fetch_assoc()['total'];
$paymentCount        = $conn->query("SELECT COUNT(*) AS total FROM PAYMENT")->fetch_assoc()['total'];
$pay_methCount       = $conn->query("SELECT COUNT(*) AS total FROM PAYMENT_METHOD")->fetch_assoc()['total'];
$pay_statCount       = $conn->query("SELECT COUNT(*) AS total FROM PAYMENT_STATUS")->fetch_assoc()['total'];
$statusCount         = $conn->query("SELECT COUNT(*) AS total FROM STATUS")->fetch_assoc()['total'];
$userCount         = $conn->query("SELECT COUNT(*) AS total FROM USER")->fetch_assoc()['total'];

$monthlyPatients = array_fill(0, 12, 0);
$patResult = $conn->query("SELECT MONTH(PAT_CREATED_AT) AS month, COUNT(*) AS total
        FROM PATIENT 
        WHERE YEAR(PAT_CREATED_AT) = YEAR(CURDATE())
        GROUP BY MONTH(PAT_CREATED_AT)");
if ($patResult) {
    while ($row = $patResult->fetch_assoc()) {
        $monthlyPatients[$row['month'] - 1] = (int)$row['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urban Medical Hospital</title>
    <link href="https://fonts.googleapis.com/css2?family=Port+Lligat+Slab&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&display=swap" rel="stylesheet">
    <style>

    :root {
            --brand-serif: 'Port Lligat Slab', Georgia, 'Times New Roman', serif;
        }

        body {
    background-color: #e9eef5ff;
    margin: 0;
    padding: 0;
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

        .box-label {
            font-family: var(--brand-serif);
            font-size: 35px;
            color: #666;
            margin-bottom: 5px;
            margin-left: 30px;
            margin-top: -30px;
            text-decoration: none;
        }

        .box-number {
            font-family: var(--brand-serif);
            font-size: 40px; 
            font-weight: bold;
            margin-left: 30px;
            margin-top: 15px;
            color: #007bff;
        }

        .user-stat-box a {
            text-decoration: none;
            color: inherit;
            display: block;
            width: 100%;
            height: 100%;
            padding: 60px 0;
        }

        .user-stat-box:hover {
            background-color: #e8efff;
            transform: scale(1.03);
            transition: 0.3s ease;
            cursor: pointer;
        }

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

    .head {
    font-size: 80px;
    text-align: center;
    font-family: "Raleway", sans-serif;
    margin-top: 50px;
    font-weight: 100;
    margin-bottom: 10px;
}

    .sub-headline {
    font-family: "Raleway", sans-serif;
    color: #007bff !important;
    font-size: 50px;
    text-align: center;
}

.head, .sub-headline {
    position: static;
    z-index: auto;
}

.page-container {
    padding-top: 170px;
}

    </style>
</head>
<body>

<?php include "../central_data/includes/header.php"; ?>

    <div class="page-container">

        <h1 class="head">WELCOME <?php 
        if (isset($_SESSION['username'])) {
          echo htmlspecialchars($username) . "!";
        } else {
          echo "Guest";
        }
      ?></h1>
    <h2 class="sub-headline">Staff Dashboard</h2>

        <div class="user-stats-container">
            <div class="user-stat-box">
                <a href="../central_data/public/staff.php">
                    <div class="box-label">Total Staff</div>
                    <div class="box-number"><?php echo $staffCount; ?></div>
                </a>
            </div>
            <div class="user-stat-box">
                <a href="../central_data/public/specialization.php">
                    <div class="box-label">Total Specializations</div>
                    <div class="box-number"><?php echo $specializationCount; ?></div>
                </a>
            </div>
            <div class="user-stat-box">
                <a href="../central_data/public/status.php">
                    <div class="box-label">Total Status</div>
                    <div class="box-number"><?php echo $statusCount; ?></div>
                </a>
            </div>
            <div class="user-stat-box">
                <a href="../central_data/public/service.php">
                    <div class="box-label">Total Services</div>
                    <div class="box-number"><?php echo $serviceCount; ?></div>
                </a>
            </div>
            <div class="user-stat-box">
                <a href="../central_data/public/medical_record.php">
                    <div class="box-label">Total Medical Records</div>
                    <div class="box-number"><?php echo $medicalCount; ?></div>
                </a>
            </div>
            <div class="user-stat-box">
                <a href="../central_data/public/payment_method.php">
                    <div class="box-label">Total Payment Methods</div>
                    <div class="box-number"><?php echo $pay_methCount; ?></div>
                </a>
            </div>
            <div class="user-stat-box">
                <a href="users.php../central_data/public/payment_status.php">
                    <div class="box-label">Total Payment Status</div>
                    <div class="box-number"><?php echo $pay_statCount; ?></div>
                </a>
            </div>
            <div class="user-stat-box">
                <a href="../central_data/public/payment.php">
                    <div class="box-label">Total Payments</div>
                    <div class="box-number"><?php echo $paymentCount; ?></div>
                </a>
            </div>
            <div class="user-stat-box">
                <a href="../central_data/public/users.php">
                    <div class="box-label">Total Users</div>
                    <div class="box-number"><?php echo $userCount; ?></div>
                </a>
            </div>
        </div>

    <footer style="margin-top: 50px;">
        <h2>&copy; 2025 Urban Medical Hospital by Hollys Group. All Rights Reserved.</h2>
    </footer>
</body>
</html>