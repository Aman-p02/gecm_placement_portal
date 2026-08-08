import os
import re

files_to_update = [
    'student-module/login.php',
    'student-module/signup.php',
    'admin-module/login.php',
    'admin-module/signup.php'
]

html_to_inject = """
    <!-- Footer & Floating Dev Button -->
    <div style="text-align: center; margin-top: 40px; margin-bottom: 20px; color: #6c757d; font-size: 0.9rem;">
        &copy; <?= date('Y') ?> GEC Modasa Placement Cell. All rights reserved.
    </div>
    <a href="../developers.php" class="btn-dev-floating" title="Meet the Developers Team" style="position: fixed; bottom: 20px; right: 20px; background-color: #0f172a; color: white; border-radius: 50px; padding: 10px 20px; font-weight: 600; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.2); transition: all 0.3s ease; z-index: 1000; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-code"></i> Developer Team
    </a>
"""

for filepath in files_to_update:
    full_path = os.path.join('c:\\xampp\\htdocs\\gec_placement_portal', filepath)
    if os.path.exists(full_path):
        with open(full_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Check if already injected
        if "btn-dev-floating" not in content:
            # Inject right before </body>
            content = content.replace("</body>", html_to_inject + "\n</body>")
            
            with open(full_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Updated {filepath}")
        else:
            print(f"Already updated {filepath}")

