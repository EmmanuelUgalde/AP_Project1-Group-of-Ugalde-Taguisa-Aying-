<?php 
require_once __DIR__ . "/../config/Database.php";

class Schedule {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConn();
    }

    public function add_sched($currentRole, $data) {
    try {
        if (!in_array($currentRole, ['superadmin', 'doctor'])) {
            return ['success' => false, 'message' => "Access Denied"];
        }

        $stmt = $this->conn->prepare("
            INSERT INTO SCHEDULE (SCHED_DAYS, SCHED_START_TIME, SCHED_END_TIME, DOC_ID, SCHED_CREATED_AT, SCHED_UPDATED_AT)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->bind_param("sssi",
            $data['sched_days'],
            $data['sched_start'],
            $data['sched_end'],
            $data['doc_id']
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => "Schedule added successfully"];
        }

        return ['success' => false, 'message' => $stmt->error];

    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}


    public function get_all($currentRole) {
        try {
            if (!in_array($currentRole, ['superadmin', 'doctor'])) {
                throw new Exception("Access Denied: Only SuperAdmin and Doctors can view schedule.");
            }
            $conn = $this->conn;

            $sql = "SELECT * FROM SCHEDULE ORDER BY SCHED_ID ASC";

            $result = $this->conn->query($sql);
            $schedule = [];

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $schedule[] = $row;
                }
            }

            return $schedule;
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
            return "Error adding payment method: " . $e->getMessage();
        }
    }

    public function get_today($currentRole, $docId) {
        try {
        if (!in_array($currentRole, ['superadmin', 'doctor'])) {
                throw new Exception("Access Denied: Only SuperAdmin and Doctors can view schedule.");
            }

            $sql = "SELECT A.APPT_ID, A.APPT_DATE, A.APPT_TIME,
                           P.PAT_FIRST_NAME, P.PAT_LAST_NAME, S.SERV_NAME
                    FROM APPOINTMENT A
                    INNER JOIN PATIENT P ON A.PAT_ID = P.PAT_ID
                    INNER JOIN SERVICE S ON A.SERV_ID = S.SERV_ID
                    WHERE A.DOC_ID = ?
                    AND A.APPT_DATE = CURDATE()
                    ORDER BY A.APPT_TIME ASC";

            $stmt = $this->conn->prepare($sql);

            if($stmt->execute([$docId])) {
                return "Doctor fetched successfully!";
            } else {
                return "Error fetching doctor: " . $stmt->error;
            }; 

            $stmt->close(); 
        } catch (Exception $e) {
            return "Error fetching schedule: " . $e->getMessage();
        }
    }

    public function search_sched($currentRole, $id = null) {
        try {
            if (!in_array($currentRole, ['superadmin', 'doctor'])) {
                throw new Exception("Access Denied: Only SuperAdmin and Doctors can view schedule.");
            }

            $conn = $this->conn;

            $stmt = $conn->prepare("SELECT * FROM SCHEDULE WHERE SCHED_ID = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $schedule = $result->fetch_assoc();
            } else {
                return "Error fetching medical record: " . $stmt->error;
            }

            $stmt->close();
            return $schedule;
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
            return "Error fetching medical record: " . $e->getMessage();
        }
    }

    public function update_sched($currentRole, $id, $data) {
    try {
        if (!in_array($currentRole, ['superadmin', 'doctor'])) {
            return ['success' => false, 'message' => "Access Denied"];
        }

        $stmt = $this->conn->prepare("
            UPDATE SCHEDULE 
            SET SCHED_DAYS=?, SCHED_START_TIME=?, SCHED_END_TIME=?, DOC_ID=?, SCHED_UPDATED_AT=NOW()
            WHERE SCHED_ID=?
        ");

        $stmt->bind_param("sssii",
            $data['sched_days'],
            $data['sched_start'],
            $data['sched_end'],
            $data['doc_id'],
            $id
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => "Schedule updated successfully"];
        }

        return ['success' => false, 'message' => $stmt->error];

    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}


    public function delete_sched($currentRole, $id) {
    try {
        if (!in_array($currentRole, ['superadmin', 'doctor'])) {
            return ['success' => false, 'message' => "Access Denied"];
        }

        $stmt = $this->conn->prepare("DELETE FROM SCHEDULE WHERE SCHED_ID=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => "Schedule deleted successfully"];
        }

        return ['success' => false, 'message' => $stmt->error];

    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
};

?>