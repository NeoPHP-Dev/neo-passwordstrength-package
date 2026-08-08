import { evaluatePassword } from './strength-engine.js';

document.querySelectorAll('.ps-wrapper').forEach((root) => initPasswordStrength(root));

function initPasswordStrength(root) {
    const config = JSON.parse(root.dataset.config);
    const input = root.querySelector('.ps-input');
    const barFill = root.querySelector('[data-role="bar"]');
    const scoreLabel = root.querySelector('[data-role="score-label"]');
    const criteriaList = root.querySelector('[data-role="criteria"]');

    const scoreLabels = ['Very weak', 'Weak', 'Fair', 'Strong', 'Very strong'];
    const scoreColors = ['#e5484d', '#f97316', '#eab308', '#65a30d', '#16a34a'];

    const criteriaLabels = {
        minLength: `At least ${config.minLength} characters`,
        uppercase: 'An uppercase letter',
        lowercase: 'A lowercase letter',
        number: 'A number',
        specialChar: 'A special character',
    };

    function update() {
        const { score, criteria } = evaluatePassword(input.value, config);

        const pct = input.value.length === 0 ? 0 : ((score + 1) / 5) * 100;
        barFill.style.width = pct + '%';
        barFill.style.background = scoreColors[score];

        scoreLabel.textContent = input.value.length === 0 ? '' : scoreLabels[score];
        scoreLabel.style.color = scoreColors[score];

        criteriaList.innerHTML = '';
        Object.entries(criteriaLabels).forEach(([key, label]) => {
            const isRelevant = key === 'minLength'
                || (key === 'uppercase' && config.requireUppercase)
                || (key === 'lowercase' && config.requireLowercase)
                || (key === 'number' && config.requireNumber)
                || (key === 'specialChar' && config.requireSpecialChar);

            if (!isRelevant) return;

            const li = document.createElement('li');
            const met = criteria[key];
            li.className = met ? 'ps-criterion ps-met' : 'ps-criterion';
            li.textContent = (met ? '✓ ' : '○ ') + label;
            criteriaList.appendChild(li);
        });
    }

    input.addEventListener('input', update);
    update();
}