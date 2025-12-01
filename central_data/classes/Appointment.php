<?php
require_once __DIR__ . "/../config/Database.php";

class Appointment {
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConn();
    }

    public function generateAppointmentID() {
        $year = date("Y");
        $month = date("m");
        $prefix = "$year-$month-";

        $sql = "SELECT APPT_ID 
                FROM APPOINTMENT 
                WHERE APPT_ID LIKE CONCAT(?, '%')
                ORDER BY APPT_ID DESC 
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $prefix);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $lastID = $result->fetch_assoc()['APPT_ID'];
            $parts = explode("-", $lastID);
            $sequence = intval($parts[2]) + 1;
        } else {
            $sequence = 1;
        }

        $sequenceFormatted = str_pad($sequence, 7, "0", STR_PAD_LEFT);

        return "$year-$month-$sequenceFormatted";
    }

    public function get_all($currentRole) {
        try {
            $currentRole = strtolower($currentRole);

            if (!in_array($currentRole, ['superadmin', 'patient'])) {
                throw new Exception("Access Denied.");
            }

            $sql = "SELECT A.*, P.PAT_ID, D.DOC_ID, SE.SERV_NAME,
                    ST.STATUS_NAME FROM APPOINTMENT A
                    INNER JOIN PATIENT P ON A.PAT_ID = P.PAT_ID
                    INNER JOIN DOCTOR D ON A.DOC_ID = D.DOC_ID
                    INNER JOIN SERVICE SE ON A.SERV_ID = SE.SERV_ID
                    INNER JOIN STATUS ST ON A.STAT_ID = ST.STATUS_ID";
            $result = $this->conn->query($sql);

            $appointments = [];
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }

            return $appointments;
        } catch (Exception $e) {
            return "Error fetching appointments: " . $e->getMessage();
        }
    }
}

?>
