/* Theme toggle */

function set_theme(theme = 'light') {
    localStorage.setItem('theme', theme);
    $('html').attr('data-theme', theme);
    $('button[data-theme-toggle]').attr('data-theme-toggle', theme);
}

// NOTE: The initial state of the theme on each page is implemented in layout.html.twig to reduce load time
window.addEventListener('DOMContentLoaded', () => {
    // Register click event for theme toggle button
    $('button[data-theme-toggle]').on('click', () => {
        set_theme(localStorage.getItem('theme') === 'light' ? 'dark' : 'light');
    })
    $('a[data-theme-toggle]').on('click', (event) => {
        event.preventDefault();
        set_theme(localStorage.getItem('theme') === 'light' ? 'dark' : 'light');
    });
})

// Listen for system theme preference change
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
    set_theme(event.matches ? 'dark' : 'light');
});
