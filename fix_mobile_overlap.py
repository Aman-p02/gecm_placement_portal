import re
import os

files_to_update = [
    'student-module/login.php',
    'student-module/signup.php',
    'admin-module/login.php',
    'admin-module/signup.php'
]

new_footer_html = """
    <!-- Footer & Floating Dev Button -->
    <div style="text-align: center; margin-top: 40px; margin-bottom: 30px; padding-bottom: 20px; width: 100%;">
        <div style="color: #6c757d; font-size: 0.9rem; margin-bottom: 15px;">
            &copy; <?= date('Y') ?> GEC Modasa Placement Cell. All rights reserved.
        </div>
        <a href="../developers.php" title="Meet the Developers Team"
            style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; background-color: #0f172a; color: white; border-radius: 50px; padding: 10px 20px; font-weight: 600; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.2); transition: all 0.3s ease; position: relative; z-index: 10;">
            <i class="fa-solid fa-code"></i> Developer Team
        </a>
    </div>
"""

for filepath in files_to_update:
    full_path = os.path.join('c:\\xampp\\htdocs\\gec_placement_portal', filepath)
    if os.path.exists(full_path):
        with open(full_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Regex to match the existing footer block
        pattern = re.compile(r'<!-- Footer & Floating Dev Button -->.*?</a>', re.DOTALL)
        
        if pattern.search(content):
            content = pattern.sub(new_footer_html.strip(), content)
            with open(full_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Updated {filepath}")
        else:
            print(f"Pattern not found in {filepath}")
