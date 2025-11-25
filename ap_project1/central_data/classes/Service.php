<?php 
require_once __DIR__ . "/../config/Database.php";

class Service {

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

    public function get_all ($currentRole) {
        try {
            if (!in_array($currentRole, ['superadmin', 'staff'])) {
                return "Access Denied: Only SuperAdmin or Staff can view services.";
            }

            $sql = "SELECT * FROM SERVICE";
            $result = $this->conn->query($sql);

            $services = [];

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $services[] = $row;
                }
            }

            return $services;
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
           return "Error fetching staff: " . $e->getMessage();
        }
    }

    public function add_service ($currentRole, $data) {
    try {
        if (!in_array($currentRole, ['superadmin', 'staff'])) {
            return ["success" => false, "message" => "Access denied."];
        }

        $stmt = $this->conn->prepare("
            INSERT INTO SERVICE 
            (SERV_NAME, SERV_DESCRIPTION, SERV_PRICE, SERV_CREATED_AT, SERV_UPDATED_AT)
            VALUES (?, ?, ?, NOW(), NOW())
        ");

        if (!$stmt) {
            return ["success" => false, "message" => $this->conn->error];
        }

        $stmt->bind_param("ssd",
            $data['serv_name'],
            $data['serv_description'],
            $data['serv_price']
        );

        if ($stmt->execute()) {
            return ["success" => true];
        } else {
            return ["success" => false, "message" => $stmt->error];
        }

    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}


    public function update_service ($currentRole, $id, $data) {
        try {
            if (!in_array($currentRole, ['superadmin', 'staff'])) {
                return "Access Denied: Only SuperAdmin or Staff can update services.";
            }

            $conn = $this->conn;

            $stmt = $conn->prepare("UPDATE SERVICE SET SERV_NAME = ?, SERV_DESCRIPTION = ?, SERV_PRICE = ?, SERV_UPDATED_AT = NOW() WHERE SERV_ID = ?");
            $stmt->bind_param("ssdi", $data['serv_name'], $data['serv_description'], $data['serv_price'], $id);

            if ($stmt->execute()) {
                return "Service updated successfully!";
            } else {
                return "Error updating service: " . $stmt->error;
            }

            $stmt->error();
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
            return "Error updating service:" . $e->getMessage();
        }
    }

    public function appointments_view($currentRole, $serv_id) {
        try {
            if (!in_array($currentRole, ['superadmin', 'staff'])) {
                return "Access Denied: Only SuperAdmin or Staff can view appointments.";
            }

            $conn = $this->conn;

            $stmt = $conn->prepare("SELECT p.PAT_ID, d.DOC_ID, se.SERV_ID, st.STAT_ID, a.APPT_ID, a.APPT_DATE, a.APPT_TIME, 
                a.APPT_CREATED_AT, a.APPT_UPDATED_AT
                FROM APPOINTMENT a
                INNER JOIN PATIENT p ON a.PAT_ID = p.PAT_ID
                INNER JOIN DOCTOR d ON a.DOC_ID = d.DOC_ID
                INNER JOIN SERVICE se ON a.SERV_ID = se.SERV_ID
                INNER JOIN STATUS st ON a.STAT_ID = st.STAT_ID
                WHERE se.SERV_ID = ?
                ORDER BY a.APPT_DATE, a.APPT_TIME");
            
            $stmt->bind_param("i", $serv_id);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $appointment = [];
                
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $appointment[] = $row;
                    }
                }

                $stmt->close();
                return $appointment;
            } else {
                return "Error viewing appointments : " . $stmt->error;
            }
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
                return "Error fetching appointments: " . $e->getMessage();
        }
    }

    public function delete_service($currentRole, $id) {
        try {
            if (!in_array($currentRole, ['superadmin'])) {
                return "Access Denied: Only SuperAdmin can delete services.";
            }

            $conn = $this->conn;

            $stmt = $conn->prepare("DELETE FROM SERVICE WHERE SERV_ID = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                return "Service deleted successfully!";
            } else {
                return "Error deleting service: " . $stmt->error;
            }

            $stmt->close();
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
            return "Error deleting service: " . $e->getMessage();
        }
    }
}

?>