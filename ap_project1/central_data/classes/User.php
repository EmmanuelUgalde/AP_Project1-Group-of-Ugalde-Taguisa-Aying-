<?php
require_once __DIR__ . "/../config/Database.php";

class User {
    private $conn;

    public function __construct()
    {
        try {
            $database = new Database();
            $this->conn = $database->getConn();
        } catch (Exception $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function get_user_role($user_id) {
        $stmt = $this->conn->prepare("SELECT USER_IS_SUPERADMIN, PAT_ID, STAFF_ID, DOC_ID FROM USER WHERE USER_ID = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['USER_IS_SUPERADMIN']) return 'Superadmin';
        if ($row['PAT_ID']) return 'Patient';
        if ($row['STAFF_ID']) return 'Staff';
        if ($row['DOC_ID']) return 'Doctor';
        return 'Unknown';
    }

    public function add_user($data) {
        try {
            $username = trim($data['username']);
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            $role = strtolower($data['role']);

            $isSuperadmin = 0;
            $pat_id = ($role === 'patient') ? 1 : NULL;
            $staff_id = ($role === 'staff') ? 1 : NULL;
            $doc_id = ($role === 'doctor') ? 1 : NULL;

            $stmt = $this->conn->prepare("
                INSERT INTO USER (USER_NAME, USER_PASSWORD, USER_IS_SUPERADMIN, PAT_ID, STAFF_ID, DOC_ID, USER_CREATED_AT)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("ssiiii", $username, $password, $isSuperadmin, $pat_id, $staff_id, $doc_id);

            if ($stmt->execute()) {
                $newId = $this->conn->insert_id;
                $stmt->close();
                return ["success" => true, "message" => "User created successfully!", "user_id" => $newId];
            } else {
                return ["success" => false, "message" => "Error adding user: " . $stmt->error];
            }

        } catch (Exception $e) {
            return ["success" => false, "message" => "Error adding user: " . $e->getMessage()];
        }
    }

    public function update_last_login($user_id) {
        $stmt = $this->conn->prepare("UPDATE USER SET USER_LAST_LOGIN = NOW() WHERE USER_ID = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }

    public function view_user($user_id) {
        $stmt = $this->conn->prepare("
            SELECT USER_ID, USER_NAME, USER_IS_SUPERADMIN, PAT_ID, STAFF_ID, DOC_ID,
                CASE
                    WHEN USER_IS_SUPERADMIN = TRUE THEN 'Superadmin'
                    WHEN PAT_ID IS NOT NULL THEN 'Patient'
                    WHEN STAFF_ID IS NOT NULL THEN 'Staff'
                    WHEN DOC_ID IS NOT NULL THEN 'Doctor'
                    ELSE 'Unknown'
                END AS ROLE
            FROM USER
            WHERE USER_ID = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        return $user ?: null;
    }

    public function view_all_users($currentRole) {
        if ($currentRole !== 'Superadmin') return "Access Denied: Only Superadmin can view all users.";

        $sql = "
            SELECT USER_ID, USER_NAME, USER_IS_SUPERADMIN, PAT_ID, STAFF_ID, DOC_ID,
                CASE
                    WHEN USER_IS_SUPERADMIN = TRUE THEN 'Superadmin'
                    WHEN PAT_ID IS NOT NULL THEN 'Patient'
                    WHEN STAFF_ID IS NOT NULL THEN 'Staff'
                    WHEN DOC_ID IS NOT NULL THEN 'Doctor'
                    ELSE 'Unknown'
                END AS ROLE
            FROM USER
            ORDER BY USER_NAME ASC
        ";

        $result = $this->conn->query($sql);
        $users = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        return $users;
    }

    public function view_all_doctors($currentRole) {
        if ($currentRole !== 'Superadmin') return "Access Denied: Only Superadmin can view doctors.";

        $sql = "SELECT USER_ID, USER_NAME, DOC_ID, 'Doctor' AS ROLE FROM USER WHERE DOC_ID IS NOT NULL ORDER BY USER_NAME ASC";
        $result = $this->conn->query($sql);
        $doctors = [];
        if ($result) while ($row = $result->fetch_assoc()) $doctors[] = $row;
        return $doctors;
    }

    public function view_all_patients($currentRole) {
        if ($currentRole !== 'Superadmin') return "Access Denied: Only Superadmin can view patients.";

        $sql = "SELECT USER_ID, USER_NAME, PAT_ID, 'Patient' AS ROLE FROM USER WHERE PAT_ID IS NOT NULL ORDER BY USER_NAME ASC";
        $result = $this->conn->query($sql);
        $patients = [];
        if ($result) while ($row = $result->fetch_assoc()) $patients[] = $row;
        return $patients;
    }

    public function view_all_staff($currentRole) {
        if ($currentRole !== 'Superadmin') return "Access Denied: Only Superadmin can view staff.";

        $sql = "SELECT USER_ID, USER_NAME, STAFF_ID, 'Staff' AS ROLE FROM USER WHERE STAFF_ID IS NOT NULL ORDER BY USER_NAME ASC";
        $result = $this->conn->query($sql);
        $staff = [];
        if ($result) while ($row = $result->fetch_assoc()) $staff[] = $row;
        return $staff;
    }
}
?>
