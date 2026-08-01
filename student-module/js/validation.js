/**
 * Client-side validation for Auth Forms (Signup / Login)
 */

document.addEventListener('DOMContentLoaded', function () {
    const signupForm = document.getElementById('signupForm');
    
    if (signupForm) {
        signupForm.addEventListener('submit', function (event) {
            let isValid = true;

            // Enrollment Validation (12 digits)
            const enrollment = document.getElementById('enrollment_no');
            const enrollmentPattern = /^\d{12}$/;
            if (!enrollmentPattern.test(enrollment.value)) {
                enrollment.classList.add('is-invalid');
                isValid = false;
            } else {
                enrollment.classList.remove('is-invalid');
                enrollment.classList.add('is-valid');
            }

            // Password Match and Strength Validation
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const pwdPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
            
            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                document.getElementById('passwordFeedback').textContent = "Passwords do not match.";
                isValid = false;
            } else if (!pwdPattern.test(password.value)) {
                password.classList.add('is-invalid');
                document.getElementById('passwordFeedback').textContent = "Must have 8+ chars, upper, lower, number & special char.";
                isValid = false;
            } else {
                password.classList.remove('is-invalid');
                password.classList.add('is-valid');
                confirmPassword.classList.remove('is-invalid');
                confirmPassword.classList.add('is-valid');
            }

            if (!isValid) {
                event.preventDefault(); // Stop form submission
                event.stopPropagation();
            }
        });
    }

    // Clear validation state on input
    const inputs = document.querySelectorAll('.form-control, .form-select');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            input.classList.remove('is-invalid', 'is-valid');
        });
    });

    // Password visibility toggle
    const togglePasswordBtns = document.querySelectorAll('.toggle-password');
    togglePasswordBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // target the input before this button
            const input = this.previousElementSibling;
            const icon = this.tagName === 'I' ? this : this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
