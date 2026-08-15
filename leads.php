<?php
// Database connection
$host = 'localhost';
$dbname = 'u318220648_kautilyadb';
$username = 'u318220648_kautilyadb';
$password = 'Nirmaan@1234';

date_default_timezone_set('Asia/Kolkata');
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

function clean_input($data)
{
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['fetch_projects'])) {
    $query = "SELECT id, name FROM tblprojects";
    $result = $conn->query($query);

    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
    echo json_encode($projects);
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['fetch_status'])) {
    $query = "SELECT id, name FROM tblleads_status";
    $result = $conn->query($query);

    $status = [];
    while ($row = $result->fetch_assoc()) {
        $status[] = $row;
    }
    echo json_encode($status);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['fetch_sources'])) {
    $query = "SELECT id, name FROM tblleads_sources";
    $result = $conn->query($query);

    $sources = [];
    while ($row = $result->fetch_assoc()) {
        $sources[] = $row;
    }
    echo json_encode($sources);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['fetch_assigned'])) {
    $query = "SELECT staffid, CONCAT(firstname, ' ', lastname) AS fullname FROM tblstaff";
    $result = $conn->query($query);

    $assigned = [];
    while ($row = $result->fetch_assoc()) {
        $assigned[] = $row;
    }
    echo json_encode($assigned);
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    // header('Content-Type: application/json'); 


    $name = clean_input($_POST['name']);
    $phonenumber = clean_input($_POST['phonenumber']);
    $email = filter_var(clean_input($_POST['email']), FILTER_VALIDATE_EMAIL) ? $_POST['email'] : null;
    $budget = is_numeric($_POST['lead_value']) ? clean_input($_POST['lead_value']) : null;
    $broker = clean_input($_POST['broker']);
    $contact_details = clean_input($_POST['contact_details']);
    $projects = clean_input($_POST['projects']);
    $assigned = !empty($_POST['assigned']) ? clean_input($_POST['assigned']) : null;
    $status = clean_input($_POST['status']);
    $source = clean_input($_POST['source']);
    $firm = clean_input($_POST['firm']);
    $address = clean_input($_POST['address']);
    $description = clean_input($_POST['description']);
    $date = date('Y-m-d H:i:s');
    $addedfrom = 1;

    // Validation
    if (empty($name) || empty($phonenumber) || empty($status) || empty($source)) {
        echo json_encode(["status" => "error", "message" => "Required fields missing!"]);
        exit;
    }
    if (!preg_match("/^\d+$/", $phonenumber)) {
        echo json_encode(["status" => "error", "message" => "Invalid phone number!"]);
        exit;
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO tblleads (name, phonenumber, email, lead_value, broker, contact_details, projects, assigned, status, source, firm, address, description, dateadded, addedfrom) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssssssisssssssi", $name, $phonenumber, $email, $budget, $broker, $contact_details, $projects, $assigned, $status, $source, $firm, $address, $description, $date, $addedfrom);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Lead added successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Lead</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

<body>

    <div class="container">
        <div class="panel_s">
            <div class="panel-body">
                <div class="row">
                    <h2>Add New Lead</h2>
                    <div id="formMessage" style="display: none;"></div>
                    <form action="" id="leadForm" method="post">

                        <input type="hidden" name="csrf_token_name" value="b4c50b9429e451a742fc6b51cf675800">
                        <input type="hidden" name="ajax" value="1">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group col-md-6">
                                    <label for="name">* Name</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="phonenumber">Phone</label>
                                    <input type="text" id="phonenumber" name="phonenumber" class="form-control" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control">
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label for="budget">Budget</label>
                                    <input type="number" id="budget" name="lead_value" class="form-control">
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label for="broker">Broker Name</label>
                                    <input type="text" id="broker" name="broker" class="form-control">
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label for="contact_details">Broker Contact Details</label>
                                    <input type="text" id="contact_details" name="contact_details" class="form-control">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="projects">Projects</label>
                                    <select id="projects" name="projects" class="form-control">
                                        <option value="">Loading projects...</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label for="assigned">Assigned</label>
                                    <select id="assigned" name="assigned" class="form-control">
                                        <option value="">Nothing selected</option>
                                        <option value="3">Vrundan Nakrani</option>
                                        <option value="11">Vedant Amin</option>
                                        <option value="2" selected>Vatsal Kamdar</option>
                                        <option value="1">Admin N360</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="form-group col-md-6">
                                    <label for="status">* Status</label>
                                    <select id="status" name="status" class="form-control" required>
                                        <option value="">Loading status...</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="source">* How did you hear about us?</label>
                                    <select id="source" name="source" class="form-control" required>
                                        <option value="">Loading source...</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label for="firm">Firm Name</label>
                                    <input type="text" id="firm" name="firm" class="form-control">
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label for="address">Current Residence</label>
                                    <textarea id="address" name="address" class="form-control"></textarea>
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label for="description">Description</label>
                                    <textarea id="description" name="description" class="form-control"></textarea>
                                </div>


                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary pull-right">Save</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

</body>

</html>


<script>
    $(document).ready(function() {
        $("#leadForm").validate({
            rules: {
                name: {
                    required: true,
                    minlength: 2
                },
                phonenumber: {
                    required: true,
                    digits: true,
                    minlength: 10,
                    maxlength: 15
                },
                email: {
                    email: true
                },
                lead_value: {
                    number: true,
                    min: 0
                },
                status: {
                    required: true
                },
                source: {
                    required: true
                }
            },
            messages: {
                name: {
                    required: "Please enter the lead's name.",
                    minlength: "Name must be at least 2 characters long."
                },
                phonenumber: {
                    required: "Please enter a valid phone number.",
                    digits: "Only numbers are allowed.",
                    minlength: "Phone number must be at least 10 digits.",
                    maxlength: "Phone number cannot be more than 15 digits."
                },
                email: {
                    email: "Please enter a valid email address."
                },
                lead_value: {
                    number: "Please enter a valid budget amount.",
                    min: "Budget cannot be negative."
                },
                status: {
                    required: "Please select a status."
                },
                source: {
                    required: "Please select a source."
                }
            },
            errorClass: "text-danger",
            errorElement: "small",
            highlight: function(element, errorClass) {
                $(element).addClass("is-invalid");
            },
            unhighlight: function(element, errorClass) {
                $(element).removeClass("is-invalid");
            },
            submitHandler: function(form) {
                $.ajax({
                    url: "leads.php",
                    type: "POST",
                    data: $(form).serialize(),
                    dataType: 'json', // Expect JSON response
                    success: function(response) {
                        if (response.status === "success") {
                            $("#formMessage").html('<div class="alert alert-success">' + response.message + '</div>').show();
                            $("#leadForm")[0].reset();
                        } else {
                            $("#formMessage").html('<div class="alert alert-danger">' + response.message + '</div>').show();
                        }
                        setTimeout(function() {
                            $("#formMessage").fadeOut();
                        }, 3000);
                    },
                    error: function(xhr, status, error) {
                        $("#formMessage").html('<div class="alert alert-danger">Request failed: ' + error + '</div>').show();
                        setTimeout(function() {
                            $("#formMessage").fadeOut();
                        }, 3000);
                    }
                });
                return false;
            }
        });
        // Fetch projects dynamically
        function fetchProjects() {
            $.ajax({
                url: "leads.php",
                type: "GET",
                data: {
                    fetch_projects: true
                },
                success: function(response) {
                    let projects = JSON.parse(response);
                    let projectDropdown = $("#projects");

                    projectDropdown.empty();
                    projectDropdown.append('<option value="">Nothing selected</option>');

                    projects.forEach(function(project) {
                        projectDropdown.append(`<option value="${project.id}">${project.name}</option>`);
                    });
                },
                error: function() {
                    $("#projects").html('<option value="">Error loading projects</option>');
                }
            });
        }

        // Load projects on page load
        fetchProjects();
        // Fetch status dynamically
        function fetchStatus() {
            $.ajax({
                url: "leads.php",
                type: "GET",
                data: {
                    fetch_status: true
                },
                success: function(response) {
                    let status = JSON.parse(response);
                    let statusDropdown = $("#status");

                    statusDropdown.empty();
                    statusDropdown.append('<option value="">Nothing selected</option>');

                    status.forEach(function(status) {
                        statusDropdown.append(`<option value="${status.id}">${status.name}</option>`);
                    });
                },
                error: function() {
                    $("#status").html('<option value="">Error loading status</option>');
                }
            });
        }

        // Load status on page load
        fetchStatus();


        // Fetch sources dynamically
        function fetchSources() {
            $.ajax({
                url: "leads.php",
                type: "GET",
                data: {
                    fetch_sources: true
                },
                success: function(response) {
                    let source = JSON.parse(response);
                    let sourceDropdown = $("#source");

                    sourceDropdown.empty();
                    sourceDropdown.append('<option value="">Nothing selected</option>');

                    source.forEach(function(source) {
                        sourceDropdown.append(`<option value="${source.id}">${source.name}</option>`);
                    });
                },
                error: function() {
                    $("#source").html('<option value="">Error loading source</option>');
                }
            });
        }

        // Load sources on page load
        fetchSources();

        // Fetch assigned dynamically
        function fetchAssigned() {
            $.ajax({
                url: "leads.php",
                type: "GET",
                data: {
                    fetch_assigned: true
                },
                success: function(response) {
                    let assigned = JSON.parse(response);
                    let assignedDropdown = $("#assigned");

                    assignedDropdown.empty();
                    assignedDropdown.append('<option value="">Nothing selected</option>');

                    assigned.forEach(function(staff) {
                        let isSelected = staff.staffid == 2 ? "selected" : ""; // Check if staffid is 2
                        assignedDropdown.append(`<option value="${staff.staffid}" ${isSelected}>${staff.fullname}</option>`);
                    });
                },
                error: function() {
                    $("#assigned").html('<option value="">Error loading source</option>');
                }
            });
        }

        // Load assigned on page load
        fetchAssigned()
    });
</script>
