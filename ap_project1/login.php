<?php
session_start();
require_once __DIR__ . "/central_data/config/Database.php";
require_once __DIR__ . "/central_data/classes/User.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = "Please enter both username and password!";
    } else {
        $userObj = new User();
        $db = new Database();
        $conn = $db->getConn();

        $stmt = $conn->prepare("SELECT USER_ID, USER_PASSWORD FROM USER WHERE USER_NAME = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['USER_PASSWORD'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['USER_ID'];
            $_SESSION['username'] = $username;

            $userObj->update_last_login($user['USER_ID']);

            $role = $userObj->get_user_role($user['USER_ID']);
            $_SESSION['role'] = $role;

            switch ($role) {
                case 'Superadmin':
                    header("Location: /ap_project1/super_admin/index.php");
                    break;
                case 'Doctor':
                    header("Location: /ap_project1/doctor/index.php");
                    break;
                case 'Staff':
                    header("Location: /ap_project1/staff/index.php");
                    break;
                case 'Patient':
                    header("Location: /ap_project1/patient/index.php");
                    break;
                default:
                    $error = "Unknown role!";
            }
            exit();
        } else {
            $error = "Invalid username or password!";
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
  <title>Urban Medical Hospital</title>
</head>
<style>
  body {
    margin: 0;
    height: 100vh;
    background-color: #FFF9F9;
    font-family: 'Port Lligat Slab', serif;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    padding-left: 10px;
  }

  .login-container {
    background-color: #3D90D7;
    width: 650px;
    height: 650px;
    padding: 60px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    border-radius: 15px;
    text-align: center;
    box-sizing: border-box;
    position: relative;
  }

  .login-container h1 {
    color: white;
    font-size: 100px;
    margin-bottom: 50px;
    margin-top: 5px;
    margin-left: -287px;
  }

  input[type="text"],
  input[type="password"] {
    width: 100%;
    padding: 18px 45px 18px 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 18px;
    box-sizing: border-box;
  }

  .username-box {
    margin-bottom: 20px;
  }

  .password-box {
    position: relative;
    width: 100%;
    max-width: 600px;
    margin: 0 auto 25px auto;
  }

  .password-box span {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 22px;
    color: #0041c7;
    user-select: none;
  }

  .links {
    display: flex;
    justify-content: space-between;
    font-size: 18px;
    color: white;
    margin-bottom: 25px;
    margin-top: 30px;
  }

  .links a {
    color: #FFD700;
    text-decoration: none;
    font-size: 30px;
  }

  .links a:hover {
    color: white;
    text-decoration: none;
    transition: 0.5s;
  }

  .login-container button {
    width: 100%;
    background-color: #0041c7;
    color: white;
    border: none;
    padding: 20px;
    font-size: 30px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s;
    font-family: 'Port Lligat Slab', serif;
  }

  .login-container button:hover {
    background-color: #0033a8;
  }

 .sign-google {
  margin-top: 30px;
  text-align: center;
}

.sign-google a {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  background-color: white;
  padding: 10px 80px;
  border-radius: 30px;
  text-decoration: none;
  color: #444;
  font-size: 18px;
  font-weight: 500;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
  transition: 0.3s;
}

.sign-google a:hover {
  background-color: #f0f0f0;
  transform: scale(1.03);
}

.google-icon {
  background-color: #fff;
  border-radius: 50%;
  padding: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 4px rgba(0,0,0,0.2);
}

.google-icon img {
  width: 30px;
  height: 30px;
}

.description img {
            justify-content: flex-end;
            width: 580px;
            height: auto;
            display: block;
            margin-left: 255px;
        }


</style>

<body>
<?php include "central_data/includes/header.html"; ?>
  <div class="login-container">
    <h1>Log In</h1>
    <form action="" method="POST">
      <div class="username-box">
        <input type="text" name="username" placeholder="Username" required>
      </div>

      <div class="password-box">
        <input type="password" name="password" id="password" placeholder="Password" required>
        <span onclick="togglePassword()">👁️</span>
      </div>

      <div class="links">
        <a href="#">Forgot Password?</a>
        <a href="/ap_project1/create.php">Don’t have an account?</a>
      </div>

      <button type="submit">Sign In</button>


      <?php if (!empty($error)): ?>
        <script>alert("<?= addslashes($error) ?>");</script>
      <?php endif; ?>
    </form>
  </div>

<div class="description">
        <img src="https://res.cloudinary.com/dlsr0ebdd/image/upload/v1764053455/Screenshot_2_jdqdx3.png" alt="Description About the Hospital">
</div>
<?php include "central_data/includes/footer.html"; ?>

<script>
  function togglePassword() {
    const passwordField = document.getElementById("password");
    passwordField.type = passwordField.type === "password" ? "text" : "password";
  }
</script>
</body>
</html>
</html>

