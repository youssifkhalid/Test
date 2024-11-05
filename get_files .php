<?php
include 'db_connect.php';

$grade = $_POST['grade'] ?? '';
$class = $_POST['class'] ?? '';
$term = $_POST['term'] ?? '';
$subject = $_POST['subject'] ?? '';

$sql = "SELECT * FROM files WHERE 1=1";
$params = [];
$types = "";

if ($grade) {
    $sql .= " AND grade = ?";
    $params[] = $grade;
    $types .= "i";
}

if ($class) {
    $sql .= " AND class = ?";
    $params[] = $class;
    $types .= "i";
}

if ($term) {
    $sql .= " AND term = ?";
    $params[] = $term;
    
    $types .= "i";
}

if ($subject) {
    $sql .= " AND subject = ?";
    $params[] = $subject;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
}

header('Content-Type: application/json');
echo json_encode($files);