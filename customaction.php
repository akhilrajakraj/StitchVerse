<?php

// Helper function to sanitize input
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic fields
    $user_id = isset($_POST['user_id']) ? sanitize($_POST['user_id']) : '';
    $cname = isset($_POST['cname']) ? sanitize($_POST['cname']) : '';
    $gender = isset($_POST['gender']) ? sanitize($_POST['gender']) : '';
    $dtype = isset($_POST['dtype']) ? sanitize($_POST['dtype']) : '';

    // Upper wear fields
    $upper_quantity = isset($_POST['upper_quantity']) ? intval($_POST['upper_quantity']) : null;
    $upper_material = isset($_POST['upper_material']) ? sanitize($_POST['upper_material']) : '';
    $upper_color = isset($_POST['upper_color']) ? sanitize($_POST['upper_color']) : '';
    $upper_neckline = isset($_POST['upper_neckline']) ? sanitize($_POST['upper_neckline']) : '';
    $upper_sleeves = isset($_POST['upper_sleeves']) ? sanitize($_POST['upper_sleeves']) : '';
    $upper_pattern = isset($_POST['upper_pattern']) ? sanitize($_POST['upper_pattern']) : '';
    $upper_work = isset($_POST['upper_work']) ? sanitize($_POST['upper_work']) : '';
    $upper_measurements = isset($_POST['upper_measurements']) ? sanitize($_POST['upper_measurements']) : '';

    // Lower wear fields
    $lower_quantity = isset($_POST['lower_quantity']) ? intval($_POST['lower_quantity']) : null;
    $lower_material = isset($_POST['lower_material']) ? sanitize($_POST['lower_material']) : '';
    $lower_color = isset($_POST['lower_color']) ? sanitize($_POST['lower_color']) : '';
    $lower_fit = isset($_POST['lower_fit']) ? sanitize($_POST['lower_fit']) : '';
    $lower_pattern = isset($_POST['lower_pattern']) ? sanitize($_POST['lower_pattern']) : '';
    $lower_work = isset($_POST['lower_work']) ? sanitize($_POST['lower_work']) : '';
    $lower_measurements = isset($_POST['lower_measurements']) ? sanitize($_POST['lower_measurements']) : '';

    // Final details
    $budget = isset($_POST['budget']) ? sanitize($_POST['budget']) : '';
    $ddate = isset($_POST['ddate']) ? sanitize($_POST['ddate']) : '';

    // Handle file uploads
    $upload_dir = __DIR__ . '/uploads';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $upper_file_path = '';
    if (isset($_FILES['upper_file']) && $_FILES['upper_file']['error'] === UPLOAD_ERR_OK) {
        $upper_file_tmp = $_FILES['upper_file']['tmp_name'];
        $upper_file_name = basename($_FILES['upper_file']['name']);
        $upper_file_ext = strtolower(pathinfo($upper_file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($upper_file_ext, $allowed_ext)) {
            $upper_file_path = $upload_dir . uniqid('upper_') . '.' . $upper_file_ext;
            move_uploaded_file($upper_file_tmp, $upper_file_path);
        }
    }

    $lower_file_path = '';
    if (isset($_FILES['lower_file']) && $_FILES['lower_file']['error'] === UPLOAD_ERR_OK) {
        $lower_file_tmp = $_FILES['lower_file']['tmp_name'];
        $lower_file_name = basename($_FILES['lower_file']['name']);
        $lower_file_ext = strtolower(pathinfo($lower_file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($lower_file_ext, $allowed_ext)) {
            $lower_file_path = $upload_dir . uniqid('lower_') . '.' . $lower_file_ext;
            move_uploaded_file($lower_file_tmp, $lower_file_path);
        }
    }

    // Here you can insert the data into a database or send an email, etc.
    // For demonstration, we'll just display the received data.

    echo "<h2>Request Received</h2>";
    echo "<strong>Name:</strong> $cname<br>";
    echo "<strong>Gender:</strong> $gender<br>";
    echo "<strong>Dress Type:</strong> $dtype<br>";

    if ($upper_material || $upper_color) {
        echo "<h3>Upper Wear Details</h3>";
        echo "<strong>Quantity:</strong> $upper_quantity<br>";
        echo "<strong>Material:</strong> $upper_material<br>";
        echo "<strong>Color:</strong> $upper_color<br>";
        echo "<strong>Neckline:</strong> $upper_neckline<br>";
        echo "<strong>Sleeves:</strong> $upper_sleeves<br>";
        echo "<strong>Pattern:</strong> $upper_pattern<br>";
        echo "<strong>Embroidery/Work:</strong> $upper_work<br>";
        echo "<strong>Measurements:</strong> $upper_measurements<br>";
        if ($upper_file_path) {
            $rel_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $upper_file_path);
            echo "<strong>Reference Image:</strong> <a href='$rel_path' target='_blank'>View</a><br>";
        }
    }

    if ($lower_material || $lower_color) {
        echo "<h3>Lower Wear Details</h3>";
        echo "<strong>Quantity:</strong> $lower_quantity<br>";
        echo "<strong>Material:</strong> $lower_material<br>";
        echo "<strong>Color:</strong> $lower_color<br>";
        echo "<strong>Fit/Style:</strong> $lower_fit<br>";
        echo "<strong>Pattern:</strong> $lower_pattern<br>";
        echo "<strong>Embroidery/Work:</strong> $lower_work<br>";
        echo "<strong>Measurements:</strong> $lower_measurements<br>";
        if ($lower_file_path) {
            $rel_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $lower_file_path);
            echo "<strong>Reference Image:</strong> <a href='$rel_path' target='_blank'>View</a><br>";
        }
    }

    echo "<h3>Final Details</h3>";
    echo "<strong>Budget:</strong> $budget<br>";
    echo "<strong>Preferred Delivery Date:</strong> $ddate<br>";

    echo "<br><a href='javascript:history.back()'>Go Back</a>";
} else {
    echo "Invalid request.";
}
?>