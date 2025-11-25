<?php 
require_once __DIR__ . "/../config/Database.php";

class Patient {
    private $conn;

    public function __construct() {
        try {
            $database = new Database();
            $this->conn = $database->getConn();
        } catch (Exception $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function add_patient($currentRole, $data) {
        try {
            if (!in_array(strtolower($currentRole), ['superadmin', 'patient'])) {
                return "Access Denied: Only SuperAdmin or Patient can add patients.";
            }

            $stmt = $this->conn->prepare("INSERT INTO PATIENT 
                (PAT_FIRST_NAME, PAT_MIDDLE_INIT, PAT_LAST_NAME, PAT_DOB, PAT_GENDER,
                 PAT_CONTACT_NUM, PAT_EMAIL, PAT_ADDRESS, PAT_CREATED_AT)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            $stmt->bind_param(
                "ssssssss", 
                $data['pat_first_name'], 
                $data['pat_middle_init'], 
                $data['pat_last_name'], 
                $data['pat_dob'], 
                $data['pat_gender'], 
                $data['pat_contact_num'], 
                $data['pat_email'], 
                $data['pat_address']
            );


            if ($stmt->execute()) {
                return "Patient added successfully.";
            } else {
                return "Error adding patient: " . $stmt->error;
            }

            $stmt->close();
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function get_all($currentRole) {
        try {
            if (!in_array(strtolower($currentRole), ['superadmin', 'patient'])) {
                return "Access Denied: Only SuperAdmin or Patient can view patients.";
            }

            $sql = "SELECT * FROM PATIENT ORDER BY PAT_LAST_NAME ASC";
            $result = $this->conn->query($sql);

            $patients = [];
            if ($result) {
                while($row = $result->fetch_assoc()) {
                    $patients[] = $row;
                }
            }

            return $patients;
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
            return "Error fetching patients: " . $e->getMessage();
        }
    }
    
    public function search_patient($currentRole, $keyword) {
        try {
            if (!in_array(strtolower($currentRole), ['superadmin', 'patient'])) {
                return "Access Denied: Only SuperAdmin or Patient can search patients.";
            }

            $stmt = $this->conn->prepare("SELECT * FROM PATIENT
                WHERE PAT_FIRST_NAME LIKE ? OR PAT_LAST_NAME LIKE ? 
                ORDER BY PAT_LAST_NAME ASC");

            $likeKeyword = "%$keyword%";
            $stmt->bind_param("ss", $likeKeyword, $likeKeyword);
            $stmt->execute();
            $result = $stmt->get_result();

            $patients = [];
            while ($row = $result->fetch_assoc()) {
                $patients[] = $row;
            }

            if (empty($patients)) {
                return "No patient found matching that name.";
            }

            return $patients;

        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
            return "Error searching patients: " . $e->getMessage();
        }
    }

    public function get($currentRole, $id, $currentPatId = null) {
        try {
            if ($currentRole === 'Patient' && $id != $currentPatId) {
                return "Access Denied: You can only view your own profile.";
            }

            $stmt = $this->conn->prepare("SELECT * FROM PATIENT WHERE PAT_ID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            $patient = $result->fetch_assoc();
            if (!$patient) {
                return "Patient not found.";
            }

            return $patient;
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
            return "Error fetching patient: " . $e->getMessage();
        }
    }

    public function update_patient($currentRole, $id, $data, $currentPatId = null) {
        try {
            if (!in_array(strtolower($currentRole), ['superadmin', 'patient'])) {
                return "Access Denied: Only SuperAdmin or Patient can update patients.";
            }

            if ($currentRole === 'Patient' && $id != $currentPatId) {
                return "Access Denied: You can only update your own information.";
            }

            $stmt = $this->conn->prepare("UPDATE PATIENT 
                SET PAT_FIRST_NAME = ?, PAT_MIDDLE_INIT = ?, PAT_LAST_NAME = ?, PAT_DOB = ?, 
                    PAT_GENDER = ?, PAT_CONTACT_NUM = ?, PAT_EMAIL = ?, PAT_ADDRESS = ?, PAT_UPDATED_AT = NOW()
                WHERE PAT_ID = ?");

            $stmt->bind_param(
                "ssssssssi", 
                $data['pat_first_name'], 
                $data['pat_middle_init'], 
                $data['pat_last_name'], 
                $data['pat_dob'], 
                $data['pat_gender'], 
                $data['pat_contact_num'], 
                $data['pat_email'], 
                $data['pat_address'],
                $id
            );

            if ($stmt->execute()) {
                return "Patient information updated successfully.";
            } else {
                return "Update Error: " . $stmt->error;
            }

            $stmt->close();
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
            return "Error updating patient: " . $e->getMessage();
        }
    }

    public function delete_patient($currentRole, $id) {
    try {
        if (strtolower($currentRole) !== 'superadmin') {
            return "Access Denied: Only SuperAdmin can delete patients.";
        }

        $stmt = $this->conn->prepare("DELETE FROM PATIENT WHERE PAT_ID = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            return "Patient deleted successfully.";
        } else {
            $error = $stmt->error;
            $stmt->close();
            return "Error deleting patient: " . $error;
        }
        
    } catch (Exception $e) {
        return "Error deleting patient: " . $e->getMessage();
    }
}

}
?>
