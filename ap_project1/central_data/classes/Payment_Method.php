<?php 
require_once __DIR__ . "/../config/Database.php";

class Pay_Method {
    private $conn;

    public function __construct() {
        try {
            $database = new Database();
            $this->conn = $database->getConn();
        } catch (Exception $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function get_all($currentRole) {
        if (!in_array(strtolower($currentRole), ['superadmin', 'staff'])) {
            return [];
        }

        $sql = "SELECT * FROM PAYMENT_METHOD ORDER BY PYMT_METH_ID ASC";
        $result = $this->conn->query($sql);

        $pay_meth = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $pay_meth[] = $row;
            }
        }
        return $pay_meth;
    }

    public function add_paymeth($currentRole, $data) {
        if (!in_array(strtolower($currentRole), ['superadmin', 'staff'])) {
            return "Access Denied!";
        }

        $stmt = $this->conn->prepare("INSERT INTO PAYMENT_METHOD (PYMT_METH_NAME, PYMT_METH_CREATED_AT, PYMT_METH_UPDATED_AT) VALUES (?, NOW(), NOW())");
        $stmt->bind_param("s", $data['pymt_meth_name']);

        if ($stmt->execute()) {
            $stmt->close();
            return "Payment Method added successfully!";
        } else {
            $stmt->close();
            return "Error adding payment method: " . $stmt->error;
        }
    }

    public function update_paymeth($currentRole, $id, $data) {
        if (!in_array(strtolower($currentRole), ['superadmin', 'staff'])) {
            return "Access Denied!";
        }

        $stmt = $this->conn->prepare("UPDATE PAYMENT_METHOD SET PYMT_METH_NAME = ?, PYMT_METH_UPDATED_AT = NOW() WHERE PYMT_METH_ID = ?");
        $stmt->bind_param("si", $data['pymt_meth_name'], $id);

        if ($stmt->execute()) {
            $stmt->close();
            return "Payment Method updated successfully!";
        } else {
            $stmt->close();
            return "Error updating payment method: " . $stmt->error;
        }
    }

    public function delete_paymeth($currentRole, $id) {
        if (strtolower($currentRole) !== 'superadmin') {
            return "Access Denied!";
        }

        $stmt = $this->conn->prepare("DELETE FROM PAYMENT_METHOD WHERE PYMT_METH_ID = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            return "Payment Method deleted successfully!";
        } else {
            $stmt->close();
            return "Error deleting payment method: " . $stmt->error;
        }
    }

    public function search_pay_meth($currentRole, $keyword) {
        if (!in_array(strtolower($currentRole), ['superadmin', 'staff'])) {
            return [];
        }

        $keyword = "%".$keyword."%";
        $stmt = $this->conn->prepare("SELECT * FROM PAYMENT_METHOD WHERE PYMT_METH_NAME LIKE ? ORDER BY PYMT_METH_ID ASC");
        $stmt->bind_param("s", $keyword);
        $stmt->execute();
        $result = $stmt->get_result();

        $pay_meth = [];
        while ($row = $result->fetch_assoc()) {
            $pay_meth[] = $row;
        }

        $stmt->close();
        return $pay_meth;
    }
}
?>
