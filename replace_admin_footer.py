import os

admin_dir = 'c:\\xampp\\htdocs\\gec_placement_portal\\admin-module'

for filename in os.listdir(admin_dir):
    if filename.endswith('.php'):
        filepath = os.path.join(admin_dir, filename)
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        if "include 'includes/footer.php'" in content:
            # Replace it with the root footer include
            content = content.replace("include 'includes/footer.php'", "include '../includes/footer.php'")
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Updated {filename}")
