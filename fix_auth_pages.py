import os
import re

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

        # Remove the corrupted button HTML
        pattern = re.compile(r'<a href="[^"]*?developers\.php"[^>]*>.*?Developer Team\s*</a>\s*</div>', re.DOTALL)
        content = pattern.sub('', content)

        # Replace the footer include
        if filepath.startswith('student-module'):
            new_footer = "<?php include '../includes/footer.php'; ?>"
        else:
            new_footer = "<?php include 'includes/footer.php'; ?>"

        # Remove any existing footer includes
        content = content.replace("<?php include '../includes/footer_auth.php'; ?>", "")
        content = content.replace("<?php include 'includes/footer_auth.php'; ?>", "")
        content = content.replace("<?php include '../includes/footer.php'; ?>", "")
        content = content.replace("<?php include 'includes/footer.php'; ?>", "")
        
        # Add the correct footer just before </body>
        content = content.replace('</body>', new_footer + '\n</body>')

        with open(full_path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Fixed {filepath}")
