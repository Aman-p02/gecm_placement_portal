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

font_awesome_tag = '    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">\n</head>'

for filepath in auth_files:
    full_path = os.path.join('c:\\xampp\\htdocs\\gec_placement_portal', filepath)
    if os.path.exists(full_path):
        with open(full_path, 'r', encoding='utf-8') as f:
            content = f.read()

        if 'font-awesome' not in content:
            content = content.replace('</head>', font_awesome_tag)
            with open(full_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Added FontAwesome to {filepath}")
        else:
            print(f"FontAwesome already exists in {filepath}")
