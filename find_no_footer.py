import os

directories = [
    '.',
    'student-module',
    'admin-module'
]

no_footer_files = []

for d in directories:
    dir_path = os.path.join('c:\\xampp\\htdocs\\gec_placement_portal', d)
    if os.path.exists(dir_path):
        for f in os.listdir(dir_path):
            if f.endswith('.php') and os.path.isfile(os.path.join(dir_path, f)):
                full_path = os.path.join(dir_path, f)
                with open(full_path, 'r', encoding='utf-8', errors='ignore') as file:
                    content = file.read()
                    if '</body>' in content and 'footer' not in content:
                        no_footer_files.append(os.path.join(d, f))

print("Files missing footer:")
for f in no_footer_files:
    print(f)
