<?php
$file = __DIR__ . '/student-module/dashboard.php';
$content = file_get_contents($file);

// Add helper functions around line 214
$helpers = <<<PHP

function getFormValue(\$field, \$profile, \$default = '') {
    if (\$_SERVER['REQUEST_METHOD'] === 'POST' && isset(\$_POST[\$field])) {
        return \$_POST[\$field];
    }
    return \$profile[\$field] ?? \$default;
}

function isChecked(\$field, \$profile) {
    if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
        return isset(\$_POST[\$field]);
    }
    return !empty(\$profile[\$field]);
}

PHP;

$content = str_replace("\$mode = (isset(\$_GET['edit']) || !\$isProfileComplete) ? 'edit' : 'view';", "\$mode = (isset(\$_GET['edit']) || !\$isProfileComplete) ? 'edit' : 'view';\n" . $helpers, $content);

// Replace value="<?= htmlspecialchars($profile['field'] ?? '') ?>"
// with value="<?= htmlspecialchars(getFormValue('field', $profile)) ?>"
// ONLY inside the edit form!
// Wait, the view mode uses 'N/A', edit mode uses ''. So I can just search for ?? '')
$content = preg_replace('/htmlspecialchars\(\$profile\[\'([a-zA-Z0-9_]+)\'\] \?\? \'\'\)/', 'htmlspecialchars(getFormValue(\'$1\', \$profile))', $content);

// For email and phone, it sometimes does htmlspecialchars($profile['email'] ?? '')
// Also for textareas: <?= htmlspecialchars($profile['training_details'] ?? '') ?>
// The regex above handles them all!

// What about selects?
// <option value="Male" <?= (isset($profile['gender']) && $profile['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
// Replace with: <option value="Male" <?= getFormValue('gender', $profile) === 'Male' ? 'selected' : '' ?>>Male</option>
$content = preg_replace('/\(isset\(\$profile\[\'([a-zA-Z0-9_]+)\'\]\) && \$profile\[\'([a-zA-Z0-9_]+)\'\] === \'([^\']+)\'\)/', 'getFormValue(\'$1\', \$profile) === \'$3\'', $content);

// What about checkboxes?
// <?= !empty($profile['physically_handicap']) ? 'checked' : '' ?>
// Replace with: <?= isChecked('physically_handicap', $profile) ? 'checked' : '' ?>
$content = preg_replace('/!empty\(\$profile\[\'([a-zA-Z0-9_]+)\'\]\)/', 'isChecked(\'$1\', \$profile)', $content);

file_put_contents($file, $content);
echo "Dashboard updated.";
