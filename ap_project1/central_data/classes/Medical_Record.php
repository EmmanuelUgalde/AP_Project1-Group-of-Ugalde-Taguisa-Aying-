<?php 
require_once __DIR__ . "/../config/Database.php";

class Medical_Record {
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConn();
    }

    public function get_all($currentRole) {
        try {
            $role = strtolower($currentRole);

            if (!in_array($role, ['superadmin', 'doctor', 'staff'])) {
                return "Access Denied.";
            }

            $sql = "SELECT * FROM MEDICAL";
            $result = $this->conn->query($sql);

            $medical = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $medical[] = $row;
                }
            }
            return $medical;

        } catch (Exception $e) {
            return "Error fetching medical records: " . $e->getMessage();
        }
    }

    public function add_medical($currentRole, $data) {
        try {
            $role = strtolower($currentRole);

            if (!in_array($role, ['superadmin', 'doctor'])) {
                return "Access Denied: Only Superadmin or Doctor can add records.";
            }

            $stmt = $this->conn->prepare("
                INSERT INTO MEDICAL (
                    MED_REC_DIAGNOSIS, 
                    MED_REC_PRESCRIPTION, 
                    MED_REC_VISIT_DATE,
                    MED_REC_CREATED_AT,
                    MED_REC_UPDATED_AT,
                    APPT_ID
                ) VALUES (?, ?, ?, NOW(), NOW(), ?)
            ");

            $stmt->bind_param("ssss",
                $data['med_rec_diagnosis'],
                $data['med_rec_prescription'],
                $data['med_rec_visit_date'],
                $data['appt_id']
            );

            $success = $stmt->execute();
            $error = $stmt->error;
            $stmt->close();

            return $success ? "Medical record added successfully!" :
                              "Error adding record: " . $error;

        } catch (Exception $e) {
            return "Error adding medical record: " . $e->getMessage();
        }
    }

    public function search_medical($currentRole, $id) {
        try {
            $role = strtolower($currentRole);

            if (!in_array($role, ['superadmin', 'doctor', 'staff'])) {
                return "Access Denied.";
            }

            $stmt = $this->conn->prepare("SELECT * FROM MEDICAL WHERE MED_REC_ID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $result = $stmt->get_result();
            $medical = [];
            while ($row = $result->fetch_assoc()) {
                $medical[] = $row;
            }

            return $medical;

        } catch (Exception $e) {
            return "Error searching record: " . $e->getMessage();
        }
    }

    public function update_medical($currentRole, $id, $data) {
        try {
            $role = strtolower($currentRole);

            if (!in_array($role, ['superadmin', 'doctor'])) {
                return "Access Denied: Only Superadmin or Doctor can update records.";
            }

            $stmt = $this->conn->prepare("
                UPDATE MEDICAL SET 
                    MED_REC_DIAGNOSIS = ?, 
                    MED_REC_PRESCRIPTION = ?, 
                    MED_REC_VISIT_DATE = ?,
                    MED_REC_UPDATED_AT = NOW(),
                    APPT_ID = ?
                WHERE MED_REC_ID = ?
            ");

            $stmt->bind_param("ssssi",
                $data['med_rec_diagnosis'],
                $data['med_rec_prescription'],
                $data['med_rec_visit_date'],
                $data['appt_id'],
                $id
            );

            $success = $stmt->execute();
            $stmt->close();

            return $success ? "Medical record updated successfully!" :
                              "Error updating record.";

        } catch (Exception $e) {
            return "Error updating record: " . $e->getMessage();
        }
    }

    public function delete_medical($currentRole, $id) {
        try {
            $role = strtolower($currentRole);

            if ($role !== 'superadmin') {
                return "Access Denied: Only Superadmin can delete records.";
            }

            $stmt = $this->conn->prepare("DELETE FROM MEDICAL WHERE MED_REC_ID = ?");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();

            return $success ? "Medical record deleted successfully!" :
                              "Error deleting record.";

        } catch (Exception $e) {
            return "Error deleting medical record: " . $e->getMessage();
        }
    }
}
?>
