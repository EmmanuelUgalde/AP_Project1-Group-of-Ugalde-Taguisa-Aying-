<?php 
require_once __DIR__ . "/../config/Database.php";

class Specialization {
    private $conn;

    public function __construct()
    {
        try {
            $database = new Database();
            $this->conn = $database->getConn();
        } catch (Exception $e){
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function get_all($currentRole) {
    try {
        if (!in_array($currentRole, ['Superadmin', 'Staff'])) {
            return [];
        }

        $sql = "SELECT * FROM SPECIALIZATION ORDER BY SPEC_ID ASC";
        $result = $this->conn->query($sql);

        $specialization = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $specialization[] = $row;
            }
        }

        return $specialization;
    } catch (Exception $e) {
        return []; 
    }
}



    public function search_special($keyword) {
    try {
        $conn = $this->conn;
        $specialization = [];

        if (is_numeric($keyword)) {
            $stmt = $conn->prepare("SELECT * FROM SPECIALIZATION WHERE SPEC_ID = ?");
            $stmt->bind_param("i", $keyword);
        } else {
            $stmt = $conn->prepare("SELECT * FROM SPECIALIZATION WHERE SPEC_NAME LIKE ?");
            $search = "%{$keyword}%";
            $stmt->bind_param("s", $search);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $specialization[] = $row;
            }
        }

        return $specialization;
    } catch (Exception $e) {
        return [];
    }
}


    public function add_spec($currentRole, $data) {
    try {
        if ($currentRole !== 'Superadmin') {
            return "Access Denied: Only Superadmin can add services.";
        }

        $conn = $this->conn;

        $stmt = $conn->prepare("INSERT INTO SPECIALIZATION (SPEC_NAME, SPEC_CREATED_AT, SPEC_UPDATED_AT) VALUES (?, NOW(), NOW())");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $data['spec_name']);

        if($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

    } catch (Exception $e) {
        echo "Error adding specialization: " . $e->getMessage();
        return false;
    }
}


    public function update_spec($currentRole, $id, $data) {
        try {
            if (!in_array($currentRole, ['Superadmin', 'Staff'])) {
                return "Access Denied: Only SuperAdmin or Staff can update services.";
            }

            $conn = $this->conn;

            $stmt = $conn->prepare("UPDATE SPECIALIZATION SET SPEC_NAME = ?, SPEC_UPDATED_AT = NOW() WHERE SPEC_ID = ?");
            $stmt->bind_param("si", $data['spec_name'], $id);

            if ($stmt->execute()) {
                echo "Specialization updated successfully!";
            } else {
                echo "Error updating specialization: " . $stmt->error;
            }

            $stmt->close();
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
            echo "Error updating specialization: " . $e->getMessage();
        }
    }

    public function delete_spec($currentRole, $id) {
    try {
        if (strtolower($currentRole) !== 'superadmin') {
            return "Access Denied: Only Superadmin can delete specialization.";
        }

        $checkStmt = $this->conn->prepare("SELECT COUNT(*) as doctor_count FROM DOCTOR WHERE SPEC_ID = ?");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['doctor_count'] > 0) {
            return "Error deleting specialization: Doctor(s) are still assigned to this specialization.";
        }
        $stmt = $this->conn->prepare("DELETE FROM SPECIALIZATION WHERE SPEC_ID = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return "Specialization deleted successfully!";
        } else {
            return "Error deleting specialization: " . $stmt->error;
        }

    } catch (Exception $e) {
        return "Error deleting specialization: " . $e->getMessage();
    }
}

    public function get_doctor_spec($currentRole, $spec_name) {
        try {
            if (!in_array($currentRole, ['Superadmin', 'Staff'])) {
                return "Access Denied: Only SuperAdmin or Staff can view doctors under specializations.";
            }

            $conn = $this->conn;

            $stmt = $conn->prepare("SELECT d.DOC_ID, d.DOC_FIRST_NAME, d.DOC_LAST_NAME, d.DOC_MIDDLE_INIT,
                    d.DOC_CONTACT_NUM, d.DOC_EMAIL, d.DOC_CREATED_AT, d.DOC_UPDATED_AT, s.SPEC_ID
                    FROM DOCTOR d
                    INNER JOIN SPECIALIZATION s ON d.SPEC_ID = s.SPEC_ID
                    WHERE s.SPEC_NAME = ?");
            
            $stmt->bind_param("s", $spec_name);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $doctors = [];
            while ($row = $result->fetch_assoc()) {
                $doctors[] = $row;
            }

            return $doctors;
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
            return "Error fetching doctors: " . $e->getMessage();
        }
    }
}
?>