<?php
require_once __DIR__ . "/../config/Database.php";

class Doctor {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConn();
    }

    public function add_doctor($currentRole, $data) {
        try {
            $currentRole = strtolower($currentRole);

            if (!in_array($currentRole, ['superadmin', 'doctor'])) {
                throw new Exception("Access Denied.");
            }

            $sql = "INSERT INTO DOCTOR 
                    (DOC_FIRST_NAME, DOC_LAST_NAME, DOC_MIDDLE_INIT, DOC_CONTACT_NUM,
                     DOC_EMAIL, DOC_CREATED_AT, DOC_UPDATED_AT, SPEC_ID)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?)";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
    "sssisi",
    $data['doc_first_name'],
    $data['doc_last_name'],
    $data['doc_middle_init'],
    $data['doc_contact_num'], 
    $data['doc_email'],
    $data['spec_id']      
);

            if ($stmt->execute()) {
                return ["success" => true, "message" => "Doctor added successfully."];
            }
            return ["success" => false, "message" => $stmt->error];

        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function get_all($currentRole) {
        try {
            $currentRole = strtolower($currentRole);

            if (!in_array($currentRole, ['superadmin', 'doctor'])) {
                throw new Exception("Access Denied.");
            }

            $sql = "SELECT D.*, S.SPEC_NAME
        FROM DOCTOR D
        LEFT JOIN SPECIALIZATION S ON D.SPEC_ID = S.SPEC_ID
        ORDER BY D.DOC_LAST_NAME ASC";


            $result = $this->conn->query($sql);

            $doctors = [];
            while ($row = $result->fetch_assoc()) {
                $doctors[] = $row;
            }

            return $doctors;

        } catch (Exception $e) {
            return [];
        }
    }

    public function search_doctor($currentRole, $keyword) {
        try {
            $currentRole = strtolower($currentRole);

            if (!in_array($currentRole, ['superadmin', 'doctor'])) {
                throw new Exception("Access Denied.");
            }

            $keyword = "%" . $keyword . "%";

            $sql = "SELECT D.*, S.SPEC_NAME
                    FROM DOCTOR D
                    LEFT JOIN SPECIALIZATION S ON D.SPEC_ID = S.SPEC_ID
                    WHERE D.DOC_FIRST_NAME LIKE ?
                       OR D.DOC_LAST_NAME LIKE ?
                       OR D.DOC_EMAIL LIKE ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sss", $keyword, $keyword, $keyword);
            $stmt->execute();

            $result = $stmt->get_result();
            $doctors = [];

            while ($row = $result->fetch_assoc()) {
                $doctors[] = $row;
            }

            return $doctors;

        } catch (Exception $e) {
            return [];
        }
    }

    public function update_doctor($currentRole, $id, $data) {
        try {
            $currentRole = strtolower($currentRole);

            if (!in_array($currentRole, ['superadmin', 'doctor'])) {
                throw new Exception("Access Denied.");
            }

            $sql = "UPDATE DOCTOR
                    SET DOC_FIRST_NAME = ?, 
                        DOC_LAST_NAME = ?, 
                        DOC_MIDDLE_INIT = ?, 
                        DOC_CONTACT_NUM = ?, 
                        DOC_EMAIL = ?, 
                        SPEC_ID = ?, 
                        DOC_UPDATED_AT = NOW()
                    WHERE DOC_ID = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
    "sssssii",
    $data['doc_first_name'],
    $data['doc_last_name'],
    $data['doc_middle_init'],
    $data['doc_contact_num'], 
    $data['doc_email'],
    $data['spec_id'],
    $id
);


            if ($stmt->execute()) {
                return ["success" => true, "message" => "Doctor updated successfully."];
            }
            return ["success" => false, "message" => $stmt->error];

        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function delete_doctor($currentRole, $id) {
    try {
        $currentRole = strtolower($currentRole);

        if (!in_array($currentRole, ['superadmin'])) {
            throw new Exception("Access Denied.");
        }

        $sqlSched = "DELETE FROM schedule WHERE DOC_ID = ?";
        $stmtSched = $this->conn->prepare($sqlSched);
        $stmtSched->bind_param("i", $id);
        $stmtSched->execute();
        $stmtSched->close();

        $sqlDoctor = "DELETE FROM doctor WHERE DOC_ID = ?";
        $stmtDoctor = $this->conn->prepare($sqlDoctor);
        $stmtDoctor->bind_param("i", $id);

        if ($stmtDoctor->execute()) {
            return ["success" => true, "message" => "Doctor deleted successfully."];
        }

        return ["success" => false, "message" => $stmtDoctor->error];

    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}


    public function getPreviousAppointments($currentRole, $doc_id) {
        try {
            $currentRole = strtolower($currentRole);

            if (!in_array($currentRole, ['superadmin', 'doctor'])) {
                throw new Exception("Access Denied.");
            }

            $sql = "SELECT A.APPT_ID, A.APPT_DATE, A.APPT_TIME,
                           P.PAT_FIRST_NAME, P.PAT_LAST_NAME, S.SERV_NAME
                    FROM APPOINTMENT A
                    JOIN PATIENT P ON A.PAT_ID = P.PAT_ID
                    JOIN SERVICE S ON A.SERV_ID = S.SERV_ID
                    WHERE A.DOC_ID = ?
                    AND A.APPT_DATE < CURDATE()
                    ORDER BY A.APPT_DATE DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $doc_id);
            $stmt->execute();

            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            return [];
        }
    }

    public function getTodaysAppointments($currentRole, $doc_id) {
        try {
            $currentRole = strtolower($currentRole);

            if (!in_array($currentRole, ['superadmin', 'doctor'])) {
                throw new Exception("Access Denied.");
            }

            $sql = "SELECT A.APPT_ID, A.APPT_DATE, A.APPT_TIME,
                           P.PAT_FIRST_NAME, P.PAT_LAST_NAME, S.SERV_NAME
                    FROM APPOINTMENT A
                    JOIN PATIENT P ON A.PAT_ID = P.PAT_ID
                    JOIN SERVICE S ON A.SERV_ID = S.SERV_ID
                    WHERE A.DOC_ID = ?
                    AND A.APPT_DATE = CURDATE()
                    ORDER BY A.APPT_TIME ASC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $doc_id);
            $stmt->execute();

            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            return [];
        }
    }

    public function getFutureAppointments($currentRole, $doc_id, $currentDocId = null) {
        try {
            $currentRole = strtolower($currentRole);

            if (!in_array($currentRole, ['superadmin', 'doctor'])) {
                throw new Exception("Access Denied.");
            }

            $sql = "SELECT A.APPT_ID, A.APPT_DATE, A.APPT_TIME,
                           P.PAT_FIRST_NAME, P.PAT_LAST_NAME, S.SERV_NAME
                    FROM APPOINTMENT A
                    JOIN PATIENT P ON A.PAT_ID = P.PAT_ID
                    JOIN SERVICE S ON A.SERV_ID = S.SERV_ID
                    WHERE A.DOC_ID = ?
                    AND A.APPT_DATE > CURDATE()
                    ORDER BY A.APPT_DATE ASC";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $doc_id);
            $stmt->execute();

            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            return [];
        }
    }
}
?>
