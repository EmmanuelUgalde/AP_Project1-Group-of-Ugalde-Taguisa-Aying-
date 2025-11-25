<?php
session_start();
require_once __DIR__ . "/central_data/config/Database.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm'] ?? '';
    $email     = trim($_POST['email'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $role      = $_POST['role'] ?? '';

    if (!$username || !$password || !$confirm || !$email || !$firstname || !$lastname || !$role) {
        $error = "All fields are required!";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match!";
    } else {
        $db = new Database();
        $conn = $db->getConn();

        $stmt = $conn->prepare("SELECT USER_ID FROM USER WHERE USER_NAME = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $pat_id = $staff_id = $doc_id = NULL;
            $isSuperadmin = 0;

            switch ($role) {
                case "Superadmin":
                    $isSuperadmin = 1;
                    break;
                case "Patient":
                    $pat_id = 1;
                    break;
                case "Staff":
                    $staff_id = 1;
                    break;
                case "Doctor":
                    $doc_id = 1;
                    break;
                default:
                    $error = "Invalid role!";
            }

            if (empty($error)) {
                $stmt = $conn->prepare("
                    INSERT INTO USER 
                    (USER_NAME, USER_PASSWORD, USER_IS_SUPERADMIN, PAT_ID, STAFF_ID, DOC_ID, USER_CREATED_AT)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->bind_param("ssiiii", $username, $hashedPassword, $isSuperadmin, $pat_id, $staff_id, $doc_id);

                if ($stmt->execute()) {
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;

                    switch ($role) {
                        case "Superadmin":
                            header("Location: /ap_project1/super_admin/index.php");
                            break;
                        case "Doctor":
                            header("Location: /ap_project1/doctor/index.php?id=$doc_id");
                            break;
                        case "Staff":
                            header("Location: /ap_project1/staff/index.php?id=$staff_id");
                            break;
                        case "Patient":
                            header("Location: /ap_project1/patient/index.php?id=$pat_id");
                            break;
                    }
                    exit();
                } else {
                    $error = "Error creating user: " . $stmt->error;
                }

                $stmt->close();
            }
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&display=swap" rel="stylesheet">
    <title>Create Account - Urban Medical Hospital</title>
    <style>
        body {
            font-family: "Raleway", sans-serif;
            background-color: #FFF9F9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            position: relative;
        }

        .logo-section {
            position: absolute;
            top: 30px;
            left: 40px;
            text-align: center;
        }

        .logo-section img {
            height: auto;
            width: 250px;
            margin-left: 30px;
        }

        .main-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 50px;
            margin-left: 150px;
        }

        .form-section {
        width: 700px;
        height: 800px;
        background: linear-gradient(180deg, #2e8bf0, #1a73e8);
        color: white;
        padding: 60px 70px;
        border-radius: 40px;
        box-shadow: 0 20px 35px rgba(0,0,0,0.25);
        box-sizing: border-box;
    }

        .form-section h2 {
            text-align: center;
            margin-bottom: 30px;
            color: white;
            font-size: 30px;
            font-weight: bold;
        }

        .form-section label {
            display: block;
            text-align: left;
            font-size: 20px;
            margin-bottom: 6px;
            padding-left: 5px;
        }

        .form-section input {
            width: 100%;
            padding: 10px;
            margin-bottom: 18px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.15);
        }

        .form-section input:focus {
            outline: 2px solid #004a9f;
        }

        .form-section button {
            width: 100%;
            padding: 16px;
            background-color: #004a9f;
            border: none;
            color: white;
            font-weight: bold;
            border-radius: 12px;
            cursor: pointer;
            font-size: 17px;
            transition: 0.3s;
            box-shadow: 0 5px 8px rgba(0,0,0,0.25);
        }

        .form-section button:hover {
            background-color: #00367a;
            transform: translateY(-1px);
        }

        .message {
            text-align: center;
            font-size: 15px;
            margin-top: 10px;
        }

        .error { color: #ffcccc; }
        .success { color: #a6ff91; }

    .image-section img {
        width: 450px;
        height: auto;
        object-fit: contain;
        transform: translate(100px, 30px);
    }
        .form-section label,
    .form-section input {
        margin-left: -15px;
    }

    .form-section button {
        margin-top: 5px;
        font-family: "Raleway", sans-serif;
    }

    select {
        width: 100%;
        padding: 10px;
        margin-bottom: 18px;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.15);
    }
    select:focus {
        outline: 2px solid #004a9f;
    }


    </style>
    </head>
    <body>

<div class="logo-section">
    <img src="/ap_project1/super_admin/includes/Screenshot1-removebg-preview.png" alt="Urban Medical Hospital">
</div>

<div class="main-container">
    <div class="form-section">
<h2 style="margin-top: -30px;">Create Account</h2>
<?php if ($error) echo "<p class='message error'>$error</p>"; ?>
<form method="POST" action="">
    <label>Username:</label>
    <input type="text" name="username" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <label>Confirm Password:</label>
    <input type="password" name="confirm" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>First Name:</label>
    <input type="text" name="firstname" required>

    <label>Last Name:</label>
    <input type="text" name="lastname" required>

    <label>Role:</label>
    <select name="role" required>
        <option value="">Select Role</option>
        <option value="Superadmin">SuperAdmin</option>
        <option value="Doctor">Doctor</option>
        <option value="Staff">Staff</option>
        <option value="Patient">Patient</option>
    </select>

    <button type="submit">Sign Up</button>
</form>
</div>

    <div class="image-section">
        <img src="/ap_project1/super_admin/includes/Screenshot_2.png" alt="Medical Tools">
    </div>
</div>

<script>
function showLinkSelect() {
    var role = document.getElementById('role').value;
    document.getElementById('linkPatient').style.display = (role === 'Patient') ? 'block' : 'none';
    document.getElementById('linkStaff').style.display = (role === 'Staff') ? 'block' : 'none';
    document.getElementById('linkDoctor').style.display = (role === 'Doctor') ? 'block' : 'none';
}
</script>

</body>
    </html>