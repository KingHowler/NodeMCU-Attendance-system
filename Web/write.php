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

$mode = isset($_GET['mode']) ? filter_input(INPUT_GET, 'mode', FILTER_SANITIZE_STRING) : null;
$card = isset($_GET['card']) ? filter_input(INPUT_GET, 'card', FILTER_SANITIZE_STRING) : null;
$name = isset($_GET['name']) ? filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING) : null;
$cardHolder;
$time;

function RecognizeCard($cardID) {
    $findCard = $GLOBALS['database']->prepare("SELECT StudentName FROM students WHERE StudentID = ?");
    $findCard->bind_param("s", $cardID);
    $findCard->execute();
    $findCard->bind_result($GLOBALS['cardHolder']);
    if ($findCard->fetch()) {
        echo $cardID . " belongs to " . $GLOBALS['cardHolder'] . "\n";
        $success = true;
    } else {
        echo $cardID . " is not recognized" . "\n";
        $success = false;
    }
    $findCard->close();
    return $success;
}

function RegisterCard($cardID, $cardHolder) {
    $registerCard = $GLOBALS['database']->prepare("INSERT INTO students (StudentID, StudentName) VALUES (?, ?)");
    $registerCard->bind_param("ss", $cardID, $cardHolder);
    if ($registerCard->execute()) {
        echo "Added " . $cardID . " as ID for " . $cardHolder . "\n";
    } else {
        echo "Error: " . $registerCard->error . "\n";
    }
    $registerCard->close();
}

function CheckPresent($cardID) {
    $check = $GLOBALS['database']->prepare("SELECT EntryTime FROM attendance WHERE StudentID = ? AND DATE(EntryTime) = ?");
    $check->bind_param("ss", $cardID, date('Y-m-d'));
    $check->execute();
    $check->bind_result($GLOBALS['time']);
    if ($check->fetch()) {
        echo $cardID . " was present at " . $GLOBALS['time'] . "\n";
        $success = true;
    } else {
        $success = false;
    }
    $check->close();
    return $success;
}

function MarkPresent($cardID) {
    date_default_timezone_set("Asia/Karachi");
    $markPresent = $GLOBALS['database']->prepare("INSERT INTO attendance (EntryDate, EntryTime, StudentID) VALUES (?, ?, ?)");
    $markPresent->bind_param("sss", date("Y-m-d"), date("H:i"), $cardID);
    if ($markPresent->execute()) {
        echo "Marked " . $cardID . " as present" . "\n";
    } else {
        echo "Error: " . $markPresent->error . "\n";
    }
    $markPresent->close();
}

switch($GLOBALS['mode']) {
    case 'mark-attendance':
        if ($GLOBALS['card']) {
            if (RecognizeCard($GLOBALS['card']) && !CheckPresent($GLOBALS['card'])) {
                MarkPresent($GLOBALS['card']);
            }
        } else {
            echo "Invalid Card" . "\n";
        }
    break;

    case 'new-card':
        if ($GLOBALS['card'] && $GLOBALS['name']) {
            if (!RecognizeCard($GLOBALS['card'])) {
                RegisterCard($GLOBALS['card'], $GLOBALS['name']);
            }
        }
    break;

    default :
        echo "Invalid Mode" . "\n";
    break;
}
?>