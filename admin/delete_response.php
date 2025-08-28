<?php
// admin/delete_response.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// Database connection
$servername = "localhost";
$username = "vxjtgclw_nairobi_survey";
$password = "FB=4x?80r=]wK;03";
$dbname = "vxjtgclw_nairobi_survey";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

try {
    if (isset($input['id'])) {
        // Delete single response
        $id = (int)$input['id'];
        
        $stmt = $conn->prepare("DELETE FROM survey_responses WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Response deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Response not found']);
            }
        } else {
            throw new Exception('Failed to delete response');
        }
        
    } elseif (isset($input['ids']) && is_array($input['ids'])) {
        // Delete multiple responses
        $ids = array_map('intval', $input['ids']);
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        $stmt = $conn->prepare("DELETE FROM survey_responses WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        
        if ($stmt->execute()) {
            $deleted_count = $stmt->affected_rows;
            echo json_encode([
                'success' => true, 
                'message' => "Successfully deleted $deleted_count response(s)"
            ]);
        } else {
            throw new Exception('Failed to delete responses');
        }
        
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No ID or IDs provided']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>