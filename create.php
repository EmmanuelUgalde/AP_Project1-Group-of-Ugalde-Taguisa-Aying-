<?php
session_start();
require_once __DIR__ . "/central_data/config/Database.php";
require_once __DIR__ . "/central_data/classes/User.php";

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $role = $_POST['role'] ?? '';

    if (!$username || !$password || !$confirmPassword || !$email || !$firstName || !$lastName || !$role) {
        $error = "Please fill in all fields!";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters!";
    } else {
        $db = new Database();
        $conn = $db->getConn();

        $stmt = $conn->prepare("SELECT USER_ID FROM USER WHERE USER_NAME = ? OR USER_EMAIL = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username or email already exists!";
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
                    (USER_NAME, USER_EMAIL, USER_PASSWORD, USER_FIRSTNAME, USER_LASTNAME, USER_IS_SUPERADMIN, PAT_ID, STAFF_ID, DOC_ID, USER_CREATED_AT)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->bind_param(
                    "sssssiiii",
                    $username,
                    $email,
                    $hashedPassword,
                    $firstName,
                    $lastName,
                    $isSuperadmin,
                    $pat_id,
                    $staff_id,
                    $doc_id
                );

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
  <title>Register - Urban Medical Hospital</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      background: linear-gradient(135deg, #EFF6FF 0%, #E0E7FF 50%, #DBEAFE 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
    }

    .auth-wrapper {
      width: 100%;
      max-width: 520px;
    }

    /* Logo Section */
    .logo-section {
      text-align: center;
      margin-bottom: 2rem;
    }

    .logo-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 64px;
      height: 64px;
      background-color: #2563EB;
      border-radius: 50%;
      margin-bottom: 1rem;
      box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
    }

    .logo-icon svg {
      width: 32px;
      height: 32px;
      stroke: white;
      stroke-width: 3;
      fill: none;
    }

    .logo-section h1 {
      font-size: 2.25rem;
      font-weight: 800;
      color: #1E3A8A;
      margin-bottom: 0.5rem;
    }

    .logo-section p {
      font-size: 1.125rem;
      color: #2563EB;
      font-weight: 600;
    }

    /* Auth Card */
    .auth-card {
      background: white;
      border-radius: 24px;
      padding: 2.5rem;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
      border: 1px solid #DBEAFE;
    }

    .auth-card h2 {
      font-size: 1.75rem;
      font-weight: 800;
      color: #1E3A8A;
      text-align: center;
      margin-bottom: 1.75rem;
    }

    /* Form Grid */
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.25rem;
    }

    .form-grid-full {
      grid-column: 1 / -1;
    }

    /* Form Elements */
    .form-group {
      margin-bottom: 1.25rem;
    }

    .form-group label {
      display: block;
      font-size: 0.875rem;
      font-weight: 700;
      color: #1E3A8A;
      margin-bottom: 0.5rem;
    }

    .input-wrapper {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      width: 20px;
      height: 20px;
      stroke: #93C5FD;
      z-index: 1;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
      width: 100%;
      padding: 14px 14px 14px 44px;
      border: 2px solid #BFDBFE;
      border-radius: 12px;
      font-size: 0.95rem;
      outline: none;
      transition: all 0.3s;
      font-family: 'Inter', sans-serif;
      background: white;
    }

    select {
      padding-left: 44px;
      cursor: pointer;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%232563EB' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus,
    select:focus {
      border-color: #2563EB;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .password-toggle {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #93C5FD;
      transition: color 0.3s;
      font-size: 1.25rem;
      z-index: 1;
    }

    .password-toggle:hover {
      color: #2563EB;
    }

    /* Submit Button */
    .submit-btn {
      width: 100%;
      padding: 16px;
      background-color: #2563EB;
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 1.05rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
      font-family: 'Inter', sans-serif;
      margin-top: 0.5rem;
    }

    .submit-btn:hover {
      background-color: #1D4ED8;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
    }

    .submit-btn:active {
      transform: translateY(0);
    }

    /* Divider */
    .divider {
      position: relative;
      text-align: center;
      margin: 1.5rem 0;
    }

    .divider::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      width: 100%;
      height: 1px;
      background-color: #BFDBFE;
    }

    .divider span {
      position: relative;
      background: white;
      padding: 0 12px;
      color: #2563EB;
      font-weight: 600;
      font-size: 0.875rem;
    }

    /* Social Login */
    .social-login {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-top: 1rem;
    }

    .social-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 12px;
      border: 2px solid #BFDBFE;
      border-radius: 12px;
      background: white;
      cursor: pointer;
      transition: all 0.3s;
      text-decoration: none;
      font-weight: 700;
      font-size: 0.875rem;
      color: #1E3A8A;
    }

    .social-btn:hover {
      background-color: #EFF6FF;
      border-color: #93C5FD;
      transform: translateY(-2px);
    }

    .social-btn svg {
      margin-right: 8px;
    }

    /* Login Link */
    .login-link {
      text-align: center;
      margin-top: 1.5rem;
      font-size: 0.875rem;
      color: #1E3A8A;
    }

    .login-link a {
      color: #2563EB;
      text-decoration: none;
      font-weight: 700;
      transition: color 0.3s;
    }

    .login-link a:hover {
      color: #1E40AF;
      text-decoration: underline;
    }

    /* Terms */
    .terms {
      text-align: center;
      margin-top: 1.5rem;
      font-size: 0.75rem;
      color: #1E40AF;
      line-height: 1.5;
    }

    .terms a {
      color: #2563EB;
      text-decoration: underline;
      font-weight: 600;
      transition: color 0.3s;
    }

    .terms a:hover {
      color: #1E3A8A;
    }

    /* Alert Messages */
    .error-alert {
      background-color: #FEE2E2;
      border: 1px solid #FCA5A5;
      color: #991B1B;
      padding: 14px 18px;
      border-radius: 12px;
      margin-bottom: 1.25rem;
      font-size: 0.875rem;
      font-weight: 600;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .success-alert {
      background-color: #D1FAE5;
      border: 1px solid #6EE7B7;
      color: #065F46;
      padding: 14px 18px;
      border-radius: 12px;
      margin-bottom: 1.25rem;
      font-size: 0.875rem;
      font-weight: 600;
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    /* Responsive */
    @media (max-width: 640px) {
      .form-grid {
        grid-template-columns: 1fr;
      }

      .logo-section h1 {
        font-size: 1.75rem;
      }

      .auth-card {
        padding: 1.5rem;
      }

      .social-login {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="auth-wrapper">
    <!-- Logo Section -->
    <div class="logo-section">
      <div class="logo-icon">
        <svg viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
      </div>
      <h1>Urban Medical Hospital</h1>
      <p>Create your account</p>
    </div>

    <!-- Auth Card -->
    <div class="auth-card">
      <h2>Register</h2>

      <?php if (!empty($error)): ?>
        <div class="error-alert">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="success-alert">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <form action="" method="POST">
        <div class="form-grid">
          <!-- First Name -->
          <div class="form-group">
            <label for="first_name">First Name</label>
            <div class="input-wrapper">
              <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <input type="text" id="first_name" name="first_name" placeholder="John" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
            </div>
          </div>

          <!-- Last Name -->
          <div class="form-group">
            <label for="last_name">Last Name</label>
            <div class="input-wrapper">
              <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <input type="text" id="last_name" name="last_name" placeholder="Doe" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
            </div>
          </div>
        </div>

        <!-- Username -->
        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-wrapper">
            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <input type="text" id="username" name="username" placeholder="Choose a username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>
        </div>

        <!-- Email -->
        <div class="form-group">
          <label for="email">Email Address</label>
          <div class="input-wrapper">
            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <input type="email" id="email" name="email" placeholder="you@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
        </div>

        <div class="form-grid">
          <!-- Password -->
          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
              <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
              <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
              <span class="password-toggle" onclick="togglePassword('password')">👁️</span>
            </div>
          </div>

          <!-- Confirm Password -->
          <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <div class="input-wrapper">
              <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
              <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
            </div>
          </div>
        </div>

        <!-- Role -->
       <div class="form-group">
       <label for="role">Select Role</label>
       <div class="input-wrapper">
       <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
       <select id="role" name="role" required>
      <option value="">Choose your role</option>
      <option value="Patient" <?= (isset($_POST['role']) && $_POST['role'] === 'Patient') ? 'selected' : '' ?>>Patient</option>
      <option value="Doctor" <?= (isset($_POST['role']) && $_POST['role'] === 'Doctor') ? 'selected' : '' ?>>Doctor</option>
      <option value="Staff" <?= (isset($_POST['role']) && $_POST['role'] === 'Staff') ? 'selected' : '' ?>>Staff</option>
      <option value="Superadmin" <?= (isset($_POST['role']) && $_POST['role'] === 'Superadmin') ? 'selected' : '' ?>>Superadmin</option>
    </select>
  </div>
</div>

        <!-- Submit Button -->
        <button type="submit" class="submit-btn">Create Account</button>
      </form>

      <!-- Divider -->
      <div class="divider">
        <span>Or continue with</span>
      </div>

      <!-- Social Login -->
      <div class="social-login">
        <a href="#" class="social-btn">
          <svg width="20" height="20" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          Google
        </a>
        <a href="#" class="social-btn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="#1877F2">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
          </svg>
          Facebook
        </a>
      </div>

      <!-- Login Link -->
      <div class="login-link">
        Already have an account? <a href="/ap_project1/index.php">Log In</a>
      </div>
    </div>

    <!-- Terms -->
    <div class="terms">
      By continuing, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
    </div>
  </div>

  <script>
    function togglePassword(fieldId) {
      const passwordField = document.getElementById(fieldId);
      const toggle = event.currentTarget;
      
      if (passwordField.type === "password") {
        passwordField.type = "text";
        toggle.textContent = "🙈";
      } else {
        passwordField.type = "password";
        toggle.textContent = "👁️";
      }
    }
  </script>
</body>
</html>