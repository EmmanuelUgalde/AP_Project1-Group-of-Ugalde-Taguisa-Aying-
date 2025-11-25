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

$staffCount = $conn->query("SELECT COUNT(*) AS total FROM STAFF")->fetch_assoc()['total'];
$patientCount = $conn->query("SELECT COUNT(*) AS total FROM PATIENT")->fetch_assoc()['total'];
$specializationCount = $conn->query("SELECT COUNT(*) AS total FROM SPECIALIZATION")->fetch_assoc()['total'];
$doctorCount = $conn->query("SELECT COUNT(*) AS total FROM DOCTOR")->fetch_assoc()['total'];
$scheduleCount = $conn->query("SELECT COUNT(*) AS total FROM SCHEDULE")->fetch_assoc()['total'];
$serviceCount = $conn->query("SELECT COUNT(*) AS total FROM SERVICE")->fetch_assoc()['total'];
$appointmentCount = $conn->query("SELECT COUNT(*) AS total FROM APPOINTMENT")->fetch_assoc()['total'];
$medicalCount = $conn->query("SELECT COUNT(*) AS total FROM MEDICAL")->fetch_assoc()['total'];
$userCount = $conn->query("SELECT COUNT(*) AS total FROM USER")->fetch_assoc()['total'];
$paymentCount = $conn->query("SELECT COUNT(*) AS total FROM PAYMENT")->fetch_assoc()['total'];
$pay_methCount = $conn->query("SELECT COUNT(*) AS total FROM PAYMENT_METHOD")->fetch_assoc()['total'];
$pay_statCount = $conn->query("SELECT COUNT(*) AS total FROM PAYMENT_STATUS")->fetch_assoc()['total'];
$statusCount = $conn->query("SELECT COUNT(*) AS total FROM STATUS")->fetch_assoc()['total'];

$monthlyPatients = array_fill(0, 12, 0);
$patResult = $conn->query("SELECT MONTH(PAT_CREATED_AT) AS month, COUNT(*) AS total
        FROM PATIENT WHERE YEAR(PAT_CREATED_AT) = YEAR(CURDATE())
        GROUP BY MONTH(PAT_CREATED_AT)");
if($patResult) {
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

        .graph-section {
            display: flex;
            justify-content: space-around;
            align-items: center;
            flex-wrap: wrap;
            margin: 50px auto;
            width: 90%;
            max-width: 5000px;
            gap: 50px;
        }

        .graph-description {
            flex: 1;
            min-width: 300px;
            max-width: 450px;
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            margin-bottom: 5px;
        }

        .graph-description h2 {
            font-size: 50px;
            margin-bottom: 50px;
        }

        .graph-description p {
            font-size: 25px;
            line-height: 1.5;
            color: #555;
        }

        .graph-container {
            flex: 1;
            min-width: 400px;
            max-width: 800px;
            text-align: center;
            margin-left: 50px;
        }

        .graph-separator {
            border: none;        
            height: 30px;       
            background-color: #007bff; 
            margin: 50px auto;     
            width: 90%;             
            border-radius: 5px;  
        }

        .graph-box {
    background-color: #f4f4f9;
    border: 3px solid #ffffff;
    border-radius: 10px;
    box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
    padding: 40px;
    margin: 40px auto;
    width: 90%;
    max-width: 1500px;
    transition: 0.3s ease;
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
    <h2 class="sub-headline">Dashboard Overview</h2>

        <div class="user-stats-container">
            <div class="user-stat-box">
                <a href="../central_data/public/staff.php">
                    <div class="box-label">Total Staff</div>
                    <div class="box-number"><?php echo $staffCount; ?></div>
                </a>
            </div>
            <div class="user-stat-box">
                <a href="../central_data/public/patient.php">
                    <div class="box-label">Total Patients</div>
                    <div class="box-number"><?php echo $patientCount; ?></div>
                </a>
            </div>
            <div class="user-stat-box">
                <a href="../central_data/public/specialization.php">
                    <div class="box-label">Total Specializations</div>
                    <div class="box-number"><?php echo $specializationCount; ?></div>
                </a>
            </div>
            <div class="user-stat-box">
                <a href="../central_data/public/doctor.php">
                    <div class="box-label">Total Doctors</div>
                    <div class="box-number"><?php echo $doctorCount; ?></div>
                </a>
            </div>
            <div class="user-stat-box">
                <a href="../central_data/public/schedule.php">
                    <div class="box-label">Total Schedules</div>
                    <div class="box-number"><?php echo $scheduleCount; ?></div>
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
                <a href="../central_data/public/appointment.php">
                    <div class="box-label">Total Appointments</div>
                    <div class="box-number"><?php echo $appointmentCount; ?></div>
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
                <a href="../central_data/public/payment_status.php">
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

        <div class="graph-box">
    <div class="graph-section">
        <div class="graph-description">
            <h2>Hospital Data Overview</h2>
            <p>
                This chart shows the current hospital data, including the total number of staff, patients, 
                doctors, and other key areas. It helps track performance and manage resources effectively.
            </p>
        </div>
        <div class="graph-container">
            <canvas id="statsChart" width="500" height="300"></canvas>
        </div>
    </div>
</div>

<hr class="graph-separator">

<div class="graph-box">
    <div class="graph-section">
        <div class="graph-description">
            <h2>Monthly Patient Growth</h2>
            <p>
                This chart shows the monthly growth of patients. It helps track trends and plan hospital resources.
            </p>
        </div>
        <div class="graph-container">
            <canvas id="monthlyChart" width="500" height="300"></canvas>
        </div>
    </div>
</div>

<hr class="graph-separator">

<div class="graph-box">
    <div class="graph-section">
        <div class="graph-description">
            <h2>Monthly Appointments</h2>
            <p>
                This chart shows the total number of appointments per month. It helps track hospital workload and scheduling trends.
            </p>
        </div>
        <div class="graph-container">
            <canvas id="appointmentsChart" width="500" height="300"></canvas>
        </div>
    </div>
</div>


    <footer>
        <h2>&copy; 2025 Urban Medical Hospital by Hollys Group. All Rights Reserved.</h2>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const staffCount = <?php echo $staffCount; ?>;
    const patientCount = <?php echo $patientCount; ?>;
    const specializationCount = <?php echo $specializationCount; ?>;
    const doctorCount = <?php echo $doctorCount; ?>;
    const scheduleCount = <?php echo $scheduleCount; ?>;
    const statusCount = <?php echo $statusCount; ?>;
    const serviceCount = <?php echo $serviceCount; ?>;
    const appointmentCount = <?php echo $appointmentCount; ?>;
    const medicalCount = <?php echo $medicalCount; ?>;
    const payMethCount = <?php echo $pay_methCount; ?>;
    const payStatCount = <?php echo $pay_statCount; ?>;
    const paymentCount = <?php echo $paymentCount; ?>;
    const userCount = <?php echo $userCount; ?>;

    const ctx = document.getElementById('statsChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                'Staff',
                'Patients',
                'Specializations',
                'Doctors',
                'Schedules',
                'Status',
                'Services',
                'Appointments',
                'Medical Records',
                'Payment Methods',
                'Payment Status',
                'Payments',
                'Users'
            ],
            datasets: [{
                label: 'Total Count',
                data: [
                    staffCount,
                    patientCount,
                    specializationCount,
                    doctorCount,
                    scheduleCount,
                    statusCount,
                    serviceCount,
                    appointmentCount,
                    medicalCount,
                    payMethCount,
                    payStatCount,
                    paymentCount,
                    userCount
                ],
                backgroundColor: '#007bff',
                borderColor: '#0056b3',
                borderWidth: 1,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

        const monthlyCtx = document.getElementById('monthlyChart');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sept','Oct','Nov','Dec'],
                datasets: [{
                    label: 'Number of Patients',
                    data: <?= json_encode($monthlyPatients) ?>,
                    backgroundColor: '#28a745'
                }]
            },
            options: { responsive: true }
        });

        const appointmentsCtx = document.getElementById('appointmentsChart');
        new Chart(appointmentsCtx, {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sept','Oct','Nov','Dec'],
                datasets: [{
                    label: 'Appointments',
                    data: [15,20,18,25,22,30,0,0,0,0,0,0],
                    backgroundColor: '#ffc107',
                    borderColor: '#ffc107',
                    fill: false,
                    tension: 0.3
                }]
            },
            options: { responsive: true }
        });
    </script>
</body>
</html>
