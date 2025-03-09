document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');

    // Function to update the meta theme-color tag
    function setThemeColor(color) {
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', color);
        }
    }

    // Function to update the theme and toggle table classes
    function syncTheme(theme) {
        // Toggle dark mode on html and body tags
        document.documentElement.classList.toggle('dark-mode', theme === 'dark');
        document.body.classList.toggle('dark-mode', theme === 'dark');
        localStorage.setItem('theme', theme);

        // Update meta theme-color based on the theme
        if (theme === 'dark') {
            setThemeColor('#1c1e21');
        } else {
            setThemeColor('#f5f7fa');
        }

        // Toggle "table-dark" class on all <table> elements
        document.querySelectorAll('table').forEach(table => {
            if (theme === 'dark') {
                table.classList.add('table-dark');
            } else {
                table.classList.remove('table-dark');
            }
        });

        // Send theme update to the server
        fetch('/update-theme', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `theme=${encodeURIComponent(theme)}&csrf_token=${encodeURIComponent(getCsrfToken())}`
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    return response.text().then(text => {
                        console.error('Response is not JSON:', text);
                        throw new Error('Response is not JSON');
                    });
                }
            })
            .then(data => {
                console.log('Theme updated successfully:', data);
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });
    }

    let preferredTheme = localStorage.getItem('theme');
    if (!preferredTheme) {
        preferredTheme = prefersDarkScheme.matches ? 'dark' : 'light';
        syncTheme(preferredTheme);
    } else {
        syncTheme(preferredTheme);
    }

    darkModeToggle.addEventListener('click', () => {
        const newTheme = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
        syncTheme(newTheme);
    });

    prefersDarkScheme.addEventListener('change', e => {
        if (!localStorage.getItem('theme')) {
            syncTheme(e.matches ? 'dark' : 'light');
        }
    });
});
