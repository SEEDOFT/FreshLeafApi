const STORAGE_KEY = 'freshleaf_sidebar_width';
const HANDLE_OVERHANG = 6;
const HANDLE_INSET = 14;

let isDragging = false;
let startPointerX = 0;
let startWidth = 0;
let handleEl = null;
let rafId = null;
let positionRetry = null;

function log(...args) {
  if (import.meta.env.DEV) console.log('[SidebarResizer]', ...args);
}

function positionHandle() {
  if (!handleEl) return;
  const el = document.querySelector('.fi-sidebar');
  if (!el) {
    handleEl.style.display = 'none';
    return;
  }
  handleEl.style.display = '';
  const r = el.getBoundingClientRect();
  handleEl.style.top = r.top + 'px';
  handleEl.style.left = (r.right - HANDLE_INSET) + 'px';
  handleEl.style.width = (HANDLE_INSET + HANDLE_OVERHANG) + 'px';
  handleEl.style.height = r.height + 'px';
}

function startRetry() {
  if (positionRetry) return;
  positionRetry = setInterval(() => {
    const el = document.querySelector('.fi-sidebar');
    if (el) {
      positionHandle();
      clearInterval(positionRetry);
      positionRetry = null;
    }
  }, 200);
  setTimeout(() => {
    if (positionRetry) {
      clearInterval(positionRetry);
      positionRetry = null;
    }
  }, 10000);
}

function createHandle() {
  if (handleEl) return;

  handleEl = document.createElement('div');
  handleEl.className = 'fi-sidebar-fixed-handle';
  handleEl.style.touchAction = 'none';
  document.body.appendChild(handleEl);

  handleEl.addEventListener('pointerdown', (e) => {
    const el = document.querySelector('.fi-sidebar');
    if (!el || e.button !== 0) return;

    isDragging = true;
    startPointerX = e.clientX;
    startWidth = el.getBoundingClientRect().width;

    document.body.style.cursor = 'ew-resize';
    document.body.classList.add('fi-sidebar-resizing');
    handleEl.setPointerCapture(e.pointerId);
    e.preventDefault();

    log('pointerdown, startWidth:', startWidth);
  });
}

function restoreSavedWidth() {
  const saved = localStorage.getItem(STORAGE_KEY);
  if (!saved) return;
  document.documentElement.style.setProperty('--sidebar-width', saved);
  log('restored saved width:', saved);
}

function init() {
  log('init');
  restoreSavedWidth();
  createHandle();
  positionHandle();
  if (!document.querySelector('.fi-sidebar')) {
    log('sidebar not found, starting retry');
    startRetry();
  } else {
    log('sidebar found on init');
  }
}

function onPointerMove(e) {
  if (!isDragging) return;
  const delta = e.clientX - startPointerX;
  const newWidth = Math.max(256, Math.min(window.innerWidth * 0.5, startWidth + delta));
  document.documentElement.style.setProperty('--sidebar-width', `${newWidth}px`);
  positionHandle();
}

function onPointerUp() {
  if (!isDragging) return;
  isDragging = false;
  document.body.style.cursor = '';
  document.body.classList.remove('fi-sidebar-resizing');
  const w = document.documentElement.style.getPropertyValue('--sidebar-width');
  if (w) localStorage.setItem(STORAGE_KEY, w);
  log('saved width:', w);
}

function onScrollOrResize() {
  if (isDragging) return;
  if (rafId) cancelAnimationFrame(rafId);
  rafId = requestAnimationFrame(positionHandle);
}

document.addEventListener('pointermove', onPointerMove);
document.addEventListener('pointerup', onPointerUp);
document.addEventListener('scroll', onScrollOrResize, true);
window.addEventListener('resize', onScrollOrResize);

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}

document.addEventListener('livewire:navigated', init);
