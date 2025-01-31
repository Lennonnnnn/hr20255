<?php
session_start();
if (!isset($_SESSION['e_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../../db/db_conn.php';

$data = json_decode(file_get_contents('php://input'), true);
$evaluations = $data['evaluations'];
$evaluatorId = $_SESSION['e_id'];

foreach ($evaluations as $evaluation) {
    $question = $evaluation['question'];
    $rating = $evaluation['rating'];

    $sql = "INSERT INTO employee_evaluations (evaluator_id, question, rating) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $evaluatorId, $question, $rating);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
echo json_encode(['success' => true]);
?>
