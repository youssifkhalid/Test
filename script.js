document.addEventListener('DOMContentLoaded', function() {
    const gradeSelect = document.getElementById('grade');
    const subjectSelect = document.getElementById('subject');
    const filterForm = document.getElementById('filter-form');
    const fileListBody = document.getElementById('file-list-body');

    gradeSelect.addEventListener('change', updateSubjects);
    filterForm.addEventListener('submit', filterFiles);

    function updateSubjects() {
        const grade = gradeSelect.value;
        subjectSelect.innerHTML = '<option value="">اختر المادة</option>';

        if (grade === '1') {
            addSubjects(['عربي', 'إنجليزي', 'فرنساوي', 'علوم متكاملة', 'رياضة', 'تاريخ', 'جغرافيا']);
        } else if (grade === '2' || grade === '3') {
            addSubjects(['عربي', 'إنجليزي', 'فرنساوي', 'فيزياء', 'كيمياء', 'أحياء', 'رياضة', 'تاريخ', 'جغرافيا']);
            if (grade === '3') {
                addSubjects(['علم نفس']);
            }
        }
    }

    function addSubjects(subjects) {
        subjects.forEach(subject => {
            const option = document.createElement('option');
            option.value = subject;
            option.textContent = subject;
            subjectSelect.appendChild(option);
        });
    }

    function filterFiles(e) {
        e.preventDefault();
        const formData = new FormData(filterForm);
        
        fetch('get_files.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(files => {
            fileListBody.innerHTML = '';
            files.forEach(file => {
                const row = `
                    <tr>
                        <td>${file.lesson_name}</td>
                        <td><a href="${file.file_url}" target="_blank" class="btn btn-sm btn-primary">تحميل ${file.file_name}</a></td>
                        <td>${file.grade}</td>
                        <td>${file.class}</td>
                        <td>${file.term}</td>
                        <td>${file.subject}</td>
                    </tr>
                `;
                fileListBody.innerHTML += row;
            });
            if (files.length === 0) {
                fileListBody.innerHTML = '<tr><td colspan="6" class="text-center">لا توجد ملفات متطابقة مع معايير البحث</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            fileListBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">حدث خطأ أثناء جلب الملفات</td></tr>';
        });
    }

    // تحميل جميع الملفات عند تحميل الصفحة
    filterFiles(new Event('submit'));
});