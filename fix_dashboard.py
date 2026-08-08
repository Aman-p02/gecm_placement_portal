import re

with open('student-module/dashboard.php', 'r', encoding='utf-8') as f:
    content = f.read()

helpers = """
function getFormValue($field, $profile, $default = '') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[$field])) {
        return $_POST[$field];
    }
    return $profile[$field] ?? $default;
}

function isChecked($field, $profile) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return isset($_POST[$field]);
    }
    return !empty($profile[$field]);
}
"""

content = content.replace("$mode = (isset($_GET['edit']) || !$isProfileComplete) ? 'edit' : 'view';", "$mode = (isset($_GET['edit']) || !$isProfileComplete) ? 'edit' : 'view';\n" + helpers)

content = re.sub(r"htmlspecialchars\(\$profile\['([a-zA-Z0-9_]+)'\] \?\? ''\)", r"htmlspecialchars(getFormValue('\1', $profile))", content)
content = re.sub(r"htmlspecialchars\(\$profile\['([a-zA-Z0-9_]+)'\]\)", r"htmlspecialchars(getFormValue('\1', $profile))", content)
content = re.sub(r"\(isset\(\$profile\['([a-zA-Z0-9_]+)'\]\) && \$profile\['([a-zA-Z0-9_]+)'\] === '([^']+)'\)", r"getFormValue('\1', $profile) === '\3'", content)
content = re.sub(r"!empty\(\$profile\['([a-zA-Z0-9_]+)'\]\)", r"isChecked('\1', $profile)", content)

with open('student-module/dashboard.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Dashboard updated successfully!")
