/* Theme toggle */

function set_theme(theme = 'light') {
    localStorage.setItem('theme', theme);
    document.querySelector('html').setAttribute('data-theme', theme);
    document.querySelector('button[data-theme-toggle]').setAttribute('data-theme-toggle', theme);
}

window.addEventListener('DOMContentLoaded', () => {

    // Register click event for theme toggle button
    document.querySelector('button[data-theme-toggle]').addEventListener('click', () => {
        set_theme(localStorage.getItem('theme') === 'light' ? 'dark' : 'light');
    })

    let theme = localStorage.getItem('theme');

    // If 'theme' is set in localStorage, return to avoid overwriting
    if (theme !== null) {
        return set_theme(theme);
    }

    // Otherwise, get the user's preferred theme, and pass it to set_theme()
    set_theme(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
})

// Listen for system theme preference change
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
    set_theme(event.matches ? 'dark' : 'light');
});