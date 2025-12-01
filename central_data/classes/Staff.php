<?php 
require_once __DIR__ . "/../config/Database.php";

class Staff {
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
            if (!in_array(strtolower($currentRole), ['superadmin', 'staff'])) {
                return "Access Denied: Only SuperAdmin or Staff can view staff.";
            }

        $sql = "SELECT * FROM STAFF";
        $result = $this->conn->query($sql);

        $staff = [];

        if ($result) {
            while($row = $result->fetch_assoc()) {
                $staff[] = $row;
            }
        }

        return $staff; }
        catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
            return "Error fetching staff: " . $e->getMessage();
        }
    }

    public function add_staff($currentRole, $data) {
        try {
             if (!in_array(strtolower($currentRole), ['superadmin', 'staff'])) {
                return "Access Denied: Only SuperAdmin or Staff can view staff.";
            }

        $conn = $this->conn;
        $stmt = $conn->prepare("INSERT INTO STAFF (STAFF_FIRST_NAME, STAFF_LAST_NAME, STAFF_MIDDLE_INIT,
                STAFF_CONTACT_NUM, STAFF_EMAIL, STAFF_CREATED_AT, STAFF_UPDATED_AT) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sssss", $data['staff_first_name'], $data['staff_last_name'], $data['staff_middle_init'],
                $data['staff_contact_num'], $data['staff_email']);
        
        if($stmt->execute()) {
            return "New staff added successfully!";
        } else {
            return "Error: " . $stmt->error;
        }

        $stmt->close(); }
        catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
            return "Error adding staff: " . $e->getMessage();
        }
    }

    public function search_staff($currentRole, $keyword = null) {
    try {
        if (!in_array(strtolower($currentRole), ['superadmin', 'staff'])) {
            return "Access Denied: Only SuperAdmin or Staff can view staff.";
        }

        $conn = $this->conn;
        $staff = [];

        if ($keyword !== null && $keyword !== '') {
            $keywordLike = "%$keyword%";
            $stmt = $conn->prepare("SELECT * FROM STAFF 
                WHERE CAST(STAFF_ID AS CHAR) LIKE ? 
                OR STAFF_FIRST_NAME LIKE ? 
                OR STAFF_LAST_NAME LIKE ? 
                OR STAFF_EMAIL LIKE ?");
            $stmt->bind_param("ssss", $keywordLike, $keywordLike, $keywordLike, $keywordLike);
        } else {
            $stmt = $conn->prepare("SELECT * FROM STAFF");
        }

        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $staff[] = $row;
        }

        $stmt->close();
        return $staff;
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
    } catch (Exception $e) {
        return "Error searching staff: " . $e->getMessage();
    }
}

    public function update_staff($currentRole, $id, $data) {
    try {
         if (!in_array(strtolower($currentRole), ['superadmin', 'staff'])) {
                return "Access Denied: Only SuperAdmin or Staff can view staff.";
            }

        $conn = $this->conn;

        $stmt = $conn->prepare("UPDATE STAFF 
            SET STAFF_FIRST_NAME = ?, STAFF_LAST_NAME = ?, STAFF_MIDDLE_INIT = ?, 
                STAFF_CONTACT_NUM = ?, STAFF_EMAIL = ?, STAFF_UPDATED_AT = NOW() 
            WHERE STAFF_ID = ?");

        $stmt->bind_param("sssssi", $data['staff_first_name'], $data['staff_last_name'], $data['staff_middle_init'],
            $data['staff_contact_num'], $data['staff_email'], $id);

        if ($stmt->execute()) {
            return "Updated staff data successfully!";
        } else {
            return "Update Error: " . $stmt->error;
        }

        $stmt->close();
    } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
        return "Error updating staff: " . $e->getMessage();
    }
}

    public function delete_staff($currentRole, $id) {
        try {
            if (!in_array(strtolower($currentRole), ['superadmin'])) {
                return "Access Denied: Only SuperAdmin or Staff can view staff.";
            }

            $conn = $this->conn;

            $stmt = $conn->prepare("DELETE FROM STAFF WHERE STAFF_ID = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                return "Staff deleted successfully!";
            } else {
                return "Error deleting staff: " . $stmt->error;
            }

            $stmt->close();
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database Error: " . $e->getMessage()];
        } catch (Exception $e) {
            return "Error deleting staff: " . $e->getMessage();
        }
    }

}
?>