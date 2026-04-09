import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('app-toast');

    if (!toast || !toast.classList.contains('is-visible')) {
        return;
    }

    window.setTimeout(() => {
        toast.classList.remove('is-visible');
    }, 2800);
});
