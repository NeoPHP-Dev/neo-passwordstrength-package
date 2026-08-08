export function evaluatePassword(password, config) {
    const criteria = {
        minLength: password.length >= config.minLength,
        maxLength: config.maxLength === null || password.length <= config.maxLength,
        uppercase: !config.requireUppercase || /[A-Z]/.test(password),
        lowercase: !config.requireLowercase || /[a-z]/.test(password),
        number: !config.requireNumber || /[0-9]/.test(password),
        specialChar: !config.requireSpecialChar || hasSpecialChar(password, config.specialChars),
    };

    const errors = [];
    if (!criteria.minLength) errors.push(`Password must be at least ${config.minLength} characters.`);
    if (!criteria.maxLength) errors.push(`Password must be at most ${config.maxLength} characters.`);
    if (config.requireUppercase && !criteria.uppercase) errors.push('Password must contain an uppercase letter.');
    if (config.requireLowercase && !criteria.lowercase) errors.push('Password must contain a lowercase letter.');
    if (config.requireNumber && !criteria.number) errors.push('Password must contain a number.');
    if (config.requireSpecialChar && !criteria.specialChar) errors.push('Password must contain a special character.');

    let score = 0;
    if (password.length >= config.minLength) score++;
    if (password.length >= config.minLength + 4) score++;
    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (config.specialChars.length && hasSpecialChar(password, config.specialChars)) score++;
    score = Math.min(4, password.length === 0 ? 0 : score);

    return { score, criteria, errors };
}

function hasSpecialChar(password, chars) {
    if (!chars.length) return false;
    const escaped = chars.map(c => c.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('');
    return new RegExp(`[${escaped}]`).test(password);
}