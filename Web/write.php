<?php
// Configure Database
$server = "fdb1034.awardspace.net";
$user   = "3956771_attendancesys";
$pass   = "ATsys20513";
$dbName = "3956771_attendancesys";

// Connect to database
$database = new mysqli($server, $user, $pass, $dbName);
if ($database->connect_error) {
    die("Connection failed: " . $database->connect_error);
}

// Check for mode parameter
if (isset($_GET['mode'])) {
    // Get mode parameter
    $mode = $_GET['mode'];

    // Run code based on mode
    if ($mode == "mark-attendance") {
        // Check for attendance parameters
        if (isset($_GET['card'])) {
            // Store card value
            $card = $_GET['card'];

            // Check if already present
            $check = $database->prepare("SELECT * FROM attendance WHERE StudentID = ? AND DATE(EntryTime) = ?");
            $check->bind_param("ss", $card, date('Y-m-d'));
            $check->execute();
            $result = $check->get_result();
            if ($result->num_rows > 0) {
                echo $card . " is already present";
            } else {
                // Create SQL script to insert card to table
                $sqlScript = $database->prepare("INSERT INTO attendance (StudentID) VALUES (?)");
                $sqlScript->bind_param("s", $card);

                // Execute SQL script
                if ($sqlScript->execute()) {
                    echo "Marked " . $card . " as present";
                } else {
                    echo "Error: " . $stmt->error;
                }

                // begin closing
                $sqlScript->close();
            }
            $check->close();
        } else {
            echo "unable to find card";
        }
    } elseif ($mode == "new-card") {
        // Check for parameters
        if (isset($_GET['card']) && isset($_GET['name'])) {
            // store parameters
            $card = $_GET['card'];
            $name = $_GET['name'];

            // Check if already present
            $check = $database->prepare("SELECT * FROM students WHERE StudentID = ?");
            $check->bind_param("s", $card);
            $check->execute();
            $result = $check->get_result();
            if ($result->num_rows > 0) {
                echo $card . " is already present";
            } else {
                // Create SQL script to insert card to table
                $sqlScript = $database->prepare("INSERT INTO students (StudentID, StudentName) VALUES (?, ?)");
                $sqlScript->bind_param("ss", $card, $name);

                // Execute SQL script
                if ($sqlScript->execute()) {
                    echo "Added " . $card . " as ID for " . $name;
                } else {
                    echo "Error: " . $sqlScript->error;
                }

                // begin closing
                $sqlScript->close();
            }
            $check->close();
        } else {
            echo "unable to identify name or ID";
        }
    } else {
        echo "unable to identify mode";
    }
} else {
    echo "mode not found";
}

// close the connection
$database->close();
?>