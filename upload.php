<?php

require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

function uploadToCloud($filePath) {
    $bucket = 'your-bucket-name';
    $key = basename($filePath);

    $s3 = new S3Client([
        'version' => 'latest',
        'region'  => 'us-east-1',
        'credentials' => [
            'key'    => 'your-access-key',
            'secret' => 'your-secret-key',
        ]
    ]);

    try {
        $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key'    => $key,
            'SourceFile' => $filePath,
        ]);

        return $result['ObjectURL'];
    } catch (AwsException $e) {
        error_log($e->getMessage());
        return false;
    }
}

session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit();
}

$grades = $conn->query("SELECT * FROM grades ORDER BY id");
$terms = ['1' => 'الفصل الأول', '2' => 'الفصل الثاني'];
$subjects = [
    '1' => ['عربي', 'إنجليزي', 'رياضيات', 'علوم', 'دراسات اجتماعية'],
    '2' => ['عربي', 'إنجليزي', 'رياضيات', 'علوم', 'دراسات اجتماعية'],
    '3' => ['عربي', 'إنجليزي', 'رياضيات', 'علوم', 'دراسات اجتماعية'],
    '4' => ['عربي', 'إنجليزي', 'رياضيات', 'علوم', 'دراسات اجتماعية'],
    '5' => ['عربي', 'إنجليزي', 'رياضيات', 'علوم', 'دراسات اجتماعية'],
    '6' => ['عربي', 'إنجليزي', 'رياضيات', 'علوم', 'دراسات اجتماعية'],
    '7' => ['عربي', 'إنجليزي', 'رياضيات', 'علوم', 'دراسات اجتماعية'],
    '8' => ['عربي', 'إنجليزي', 'رياضيات', 'علوم', 'دراسات اجتماعية'],
    '9' => ['عربي', 'إنجليزي', 'رياضيات', 'علوم', 'دراسات اجتماعية'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade = $_POST['grade'];
    $term = $_POST['term'];
    $subject = $_POST['subject'];
    $lesson_name = $_POST['lesson_name'];
    $school_year = $_POST['school_year'];
    
    $uploadDir = 'uploads/';
    $allowedExtensions = ['pdf'];
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            die('Failed to create upload directory');
        }
    }
    
    $fileName = $_FILES['file']['name'];
    $fileSize = $_FILES['file']['size'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileType = $_FILES['file']['type'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileTmpName);
    finfo_close($finfo);
    
    if ($mimeType !== 'application/pdf') {
        $error = "عذرًا، يُسمح فقط بملفات PDF.";
    } elseif (!in_array($fileExtension, $allowedExtensions)) {
        $error = "عذرًا، يُسمح فقط بملفات PDF.";
    } elseif ($fileSize > $maxFileSize) {
        $error = "عذرًا، حجم الملف يجب أن يكون أقل من 10 ميجابايت.";
    } else {
        $newFileName = uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            $cloudUrl = uploadToCloud($uploadPath);
            if ($cloudUrl === false) {
                $error = "فشل رفع الملف إلى التخزين السحابي.";
            } else {
                $stmt = $conn->prepare("INSERT INTO files (grade, term, subject, file_name, file_url, lesson_name, school_year, cloud_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $fileUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $uploadPath;
                $stmt->bind_param("ssssssss", $grade, $term, $subject, $fileName, $fileUrl, $lesson_name, $school_year, $cloudUrl);
                
                if ($stmt->execute()) {
                    $success = "تم رفع الملف بنجاح.";
                    unlink($uploadPath); // Delete the temporary file
                } else {
                    $error = "حدث خطأ أثناء حفظ معلومات الملف في قاعدة البيانات.";
                }
                
                $stmt->close();
            }
        } else {
            $error = "حدث خطأ أثناء رفع الملف.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفع ملف جديد</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>رفع ملف جديد</h1>
    </header>
    <main class="container mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="grade">الصف الدراسي</label>
                <select class="form-control" id="grade" name="grade" required>
                    <option value="">اختر الصف</option>
                     <?php while ($grade = $grades->fetch_assoc()): ?>
                        <option value="<?php echo $grade['id']; ?>"><?php echo $grade['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="term">الفصل الدراسي</label>
                <select class="form-control" id="term" name="term" required>
                    <option value="">اختر الفصل</option>
                    <?php foreach ($terms as $key => $value): ?>
                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="subject">المادة</label>
                <select class="form-control" id="subject" name="subject" required>
                    <option value="">اختر المادة</option>
                </select>
            </div>
            <div class="form-group">
                <label for="lesson_name">اسم الدرس</label>
                <input type="text" class="form-control" id="lesson_name" name="lesson_name" required>
            </div>
            <div class="form-group">
                <label for="school_year">العام الدراسي</label>
                <select class="form-control" id="school_year" name="school_year" required>
                    <?php
                    $current_year = date('Y');
                    for ($i = 0; $i < 5; $i++) {
                        $year = $current_year - $i;
                        echo "<option value='{$year}-" . ($year+1) . "'>{$year}-" . ($year+1) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="file">اختر الملف (PDF فقط)</label>
                <input type="file" class="form-control-file" id="file" name="file" required accept=".pdf">
            </div>
            <button type="submit" class="btn btn-primary">رفع الملف</button>
        </form>
    </main>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            const subjects = <?php echo json_encode($subjects); ?>;
            
            $('#grade').change(function() {
                const grade = $(this).val();
                const subjectSelect = $('#subject');
                subjectSelect.empty().append('<option value="">اختر المادة</option>');
                
                if (grade in subjects) {
                    subjects[grade].forEach(function(subject) {
                        subjectSelect.append(`<option value="${subject}">${subject}</option>`);
                    });
                }
            });
        });
    </script>
</body>
</html>