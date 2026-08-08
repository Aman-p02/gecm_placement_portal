import re

with open('c:\\xampp\\htdocs\\gec_placement_portal\\developers.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Make cards smaller
content = content.replace('col-12 col-md-6 col-lg-5', 'col-12 col-md-6 col-lg-4')

# Move header up
content = content.replace('padding: 5rem 1rem 3rem;', 'padding: 2.5rem 1rem 1.5rem;')

# Make name larger
content = content.replace('font-size: 1.5rem;', 'font-size: 1.7rem;')

# Make details larger
content = content.replace('font-size: 0.9rem;', 'font-size: 1.05rem;')

# Add colorful linkedin and github icons
# Find the social-btn CSS and add the specific classes
social_css_addition = """
        .social-btn.linkedin {
            color: #0077b5;
            border-color: rgba(0, 119, 181, 0.3);
            background: rgba(0, 119, 181, 0.05);
        }
        
        .social-btn.github {
            color: #333;
            border-color: rgba(51, 51, 51, 0.3);
            background: rgba(51, 51, 51, 0.05);
        }
"""

if '.social-btn.linkedin {' not in content:
    content = content.replace('.social-btn:hover {', social_css_addition + '\n        .social-btn:hover {')

# Make the social-btn default font size bigger so icons are bigger
content = content.replace('font-size: 1.1rem;', 'font-size: 1.4rem;')
content = content.replace('width: 45px;\n            height: 45px;', 'width: 50px;\n            height: 50px;')


with open('c:\\xampp\\htdocs\\gec_placement_portal\\developers.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated developers.php")
