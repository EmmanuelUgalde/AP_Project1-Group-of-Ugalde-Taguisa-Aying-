<?php
session_start();
require_once __DIR__ . "/../../central_data/classes/Appointment.php";

if (!isset($_SESSION['role'])) {
    echo "<div style='
        display: flex; justify-content: center; align-items: center; 
        height: calc(100vh - 60px); flex-direction: column; text-align: center; 
        font-family: \"Port Lligat Slab\", serif; color: red; font-size: 80px;'>
        <h1>Access Denied</h1>
        <p>You must be logged in to access this page.</p>
    </div>";
    exit;
}

$currentRole = ucfirst(strtolower($_SESSION['role']));
if (!in_array($currentRole, ['Superadmin', 'Patient'])) {
    echo "<div style='
        display: flex; justify-content: center; align-items: center; 
        height: calc(100vh - 60px); flex-direction: column; text-align: center; 
        font-family: \"Port Lligat Slab\", serif; color: red; font-size: 50px;'>
        <h1>Access Denied</h1>
        <p>Only SuperAdmin or Patient can access this page.</p>
    </div>";
    exit;
}

$apptObj = new Appointment();
$conn = (new Database())->getConn();

if (isset($_POST['add_appt']) && in_array(strtolower($currentRole), ['superadmin', 'patient'])) {
    $pat_id = $_POST['pat_id'];
    $doc_id = $_POST['doc_id'];
    $serv_id = $_POST['serv_id'];
    $status_name = $_POST['status_name'];
    $appt_date = $_POST['appt_date'];
    $appt_time = $_POST['appt_time'];

    $check = $conn->prepare("SELECT PAT_LAST_NAME FROM PATIENT WHERE PAT_ID = ?");
    $check->bind_param("i", $pat_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        echo "<script>alert('Error: Patient does not exist.'); window.history.back();</script>";
        exit;
    }

    $check = $conn->prepare("SELECT DOC_LAST_NAME FROM DOCTOR WHERE DOC_ID = ?");
    $check->bind_param("i", $doc_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        echo "<script>alert('Error: Doctor does not exist.'); window.history.back();</script>";
        exit;
    }

    $stmt = $conn->prepare("SELECT STATUS_ID FROM STATUS WHERE STATUS_NAME = ?");
    $stmt->bind_param("s", $status_name);
    $stmt->execute();
    $stmt->bind_result($stat_id);
    if (!$stmt->fetch()) {
        $stmt->close();
        $insert = $conn->prepare("INSERT INTO STATUS (STATUS_NAME) VALUES (?)");
        $insert->bind_param("s", $status_name);
        $insert->execute();
        $stat_id = $insert->insert_id;
        $insert->close();
    } else {
        $stmt->close();
    }

    $appt_id = $apptObj->generateAppointmentID();

    $stmt = $conn->prepare("INSERT INTO APPOINTMENT 
        (APPT_ID, APPT_DATE, APPT_TIME, APPT_CREATED_AT, APPT_UPDATED_AT, PAT_ID, DOC_ID, SERV_ID, STAT_ID)
        VALUES (?, ?, ?, NOW(), NOW(), ?, ?, ?, ?)");
    $stmt->bind_param("sssiiii", $appt_id, $appt_date, $appt_time, $pat_id, $doc_id, $serv_id, $stat_id);

    if ($stmt->execute()) {
        $stmt->close();
        echo "<script>alert('Appointment created! ID: $appt_id'); window.location.href='appointment.php?added=1&id=$appt_id';</script>";
        exit;
    } else {
        die("Error adding appointment: " . $stmt->error);
    }
}

if (isset($_POST['update_appt']) && in_array(strtolower($currentRole), ['superadmin', 'patient'])) {
    $appt_id = $_POST['appt_id'];
    $pat_id = $_POST['pat_id'];
    $doc_id = $_POST['doc_id'];
    $serv_id = $_POST['serv_id'];
    $status_name = $_POST['status_name'];
    $appt_date = $_POST['appt_date'];
    $appt_time = $_POST['appt_time'];

    $check = $conn->prepare("SELECT PAT_ID FROM PATIENT WHERE PAT_ID = ?");
    $check->bind_param("i", $pat_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        echo "<script>alert('Error: Patient does not exist.'); window.history.back();</script>";
        exit;
    }

    $check = $conn->prepare("SELECT DOC_ID FROM DOCTOR WHERE DOC_ID = ?");
    $check->bind_param("i", $doc_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        echo "<script>alert('Error: Doctor does not exist.'); window.history.back();</script>";
        exit;
    }

    $stmt = $conn->prepare("SELECT STATUS_ID FROM STATUS WHERE STATUS_NAME = ?");
    $stmt->bind_param("s", $status_name);
    $stmt->execute();
    $stmt->bind_result($stat_id);
    if (!$stmt->fetch()) {
        $stmt->close();
        $insert = $conn->prepare("INSERT INTO STATUS (STATUS_NAME) VALUES (?)");
        $insert->bind_param("s", $status_name);
        $insert->execute();
        $stat_id = $insert->insert_id;
        $insert->close();
    } else {
        $stmt->close();
    }

    $stmt = $conn->prepare("
        UPDATE APPOINTMENT 
        SET APPT_DATE = ?, APPT_TIME = ?, PAT_ID = ?, DOC_ID = ?, SERV_ID = ?, STAT_ID = ?, APPT_UPDATED_AT = NOW()
        WHERE APPT_ID = ?
    ");
    $stmt->bind_param("ssiiiss", $appt_date, $appt_time, $pat_id, $doc_id, $serv_id, $stat_id, $appt_id);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: appointment.php?updated=1&id=$appt_id");
        exit;
    } else {
        die("Error updating appointment: " . $stmt->error);
    }
}

if (isset($_POST['delete_appt']) && in_array(strtolower($currentRole), ['superadmin', 'patient'])) {
    $id = $_POST['delete_appt'];

    $stmt = $conn->prepare("DELETE FROM APPOINTMENT WHERE APPT_ID = ?");
    $stmt->bind_param("s", $id);
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: appointment.php?deleted=1");
        exit;
    } else {
        die("Error deleting appointment: " . $stmt->error);
    }
}

$search = $_GET['search'] ?? '';
if ($search) {
    $like = "%$search%";
    $stmt = $conn->prepare("
        SELECT A.APPT_ID, A.APPT_DATE, A.APPT_TIME,
               A.APPT_CREATED_AT, A.APPT_UPDATED_AT,
               A.PAT_ID, A.DOC_ID,
               S.SERV_NAME, ST.STATUS_NAME,
               P.PAT_FIRST_NAME, P.PAT_LAST_NAME,
               D.DOC_FIRST_NAME, D.DOC_LAST_NAME
        FROM APPOINTMENT A
        LEFT JOIN SERVICE S ON A.SERV_ID = S.SERV_ID
        LEFT JOIN STATUS ST ON A.STAT_ID = ST.STATUS_ID
        LEFT JOIN PATIENT P ON A.PAT_ID = P.PAT_ID
        LEFT JOIN DOCTOR D ON A.DOC_ID = D.DOC_ID
        WHERE A.APPT_ID LIKE ?
        ORDER BY A.APPT_DATE ASC
    ");
    $stmt->bind_param("s", $like);
} else {
    $stmt = $conn->prepare("
        SELECT A.APPT_ID, A.APPT_DATE, A.APPT_TIME,
               A.APPT_CREATED_AT, A.APPT_UPDATED_AT,
               A.PAT_ID, A.DOC_ID,
               S.SERV_NAME, ST.STATUS_NAME,
               P.PAT_FIRST_NAME, P.PAT_LAST_NAME,
               D.DOC_FIRST_NAME, D.DOC_LAST_NAME
        FROM APPOINTMENT A
        LEFT JOIN SERVICE S ON A.SERV_ID = S.SERV_ID
        LEFT JOIN STATUS ST ON A.STAT_ID = ST.STATUS_ID
        LEFT JOIN PATIENT P ON A.PAT_ID = P.PAT_ID
        LEFT JOIN DOCTOR D ON A.DOC_ID = D.DOC_ID
        ORDER BY A.APPT_DATE ASC
    ");
}
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointments - Urban Medical Hospital</title>
<style>
body {
    margin: 0;
    font-family: 'Port Lligat Slab', serif;
    background-color: #FFF9F9;
}
.container-page {
    padding: 70px;
}
.container-page h1 {
    font-size: 80px;
    color: black;
    margin-top: 120px;
    text-align: left;
}
.action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
}
.search-bar input {
    padding: 15px 12px;
    width: 250px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
.btn {
    padding: 8px 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}
.btn-add { background-color: #28a745; color: white; width: 150px; height: 60px; font-size: 20px; }
.btn-update { background-color: #007bff; color: white; width: 150px; height: 60px; font-size: 20px; }
.btn-delete { background-color: #dc3545; color: white; width: 150px; height: 50px; font-size: 20px; }
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
    background: white;
}
th, td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    font-size: 20px;
}
th {
    background-color: #f1f1f1;
}
.message {
    margin: 10px 0;
    color: green;
    font-weight: bold;
}

.container-page .search-bar {
    margin-left: -5px;
    padding: 40px 40px;
}

.container-page .search-bar input {
    padding: 16px 12px;
    width: 250px;
    height: 50px;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-left: -5px;
    margin-top: 5px;
}

.container-page .search-bar form {
    display: flex;
    gap: 10px;
}

.container-page .search-bar button {
    width: 100px;
}

.container-page .search-bar button:hover {
    background-color: #66b3ff;
    transition: background-color 0.3s ease;
}

.add-form-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, 30%) scale(0.9);
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    width: 400px;
    max-width: 90%;
    z-index: 2000;
    display: none;
    opacity: 0;
    transition: all 0.4s ease;
}

.add-form-container.show {
    display: block;
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
}

.add-form-container.hiding {
    opacity: 0;
    transform: translate(-50%, 30%) scale(0.9);
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}

.overlay.show {
    opacity: 1;
    pointer-events: all;
}

.add-form-container h2 {
    text-align: center;
    margin-bottom: 20px;
}

.add-form-container label {
    display: block;
    font-weight: bold;
    margin-top: 10px;
}

.add-form-container input,
.add-form-container select {
    width: 100%;
    padding: 12px 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    font-family: 'Port Lligat Slab', serif;
    background-color: white;
    cursor: pointer;
    height: 45px;
    box-sizing: border-box;
}

.add-form-container select:hover {
    border-color: #007bff;
}

.add-form-container select:focus,
.add-form-container input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
}

.add-form-container select option {
    padding: 10px;
    font-size: 15px;
}

.form-actions {
    display: flex; 
    justify-content: space-between; 
    margin-top: 15px; 
    gap: 20px;
}

footer {
    width: 100%;
    background-color: #0041c7;
    color: white;
    padding: 30px 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 199px;
}

footer h2 {
    font-family: var(--brand-serif);
    font-weight: 400;
    letter-spacing: 0.5px;
}
</style>
</head>
<body>
<?php include "../includes/header.php"; ?>
<div class="container-page">
    <h1>Appointments</h1>

    <?php if (isset($_GET['added'])) echo "<div class='message'>Appointment added successfully!</div>"; ?>
    <?php if (isset($_GET['updated'])) echo "<div class='message'>Appointment updated successfully!</div>"; ?>
    <?php if (isset($_GET['deleted'])) echo "<div class='message'>Appointment deleted successfully!</div>"; ?>

    <div class="action-bar">
        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="Search appointment ID..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-update" type="submit">Search</button>
            </form>
        </div>

        <?php if ($currentRole === 'Superadmin' || $currentRole === 'Patient'): ?>
            <button class="btn btn-add" onclick="showAddForm()" style="width: 160px;">Add Appointment</button>
        <?php endif; ?>
    </div>

    <table>
        <tr>
            <th>Appointment ID</th>
            <th>Date</th>
            <th>Time</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Patient ID</th>
            <th>Doctor</th>
            <th>Service</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if ($appointments && $appointments->num_rows): ?>
            <?php while ($r = $appointments->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($r['APPT_ID']) ?></td>
                    <td><?= htmlspecialchars($r['APPT_DATE']) ?></td>
                    <td><?= htmlspecialchars($r['APPT_TIME']) ?></td>
                    <td><?= htmlspecialchars($r['APPT_CREATED_AT']) ?></td>
                    <td><?= htmlspecialchars($r['APPT_UPDATED_AT']) ?></td>
                    <td><?= htmlspecialchars($r['PAT_ID']) ?></td>
                    <td><?= htmlspecialchars('Dr. '.$r['DOC_LAST_NAME']) ?></td>
                    <td><?= htmlspecialchars($r['SERV_NAME']) ?></td>
                    <td><?= htmlspecialchars($r['STATUS_NAME']) ?></td>
                    <td>
                        <button class="btn btn-update"
                            onclick="showUpdateForm(
                                '<?= $r['APPT_ID'] ?>',
                                '<?= $r['APPT_DATE'] ?>',
                                '<?= $r['APPT_TIME'] ?>',
                                '<?= $r['PAT_ID'] ?>',
                                '<?= $r['DOC_ID'] ?>',
                                '<?= htmlspecialchars($r['SERV_NAME'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($r['STATUS_NAME'], ENT_QUOTES) ?>'
                            )" style="height: 40px; width: 100px;">Edit</button>

                        <?php if ($currentRole === 'Superadmin' || $currentRole === 'Patient'): ?>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure to cancel this appointment?');">
                                <input type="hidden" name="delete_appt" value="<?= $r['APPT_ID'] ?>">
                                <button class="btn btn-delete" type="submit" style="height: 40px; width: 100px;">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="10">No appointments found.</td></tr>
        <?php endif; ?>
    </table>
</div>

<!-- ADD APPOINTMENT FORM -->
<div id="addForm" class="add-form-container">
    <form method="POST" action="">
        <h2>Add Appointment</h2>

        <label>Date:</label>
        <input type="date" name="appt_date" required>

        <label>Time:</label>
        <input type="time" name="appt_time" required>

        <label>Patient ID:</label>
        <input type="number" name="pat_id" required>

        <label>Service:</label>
        <select name="serv_id" id="add_serv" required>
            <option value="">-- Select Service --</option>
        </select>

        <label>Doctor:</label>
        <select name="doc_id" id="add_doc" required>
            <option value="">-- Select Doctor --</option>
        </select>

        <label>Status:</label>
        <select name="status_name" required>
            <option value="Scheduled">Scheduled</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
        </select>

        <div class="form-actions">
            <button name="add_appt" class="btn btn-add" type="submit">Save</button>
            <button type="button" class="btn btn-delete" onclick="hideAddForm()" style="height: 60px;">Cancel</button>
        </div>
    </form>
</div>

<!-- UPDATE APPOINTMENT FORM -->
<div id="updateForm" class="add-form-container">
    <form method="POST" action="">
        <h2>Update Appointment</h2>

        <input type="hidden" name="appt_id" id="u_id">

        <label>Date:</label>
        <input type="date" name="appt_date" id="u_date" required>

        <label>Time:</label>
        <input type="time" name="appt_time" id="u_time" required>

        <label>Patient ID:</label>
        <input type="number" name="pat_id" id="u_pat" required>

        <label>Service:</label>
        <select name="serv_id" id="u_serv" required>
            <option value="">-- Select Service --</option>
        </select>

        <label>Doctor:</label>
        <select name="doc_id" id="u_doc" required>
            <option value="">-- Select Doctor --</option>
        </select>

        <label>Status:</label>
        <select name="status_name" id="u_status" required>
            <option value="Scheduled">Scheduled</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
        </select>

        <div class="form-actions">
            <button name="update_appt" class="btn btn-update" type="submit">Update</button>
            <button type="button" class="btn btn-delete" onclick="hideUpdateForm()" style="height: 60px;">Cancel</button>
        </div>
    </form>
</div>

<div id="overlay" class="overlay" onclick="hideAddForm(); hideUpdateForm();"></div>
<footer><h2>&copy; 2025 Urban Medical Hospital by Hollys Group. All Rights Reserved.</h2></footer>

<script>
// Load services when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadServices('add_serv');
    loadServices('u_serv');
});

function showAddForm() {
    document.getElementById("addForm").classList.add("show");
    document.getElementById("overlay").classList.add("show");
}

function hideAddForm() {
    let box = document.getElementById("addForm");
    box.classList.add("hiding");
    setTimeout(() => {
        box.classList.remove("show", "hiding");
    }, 300);
    document.getElementById("overlay").classList.remove("show");
}

function showUpdateForm(id, date, time, pat, doc, serv, status) {
    document.getElementById("u_id").value = id;
    document.getElementById("u_date").value = date;
    document.getElementById("u_time").value = time;
    document.getElementById("u_pat").value = pat;
    document.getElementById("u_status").value = status;

    // Load services first, then set the selected service and load doctors
    loadServices('u_serv', function() {
        // Find service ID by name
        const serviceDropdown = document.getElementById("u_serv");
        for (let option of serviceDropdown.options) {
            if (option.text === serv) {
                option.selected = true;
                loadDoctors(option.value, "u_doc", doc);
                break;
            }
        }
    });

    document.getElementById("updateForm").classList.add("show");
    document.getElementById("overlay").classList.add("show");
}

function hideUpdateForm() {
    let box = document.getElementById("updateForm");
    box.classList.add("hiding");
    setTimeout(() => {
        box.classList.remove("show", "hiding");
    }, 300);
    document.getElementById("overlay").classList.remove("show");
}

// Event listeners for service dropdown changes
document.getElementById("add_serv").addEventListener("change", function () {
    loadDoctors(this.value, "add_doc");
});

document.getElementById("u_serv").addEventListener("change", function () {
    loadDoctors(this.value, "u_doc");
});

// Function to load services from service.php
function loadServices(dropdownID, callback) {
    fetch("service.php")
        .then(response => response.json())
        .then(data => {
            const dropdown = document.getElementById(dropdownID);
            dropdown.innerHTML = '<option value="">-- Select Service --</option>';

            data.forEach(service => {
                let opt = document.createElement("option");
                opt.value = service.id;
                opt.textContent = service.name;
                dropdown.appendChild(opt);
            });

            if (callback) callback();
        })
        .catch(error => {
            console.error('Error loading services:', error);
        });
}

// Function to load doctors from doctor.php based on selected service
function loadDoctors(serviceId, dropdownID, selectedDoctor = null) {
    if (!serviceId) {
        const dropdown = document.getElementById(dropdownID);
        dropdown.innerHTML = '<option value="">-- Select Doctor --</option>';
        return;
    }

    fetch("doctor.php?service_id=" + encodeURIComponent(serviceId))
        .then(response => response.json())
        .then(data => {
            const dropdown = document.getElementById(dropdownID);
            dropdown.innerHTML = '<option value="">-- Select Doctor --</option>';

            data.forEach(doc => {
                let opt = document.createElement("option");
                opt.value = doc.id;
                opt.textContent = doc.name;

                if (selectedDoctor == doc.id) opt.selected = true;

                dropdown.appendChild(opt);
            });
        })
        .catch(error => {
            console.error('Error loading doctors:', error);
        });
}
</script>

</body>
</html>