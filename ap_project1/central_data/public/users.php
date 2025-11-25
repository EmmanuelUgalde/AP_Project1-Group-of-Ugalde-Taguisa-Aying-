<?php
session_start();
require_once __DIR__ . "/../../central_data/classes/User.php";

if (!isset($_SESSION['role'])) {
    echo "<div style='text-align:center; margin-top:200px; font-family: Port Lligat Slab, serif;'>
            <h1 style='color:red;'>Access Denied</h1>
            <p>You must be logged in to access this page.</p>
          </div>";
    exit;
}

$userObj = new User();
$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

$error = $success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $userObj->add_user($_POST);
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}

$filterRole = $_GET['role'] ?? 'all';

if ($role === 'Superadmin') {
    switch ($filterRole) {
        case 'doctor':
            $users = $userObj->view_all_doctors($role);
            break;
        case 'staff':
            $users = $userObj->view_all_staff($role);
            break;
        case 'patient':
            $users = $userObj->view_all_patients($role);
            break;
        case 'superadmin':
            $allUsers = $userObj->view_all_users($role);
            $users = array_filter($allUsers, fn($u) => $u['USER_IS_SUPERADMIN']);
            break;
        default:
            $users = $userObj->view_all_users($role);
    }
} else {
    switch ($role) {
        case 'Doctor':
            $users = $userObj->view_all_doctors('Superadmin');
            break;
        case 'Staff':
            $users = $userObj->view_all_staff('Superadmin');
            break;
        case 'Patient':
            $users = $userObj->view_all_patients('Superadmin');
            break;
        default:
            $users = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users - Urban Medical Hospital</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

<style>
body {
    margin: 0;
    font-family: 'Port Lligat Slab', serif;
    background-color: #FFF9F9;
}
.container-page {
    margin-top: 140px;
    padding: 70px;
}
.container-page h1, .container-page h2 {
    font-size: 80px;
    color: black;
    margin-top: 120px;
    text-align: left;
}
.action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
}
.search-bar input {
    padding: 15px 12px;
    width: 250px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
.btn-primary {
    display: inline-block;
    margin-top: 20px;
    padding: 15px 25px;
    background-color: #ADD8E6;
    color: #006994;
    text-decoration: none;
    border-radius: 10px;
    font-size: 30px;
    font-family: 'Port Lligat Slab', serif;
    font-weight: bold;
}
.btn {
    padding: 8px 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}
.btn-add { background-color: #28a745; color: white; width: 150px; height: 60px; font-size: 20px; }
.btn-update { background-color: #007bff; color: white; width: 150px; height: 60px; font-size: 20px; }
.btn-delete { background-color: #dc3545; color: white; width: 150px; height: 50px; font-size: 20px; }
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
    background: white;
}
th, td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    font-size: 20px;
}
th {
    background-color: #f1f1f1;
}
.message {
    margin: 10px 0;
    color: green;
    font-weight: bold;
}

body, html {
    box-sizing: border-box;
}
.container-wrapper {
    display: flex;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 900px;
    overflow: hidden;
    margin-top: 40px;
    margin-bottom: 20px;
}
.sidebar {
    width: 250px;
    background-color: #f8f8f8;
    padding: 20px 0;
    border-right: 1px solid #eee;
    flex-shrink: 0;
}
.profile-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 0 20px;
}
.profile-pic {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 10px;
    border: 2px solid #ddd;
}
.sidebar nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.sidebar nav li {
    margin-bottom: 5px;
}
.sidebar nav a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    text-decoration: none;
    color: #555;
    font-weight: 500;
    transition: background-color 0.2s, color 0.2s;
}
.sidebar nav a i { margin-right: 10px; font-size: 18px; }
.sidebar nav a:hover { background-color: #e9e9e9; color: #333; }
.sidebar nav a.active { background-color: #007bff; color: #fff; border-left: 4px solid #0056b3; }
.sidebar nav a.active:hover { background-color: #0069d9; color: #fff; }
.content {
    flex-grow: 1;
    padding: 30px;
}
h1, h2 { color: #333; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-weight: 600; margin-bottom: 8px; color: #444; }
.form-group input[type="text"], .form-group input[type="email"], .form-group input[type="password"] {
    width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box;
}
.button-group { margin-top: 30px; text-align: right; }
.button-group button { background-color: #007bff; color: #fff; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.2s; }
.button-group button:hover { background-color: #0056b3; }
.graph-placeholder { width: 100%; height: 250px; background-color: #e0f2f7; border: 1px solid #a7d9ee; border-radius: 8px; display: flex; justify-content: center; align-items: center; font-size: 20px; color: #007bff; margin: 20px 0; font-weight: bold; }
.footer { background-color: #0056b3; color: white; padding: 15px 0; text-align: center; width: 100%; margin-top: auto; }

@media(max-width: 1000px){
    .container-wrapper { flex-direction: column; max-width: 100%; }
    .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #eee; }
}
</style>
</head>
<body>

<?php include "../includes/header.php"; ?>

<div class="container-page">
    <h2 style="font-size: 80px; margin-top: -10px;">User Management</h2>

    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="card" style="margin-top: 15px; background-color: #006994; border-radius: 10px; text-align: center; padding: 30px;">
        <h2 style="color: white; font-size: 35px; text-align: center; margin-top: 5px;">Want to create a new user? Press the button below!</h2>
        <a href="../../create.php" class="btn-primary">Create Account</a>
    </div>

    <?php if ($role === 'Superadmin'): ?>
    <div class="card" style="margin-top: 30px;">
        <form method="GET">
            <label style="font-family:'Port Lligat Slab', serif; font-size: 35px;">Filter Users by Role</label>
            <select name="role" onchange="this.form.submit()" style="margin-left: 15px;width: 250px; height: 40px; font-size: 20px; border-radius: 8px;">
                <option value="all" <?= $filterRole==='all'?'selected':'' ?>>All</option>
                <option value="superadmin" <?= $filterRole==='superadmin'?'selected':'' ?>>Superadmin</option>
                <option value="doctor" <?= $filterRole==='doctor'?'selected':'' ?>>Doctor</option>
                <option value="staff" <?= $filterRole==='staff'?'selected':'' ?>>Staff</option>
                <option value="patient" <?= $filterRole==='patient'?'selected':'' ?>>Patient</option>
            </select>
        </form>
    </div>
    <?php endif; ?>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['USER_ID']) ?></td>
                        <td><?= htmlspecialchars($row['USER_NAME']) ?></td>
                        <td><?= htmlspecialchars($row['ROLE']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="container-wrapper">
        <div class="sidebar">

            <nav>
                <ul>
                    <li><a href="#" class="tab-link" data-tab="account-details"><i class="fas fa-user"></i> Account Details</a></li>
                    <li><a href="#" class="tab-link" data-tab="change-password"><i class="fas fa-lock"></i> Change Password</a></li>
                    <li><a href="../../index.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
        <div class="content">
            <h1 style="font-size: 70px; text-align: center; margin-top: 5px;">Account Settings</h1>

            <div id="account-details" class="tab-content active">
                <h2 style="font-size: 50px; text-align: center; margin-top: 30px; margin-bottom: 15px;">Account Details</h2>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" placeholder="Enter new email address">
                </div>
                <div class="form-group">
                    <label for="first-name">First name</label>
                    <input type="text" id="first-name" placeholder="Enter new first name">
                </div>
                <div class="form-group">
                    <label for="last-name">Last name</label>
                    <input type="text" id="last-name" placeholder="Enter new last name">
                </div>
                <div class="form-group">
                    <label for="facebook">Facebook</label>
                    <input type="text" id="facebook" placeholder="Enter Facebook account (optional)">
                </div>
                <div class="form-group">
                    <label for="twitter">Twitter</label>
                    <input type="text" id="twitter" placeholder="Enter Twitter/X account (optional)">
                </div>
                <div class="button-group">
                    <button style="font-size: 20px;height: 60px; width: 180px; font-family:Arial, Helvetica, sans-serif;">Save Changes</button>
                </div>
            </div>

            <div id="change-password" class="tab-content">
                <h2 style="font-size: 50px; text-align: center; margin-top: 30px; margin-bottom: 15px;">Change Password</h2>
                <div class="form-group">
                    <label for="current-password">Current Password</label>
                    <input type="password" id="current-password" value="">
                </div>
                <div class="form-group">
                    <label for="new-password">New Password</label>
                    <input type="password" id="new-password" value="">
                </div>
                <div class="form-group">
                    <label for="confirm-password">Confirm New Password</label>
                    <input type="password" id="confirm-password" value="">
                </div>
                <div class="button-group">
                    <button style="font-size: 20px;height: 60px; width: 200px; font-family:Arial, Helvetica, sans-serif;">Update Password</button>
                </div>
            </div>
        </div>
    </div>

</div>

<footer class="footer">
    <h2 style="color: white;">&copy; 2025 Urban Medical Hospital by Hollys Group. All Rights Reserved.</h2>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');

    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            tabLinks.forEach(l => l.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            this.classList.add('active');

            const targetTab = this.getAttribute('data-tab');
            document.getElementById(targetTab).classList.add('active');
        });
    });

    document.querySelector('.tab-link[data-tab="account-details"]').click();
});
</script>

</body>
</html>
