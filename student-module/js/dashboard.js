/**
 * Dashboard interactivity: File previews, dynamic skills tagging
 */

document.addEventListener('DOMContentLoaded', function () {
    
    // 1. Profile Picture Preview
    const profilePicInput = document.getElementById('profile_pic');
    const previewImage = document.getElementById('preview_img');

    if (profilePicInput && previewImage) {
        profilePicInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate size (< 2MB)
                if(file.size > 2 * 1024 * 1024) {
                    alert('File size exceeds 2MB limit.');
                    profilePicInput.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // 2. Dynamic Skills Addition
    const addSkillBtn = document.getElementById('add_skill_btn');
    const skillInput = document.getElementById('skill_input');
    const skillsContainer = document.getElementById('skills_container');
    const hiddenSkillsInput = document.getElementById('hidden_skills');

    let skillsArray = [];

    // Initialize from hidden input if editing
    if(hiddenSkillsInput && hiddenSkillsInput.value) {
        skillsArray = hiddenSkillsInput.value.split(',').filter(s => s.trim() !== '');
        renderSkills();
    }

    if (addSkillBtn && skillInput) {
        addSkillBtn.addEventListener('click', function() {
            const skill = skillInput.value.trim();
            if (skill && !skillsArray.includes(skill)) {
                skillsArray.push(skill);
                skillInput.value = '';
                updateHiddenInput();
                renderSkills();
            }
        });

        skillInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addSkillBtn.click();
            }
        });
    }

    function renderSkills() {
        if(!skillsContainer) return;
        skillsContainer.innerHTML = '';
        skillsArray.forEach((skill, index) => {
            const badge = document.createElement('div');
            badge.className = 'skill-tag';
            badge.innerHTML = `
                ${skill}
                <i class="fa-solid fa-xmark remove-skill" data-index="${index}"></i>
            `;
            skillsContainer.appendChild(badge);
        });

        // Add remove listeners
        document.querySelectorAll('.remove-skill').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = this.getAttribute('data-index');
                skillsArray.splice(idx, 1);
                updateHiddenInput();
                renderSkills();
            });
        });
    }

    function updateHiddenInput() {
        if(hiddenSkillsInput) {
            hiddenSkillsInput.value = skillsArray.join(',');
        }
    }
});
