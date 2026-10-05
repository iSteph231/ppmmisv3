<script>
document.addEventListener('DOMContentLoaded', function () {
    const password = document.getElementById('password');
    const confirmation = document.querySelector('[name="password_confirmation"]');
    const strengthText = document.getElementById('strengthText');
    const strengthFill = document.getElementById('strengthFill');
    const matchText = document.getElementById('matchText');

    function updateFeedback() {
        const value = password.value;
        let strength = 0;
        if (value.length >= 8) strength++;
        if (/[A-Z]/.test(value)) strength++;
        if (/[0-9]/.test(value)) strength++;
        if (/[@$!%*?&]/.test(value)) strength++;

        const level = strength <= 1 ? 0 : strength === 2 ? 1 : 2;
        const colors = ['#e74c3c', '#f39c12', '#27ae60'];
        const messages = [
            'Weak password - Use 8+ chars, uppercase, numbers',
            'Medium strength - Add uppercase or numbers',
            'Strong password'
        ];
        strengthText.textContent = value ? messages[level] : '';
        strengthText.className = ['weak', 'medium', 'strong'][level];
        strengthText.style.color = colors[level];
        strengthFill.style.width = value ? ['33%', '66%', '100%'][level] : '0%';
        strengthFill.style.background = colors[level];

        const matches = confirmation.value === value;
        confirmation.setCustomValidity(confirmation.value && !matches ? 'Password confirmation does not match.' : '');
        matchText.textContent = confirmation.value ? (matches ? '✓ Passwords match' : '✗ Passwords do not match') : '';
        matchText.style.color = matches ? '#27ae60' : '#e74c3c';
    }

    password.addEventListener('input', updateFeedback);
    confirmation.addEventListener('input', updateFeedback);
    updateFeedback();
});
</script>
