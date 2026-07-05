document.addEventListener('DOMContentLoaded', function () {
  document.addEventListener('click', function (event) {
    if (event.target.matches('.nav nav a')) document.body.classList.remove('menu-open');
  });

  const navLinks = Array.from(document.querySelectorAll('.nav nav a[href^="#"]'));
  const sections = navLinks.map(a => document.querySelector(a.getAttribute('href'))).filter(Boolean);
  const setActive = () => {
    let current = sections[0];
    sections.forEach(sec => {
      if (sec.getBoundingClientRect().top < 140) current = sec;
    });
    navLinks.forEach(a => a.classList.toggle('active', current && a.getAttribute('href') === '#' + current.id));
  };
  window.addEventListener('scroll', setActive, { passive: true });
  setActive();

  const cards = document.querySelectorAll('.card,.result,.admin-panel,.intro,.booking');
  cards.forEach(el => el.classList.add('reveal'));
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) entry.target.classList.add('in');
    });
  }, { threshold: 0.12 });
  cards.forEach(el => observer.observe(el));

  const layout = localStorage.getItem('mc_layout') || 'grid';
  document.body.classList.toggle('layout-list', layout === 'list');

  const sw = document.createElement('div');
  sw.className = 'layout-switch';
  sw.innerHTML = '<button type="button" data-layout="grid" title="Grid layout">▦</button><button type="button" data-layout="list" title="List layout">☰</button>';
  document.body.appendChild(sw);

  const refreshButtons = () => {
    const isList = document.body.classList.contains('layout-list');
    sw.querySelector('[data-layout="grid"]').classList.toggle('active', !isList);
    sw.querySelector('[data-layout="list"]').classList.toggle('active', isList);
  };
  sw.addEventListener('click', e => {
    const btn = e.target.closest('button[data-layout]');
    if (!btn) return;
    const value = btn.dataset.layout;
    document.body.classList.toggle('layout-list', value === 'list');
    localStorage.setItem('mc_layout', value);
    refreshButtons();
  });
  refreshButtons();
});
