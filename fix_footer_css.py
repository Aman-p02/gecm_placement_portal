import os

files_to_update = {
    'includes/footer.php': ('margin-top: 40px;', 'margin-top: auto;\n        width: 100%;'),
    'admin-module/includes/footer.php': ('margin-top: 60px;', 'margin-top: auto;\n        width: 100%;')
}

for filepath, (old_str, new_str) in files_to_update.items():
    full_path = os.path.join('c:\\xampp\\htdocs\\gec_placement_portal', filepath)
    if os.path.exists(full_path):
        with open(full_path, 'r', encoding='utf-8') as f:
            content = f.read()
        content = content.replace(old_str, new_str)
        with open(full_path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {filepath} CSS")

