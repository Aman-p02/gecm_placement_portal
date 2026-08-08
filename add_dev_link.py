import os
import re

files_to_update = [
    'placement_team.php',
    'rules_and_guidelines.php',
    'placement_activities.php',
    'placement_statistics.php',
    'major_recruiters.php',
    'activity_details.php'
]

replacement = r'\1\n                        <li class="nav-item"><a class="nav-link" href="developers.php">Developers Team</a></li>'

for filename in files_to_update:
    filepath = os.path.join('c:\\xampp\\htdocs\\gec_placement_portal', filename)
    if os.path.exists(filepath):
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # We look for the Placement Team line
        content = re.sub(r'(<li class="nav-item"><a class="nav-link[^"]*" href="placement_team\.php">Placement Team</a></li>)', replacement, content)
        
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)

print("Navbars updated!")
