<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        $file_id = $_POST['file_id'];
        $sql = "SELECT file_url FROM files WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $file = $result->fetch_assoc();
        
        if ($file) {
            $file_path = parse_url($file['file_url'], PHP_URL_PATH);
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $file_path)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $file_path);
            }
        }
        
        $sql = "DELETE FROM files WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $message = "تم حذف الملف بنجاح";
    } else {
        $grade = $_POST['grade'];
        $class = $_POST['class'];
        $term = $_POST['term'];
        $subject = $_POST['subject'];
        $lesson_name = $_POST['lesson_name'];
        $file = $_FILES['file'];

        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        $file_extension = pathinfo($file["name"], PATHINFO_EXTENSION);
        $new_file_name = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_file_name;
        
        $allowed_types = ['pdf', 'doc', 'docx', 'txt'];
        if (!in_array($file_extension, $allowed_types)) {
            $error = "عذرًا، يُسمح فقط بملفات PDF و DOC و DOCX و TXT.";
        } elseif (move_uploaded_file($file["tmp_name"], $target_file)) {
            $file_url = "http://" . $_SERVER['HTTP_HOST'] . "/" . $target_file;
            $sql = "INSERT INTO files (grade, class, term, subject, lesson_name, file_name, file_url) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiissss", $grade, $class, $term, $subject, $lesson_name, $file["name"], $file_url);
            $stmt->execute();
            $message = "تم رفع الملف بنجاح";
        } else {
            $error = "عذرًا، حدث خطأ أثناء رفع الملف";
        }
    }
}

// Fetch all files
$sql = "SELECT * FROM files ORDER BY upload_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم للمسؤول</title>
    <link rel="stylesheet" href="style.css">
    <style>/* الأنماط الأساسية */
body {
    font-family: 'Arial', sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.container {
    width: 80%;
    margin: auto;
    overflow: hidden;
    padding: 0 20px;
}

/* الرأس */
header {
    background: #35424a;
    color: #ffffff;
    padding-top: 30px;
    min-height: 70px;
    border-bottom: #e8491d 3px solid;
}

header h1 {
    margin: 0;
    text-align: center;
    padding-bottom: 10px;
}

header nav {
    text-align: center;
    margin-top: 10px;
}

header nav a {
    color: #ffffff;
    text-decoration: none;
    padding: 5px 15px;
    font-size: 18px;
}

header nav a:hover {
    color: #e8491d;
    text-decoration: underline;
}

/* المحتوى الرئيسي */
main {
    padding: 20px 0;
}

/* نموذج التصفية */
#filters {
    background: #ffffff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

#filters h2 {
    margin-top: 0;
    color: #35424a;
}

#filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

#filter-form select, #filter-form button {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

#filter-form button {
    background: #e8491d;
    color: #ffffff;
    border: none;
    cursor: pointer;
}

#filter-form button:hover {
    background: #333333;
}

/* جدول الملفات */
#file-list {
    background: #ffffff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

#file-list h2 {
    margin-top: 0;
    color: #35424a;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 12px;
    text-align: right;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #f2f2f2;
    font-weight: bold;
}

tr:hover {
    background-color: #f5f5f5;
}

/* الروابط في الجدول */
table a {
    color: #e8491d;
    text-decoration: none;
}

table a:hover {
    text-decoration: underline;
}

/* تذييل الصفحة */
footer {
    background: #35424a;
    color: #ffffff;
    text-align: center;
    padding: 20px 0;
    margin-top: 20px;
}

/* الاستجابة للشاشات الصغيرة */
@media (max-width: 768px) {
    #filter-form {
        flex-direction: column;
    }

    #filter-form select, #filter-form button {
        width: 100%;
    }

    table, thead, tbody, th, td, tr {
        display: block;
    }

    thead tr {
        position: absolute;
        top: -9999px;
        left: -9999px;
    }

    tr {
        border: 1px solid #ccc;
        margin-bottom: 10px;
    }

    td {
        border: none;
        position: relative;
        padding-left: 50%;
    }

    td:before {
        content: attr(data-label);
        position: absolute;
        left: 6px;
        width: 45%;
        padding-right: 10px;
        white-space: nowrap;
        font-weight: bold;
    }
}body {
    font-family: 'Arial', sans-serif;
    background-color: #f8f9fa;
}

header {
    margin-bottom: 2rem;
}

header nav {
    margin-top: 1rem;
}

main {
    min-height: calc(100vh - 200px);
}

#filters {
    background-color: #ffffff;
    padding: 1.5rem;
    border-radius: 0.25rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

#file-list {
    background-color: #ffffff;
    padding: 1.5rem;
    border-radius: 0.25rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.table {
    margin-bottom: 0;
}

.table th {
    background-color: #f8f9fa;
}

footer {
    margin-top: 2rem;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }

    .form-group {
        margin-bottom: 1rem;
    }
}</style>
</head>
<body>
    <h1>لوحة التحكم للمسؤول</h1>
    <?php if (isset($message)) echo "<p class='success'>$message</p>"; ?>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <select name="grade" required>
            <option value="">اختر السنة الدراسية</option>
            <option value="1">الأولى الثانوية</option>
            <option value="2">الثانية الثانوية</option>
            <option value="3">الثالثة الثانوية</option>
        </select>
        <select name="class" required>
            <option value="">اختر الفصل</option>
            <?php for($i=1; $i<=7; $i++): ?>
                <option value="<?php echo $i; ?>">الفصل <?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
        <select name="term" required>
            <option value="">اختر الترم</option>
            <option value="1">الترم الأول</option>
            <option value="2">الترم الثاني</option>
        </select>
        <select name="subject" required>
            <option value="">اختر المادة</option>
            <!-- سيتم ملء هذه القائمة ديناميكيًا باستخدام JavaScript -->
        </select>
        <input type="text" name="lesson_name" placeholder="اسم الدرس" required>
        <input type="file" name="file" accept=".pdf,.doc,.docx,.txt" required>
        <button type="submit">رفع الملف</button>
    </form>

    <h2>الملفات المرفوعة</h2>
    <table>
        <tr>
            <th>اسم الدرس</th>
            <th>الملف</th>
            <th>السنة الدراسية</th>
            <th>الفصل</th>
            <th>الترم</th>
            <th>المادة</th>
            <th>الإجراءات</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['lesson_name']); ?></td>
                <td><a href="<?php echo htmlspecialchars($row['file_url']); ?>" target="_blank"><?php echo htmlspecialchars($row['file_name']); ?></a></td>
                <td><?php echo htmlspecialchars($row['grade']); ?></td>
                <td><?php echo htmlspecialchars($row['class']); ?></td>
                <td><?php echo htmlspecialchars($row['term']); ?></td>
                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="file_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete">حذف</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <a href="index.php">العودة إلى الصفحة الرئيسية</a>
    <script src="admin_script.js"></script>
</body>
</html>