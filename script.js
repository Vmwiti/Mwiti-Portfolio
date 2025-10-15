// 🌙 Dark Mode Toggle
const toggleBtn = document.getElementById('modeToggle');
toggleBtn.addEventListener('click', () => {
  document.body.classList.toggle('dark');
  toggleBtn.textContent = document.body.classList.contains('dark') ? '☀️' : '🌙';
});

// 👀 Reveal on Scroll
const observer = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) entry.target.classList.add('show');
  });
});
document.querySelectorAll('.hidden').forEach(el => observer.observe(el));

// 🎯 Smooth Scroll from "View My Projects"
document.querySelector('.cta').addEventListener('click', () => {
  document.getElementById('projects').scrollIntoView({ behavior: 'smooth' });
});
