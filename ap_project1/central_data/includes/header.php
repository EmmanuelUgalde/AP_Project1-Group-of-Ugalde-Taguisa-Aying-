<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Port+Lligat+Slab&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Document</title>
    <style>
        :root {
        --brand-serif: 'Port Lligat Slab', Georgia, 'Times New Roman', serif;
    }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        header {
            width: 100%;
            height: 100px;
            background-color: #215dd4ff;
            color: white;
            padding: 80px 50px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 15px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .logo {
            height: 120px;
            width: auto;
            margin-left: 10px;
        }

        .headline1 {
            font-family: var(--brand-serif);
            font-size: 35px;
            color: #EFBF04;
            font-weight: normal;
            margin: 0;
        }

        .headline2 {
            font-family: var(--brand-serif);
            font-size: 35px;
            color: #EFBF04;
            font-weight: normal;
            justify-content: flex-end;
        }
            
.hamburger {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 30px;
    height: 22px;
    margin-left: -5px;
    cursor: pointer;
}

.hamburger span {
    display: block;
    height: 4px;
    background-color: white;
    border-radius: 2px;
}

 .header-right {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 15px;
    color: white;
}


.header-right .logout {
    font-family: var(--brand-serif);
    padding: 8px 16px;
    font-size: 30px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    color: white;
    text-decoration: none;
    background-color: transparent;
}

.header-right .logout:hover {
    color: #EFBF04;
    transition: 0.3s;
}

.header-right .home {
     font-family: var(--brand-serif);
    padding: 8px 16px;
    font-size: 30px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    color: white;
    text-decoration: none;
    background-color: transparent;
}

.header-right .home:hover {
    color: #EFBF04;
    transition: 0.3s;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 91px;
    left: -50px;
    background-color: #215dd4ff;
    flex-direction: column;
    gap: 10px;
    padding: 20px 20px;
    z-index: 1001;
    min-width: 330px;
    height: 800px;
}

.dropdown-menu a {
    margin-left: 30px;
    font-family: var(--brand-serif);
    display: block;   
    padding: 10px 0;
    font-size: 28px;
    color: white;
    text-decoration: none;
}

.dropdown-menu a:hover {
    color: #EFBF04;
    transition: 0.2s;
}

.hamburger-container {
    position: relative;
}

    </style>
</head>
<body>
    <header>
    <div class="hamburger-container">
        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    <div class="dropdown-menu" id="dropdown-menu">
        <a href="/ap_project1/central_data/public/staff.php">Staff</a>
        <a href="/ap_project1/central_data/public/patient.php">Patient</a>
        <a href="/ap_project1/central_data/public/specialization.php">Specialization</a>
        <a href="/ap_project1/central_data/public/doctor.php">Doctor</a>
        <a href="/ap_project1/central_data/public/schedule.php">Schedule</a>
        <a href="/ap_project1/central_data/public/status.php">Status</a>
        <a href="/ap_project1/central_data/public/service.php">Service</a>
        <a href="/ap_project1/central_data/public/appointment.php">Appointment</a>
        <a href="/ap_project1/central_data/public/medical_record.php">Medical Record</a>
        <a href="/ap_project1/central_data/public/payment_method.php">Payment Method</a>
        <a href="/ap_project1/central_data/public/payment_status.php">Payment Status</a>
        <a href="/ap_project1/central_data/public/payment.php">Payment</a>
        <a href="/ap_project1/central_data/public/users.php">User</a>
    </div>
</div>

<img src="https://res.cloudinary.com/dlsr0ebdd/image/upload/v1764058940/Screenshot1-removebg-preview_itx3n8.png" alt="Logo of Urban Medical" class="logo">
        <h1 class="headline1">Urban Medical Hospital</h1>


       <div class="header-right">
    <?php
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'Superadmin':
            $homeLink = "/ap_project1/super_admin/index.php";
            break;
        case 'Doctor':
            $homeLink = "/ap_project1/doctor/index.php";
            break;
        case 'Staff':
            $homeLink = "/ap_project1/staff/index.php";
            break;
        case 'Patient':
            $homeLink = "/ap_project1/patient/index.php";
            break;
        default:
            $homeLink = "/ap_project1/index.php";
    }
} else {
    $homeLink = "/ap_project1/index.php";
}
?>
<a href="<?php echo $homeLink; ?>" class="home">Home</a>
    <a href="/ap_project1/index.php" class="logout">Log Out</a>
</div>
    </header>

    <script>
const hamburger = document.getElementById('hamburger');
const dropdownMenu = document.getElementById('dropdown-menu');

hamburger.addEventListener('click', () => {
    if (dropdownMenu.style.display === "block") {
        dropdownMenu.style.display = "none";
    } else {
        dropdownMenu.style.display = "block";
    }
});
</script>
</body>
</html>

