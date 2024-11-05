document.addEventListener('DOMContentLoaded', function() {
    const gradeSelect = document.querySelector('select[name="grade"]');
    const subjectSelect = document.querySelector('select[name="subject"]');

    gradeSelect.addEventListener('change', updateSubjects);

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
});