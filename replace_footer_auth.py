import os

auth_files = [
    'student-module\\login.php',
    'student-module\\signup.php',
    'student-module\\forgot_password.php',
    'student-module\\reset_password.php',
    'student-module\\verify.php',
    'admin-module\\login.php',
    'admin-module\\signup.php',
    'admin-module\\forgot_password.php',
    'admin-module\\reset_password.php'
]

for filepath in auth_files:
    full_path = os.path.join('c:\\xampp\\htdocs\\gec_placement_portal', filepath)
    if os.path.exists(full_path):
        with open(full_path, 'r', encoding='utf-8') as f:
            content = f.read()

        if "<?php include '../includes/footer.php'; ?>" in content:
            content = content.replace("<?php include '../includes/footer.php'; ?>", "<?php include '../includes/footer_auth.php'; ?>")
            
        if "<?php include 'includes/footer.php'; ?>" in content:
            content = content.replace("<?php include 'includes/footer.php'; ?>", "<?php include '../includes/footer_auth.php'; ?>")

        with open(full_path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {filepath}")

