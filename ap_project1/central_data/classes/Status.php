<?php 
require_once __DIR__ . "/../config/Database.php";

class Status {

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
            if (!in_array($currentRole, ['superadmin', 'staff'])) {
                throw new Exception("Access Denied: Only SuperAdmin or Staff can view status.");
            }

            $sql = "SELECT * FROM STATUS ORDER BY STATUS_ID ASC";
            $result = $this->conn->query($sql);

            $status = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $status[] = $row;
                }
            }
            return $status;
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function add_status($currentRole, $data) {
        try {
            if (!in_array($currentRole, ['superadmin', 'staff'])) {
                throw new Exception("Access Denied: Only SuperAdmin or Staff can add status.");
            }

            $stmt = $this->conn->prepare(
                "INSERT INTO STATUS (STATUS_NAME, STATUS_CREATED_AT, STATUS_UPDATED_AT)
                 VALUES (?, NOW(), NOW())"
            );
            $stmt->bind_param("s", $data['status_name']);
            if (!$stmt->execute()) {
                throw new Exception("Error adding status: " . $stmt->error);
            }
            $stmt->close();

            return "Status added successfully!";
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function search_status_by_name($currentRole, $name) {
    try {
        if (!in_array($currentRole, ['superadmin', 'staff'])) {
            throw new Exception("Access Denied: Only SuperAdmin or Staff can search status.");
        }

        $stmt = $this->conn->prepare(
            "SELECT * FROM STATUS WHERE STATUS_NAME LIKE ? ORDER BY STATUS_ID DESC"
        );
        $like = "%$name%";
        $stmt->bind_param("s", $like);
        $stmt->execute();
        $result = $stmt->get_result();

        $statuses = [];
        while ($row = $result->fetch_assoc()) {
            $statuses[] = $row;
        }

        $stmt->close();
        return $statuses;

    } catch (Exception $e) {
        return [];
    }
}

    public function update_status($currentRole, $id, $data) {
        try {
            if (!in_array($currentRole, ['superadmin', 'staff'])) {
                throw new Exception("Access Denied: Only SuperAdmin or Staff can update status.");
            }

            $stmt = $this->conn->prepare(
                "UPDATE STATUS
                 SET STATUS_NAME = ?, STATUS_UPDATED_AT = NOW()
                 WHERE STATUS_ID = ?"
            );
            $stmt->bind_param("si", $data['status_name'], $id);
            if (!$stmt->execute()) {
                throw new Exception("Error updating status: " . $stmt->error);
            }
            $stmt->close();

            return "Status updated successfully!";
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function delete_status($currentRole, $id) {
    try {
        if (!in_array($currentRole, ['superadmin'])) {
            return "Error: Access denied.";
        }

        $check = $this->conn->prepare("SELECT APPT_ID FROM APPOINTMENT WHERE STAT_ID = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            return "Status is still linked to an existing appointment. Please delete the appointment first.";
        }

        $stmt = $this->conn->prepare("DELETE FROM STATUS WHERE STATUS_ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return "Success";

    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}


    public function create_default() {
        try {
            $default = ['Scheduled', 'Completed', 'Cancelled'];

            foreach ($default as $status) {
                $stmt = $this->conn->prepare(
                    "INSERT INTO STATUS (STATUS_NAME, STATUS_CREATED_AT, STATUS_UPDATED_AT)
                     SELECT ?, NOW(), NOW()
                     WHERE NOT EXISTS (SELECT 1 FROM STATUS WHERE STATUS_NAME = ?)"
                );
                $stmt->bind_param("ss", $status, $status);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Exception $e) {
            die("Error creating default statuses: " . $e->getMessage());
        }
    }

    public function get_all_with_appointments($currentRole) {
    try {
        if (!in_array($currentRole, ['superadmin', 'staff'])) {
            throw new Exception("Access Denied: Only SuperAdmin or Staff can view statuses.");
        }

        $sql = "SELECT s.STATUS_ID, s.STATUS_NAME, s.STATUS_CREATED_AT, s.STATUS_UPDATED_AT, a.APPT_ID
                FROM STATUS s
                LEFT JOIN appointment a ON s.STATUS_ID = a.STAT_ID
                ORDER BY s.STATUS_ID DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $res ?: [];
    } catch (Exception $e) {
        return ["error" => $e->getMessage()];
    }
}

}
?>
