import os
import re

files_to_update = [
    '.\\activity_details.php',
    '.\\developers.php',
    'student-module\\dashboard.php',
    'student-module\\forgot_password.php',
    'student-module\\login.php',
    'student-module\\reset_password.php',
    'student-module\\signup.php',
    'student-module\\track_applications.php',
    'student-module\\verify.php',
    'admin-module\\forgot_password.php',
    'admin-module\\login.php',
    'admin-module\\reset_password.php',
    'admin-module\\signup.php'
]

# Files that currently have the hardcoded footer block that needs to be removed
hardcoded_files = [
    'student-module\\login.php',
    'student-module\\signup.php',
    'admin-module\\login.php',
    'admin-module\\signup.php'
]

for filepath in files_to_update:
    full_path = os.path.join('c:\\xampp\\htdocs\\gec_placement_portal', filepath)
    if os.path.exists(full_path):
        with open(full_path, 'r', encoding='utf-8') as f:
            content = f.read()

        # If it's a hardcoded file, remove the hardcoded footer block
        if filepath in hardcoded_files:
            pattern = re.compile(r'<!-- Footer & Floating Dev Button -->.*?</div>', re.DOTALL)
            content = pattern.sub('', content)

        # Determine the correct include statement
        if filepath.startswith('student-module'):
            # Use root footer for student module
            include_stmt = "<?php include '../includes/footer.php'; ?>"
        elif filepath.startswith('admin-module'):
            # Use admin footer for admin module
            include_stmt = "<?php include 'includes/footer.php'; ?>"
        else:
            # Root files
            include_stmt = "<?php include 'includes/footer.php'; ?>"

        # Inject just before </body>
        if include_stmt not in content:
            content = content.replace('</body>', include_stmt + '\n</body>')

            with open(full_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Updated {filepath}")
        else:
            print(f"Already contains footer include {filepath}")
