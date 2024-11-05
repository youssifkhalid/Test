<?php
session_start();
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المكتبة الإلكترونية للمدرسة</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>المكتبة الإلكترونية للمدرسة</h1>
        <nav>
            <?php if(isset($_SESSION['admin'])): ?>
                <a href="admin_panel.php" class="btn btn-light mr-2">لوحة التحكم</a>
                <a href="logout.php" class="btn btn-light">تسجيل الخروج</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-light">تسجيل الدخول للمسؤول</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="container mt-4">
        <section id="filters" class="mb-4">
            <h2>تصفية الملفات</h2>
            <form id="filter-form" class="form-row">
                <div class="form-group col-md-3">
                    <select id="grade" name="grade" class="form-control">
                        <option value="">اختر السنة الدراسية</option>
                        <option value="1">الأولى الثانوية</option>
                        <option value="2">الثانية الثانوية</option>
                        <option value="3">الثالثة الثانوية</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <select id="class" name="class" class="form-control">
                        <option value="">اختر الفصل</option>
                        <?php for($i=1; $i<=7; $i++): ?>
                            <option value="<?php echo $i; ?>">الفصل <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <select id="term" name="term" class="form-control">
                        <option value="">اختر الترم</option>
                        <option value="1">الترم الأول</option>
                        <option value="2">الترم الثاني</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <select id="subject" name="subject" class="form-control">
                        <option value="">اختر المادة</option>
                    </select>
                </div>
                <div class="form-group col-md-12">
                    <button type="submit" class="btn btn-primary">تصفية</button>
                </div>
            </form>
        </section>

        <section id="file-list">
            <h2>الملفات المرفوعة</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>اسم الدرس</th>
                            <th>الملف</th>
                            <th>السنة الدراسية</th>
                            <th>الفصل</th>
                            <th>الترم</th>
                            <th>المادة</th>
                        </tr>
                    </thead>
                    <tbody id="file-list-body">
                        <!-- سيتم ملء هذا الجزء بواسطة JavaScript -->
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer class="bg-dark text-white text-center py-3 mt-4">
        <p>&copy; <?php echo date("Y"); ?> المكتبة الإلكترونية للمدرسة. جميع الحقوق محفوظة.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="script.js"></script>
</body>
</html>