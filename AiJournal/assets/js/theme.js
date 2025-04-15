document.addEventListener('DOMContentLoaded', function() {
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    
    // Check if user has a preference stored
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // Apply theme on initial load
    if (currentTheme === 'dark') {
        document.body.classList.add('dark-mode');
    } else {
        document.body.classList.remove('dark-mode');
    }
    
    // Toggle theme when button is clicked
    themeToggleBtn.addEventListener('click', function() {
        if (document.body.classList.contains('dark-mode')) {
            document.body.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light');
            document.cookie = "dark_mode=false; path=/; max-age=31536000"; // 1 year expiry
        } else {
            document.body.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
            document.cookie = "dark_mode=true; path=/; max-age=31536000"; // 1 year expiry
        }
    });
}); 