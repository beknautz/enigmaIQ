// Nav scroll behavior
const nav = document.getElementById('nav');
window.addEventListener('scroll', () => {
  nav.classList.toggle('scrolled', window.scrollY > 40);
}, { passive: true });

// Mobile hamburger
const hamburger = document.getElementById('hamburger');
const navLinks = document.querySelector('.nav-links');
hamburger.addEventListener('click', () => {
  navLinks.classList.toggle('open');
});
document.querySelectorAll('.nav-links a').forEach(link => {
  link.addEventListener('click', () => navLinks.classList.remove('open'));
});

// Intersection observer fade-up
const fadeEls = document.querySelectorAll(
  '.service-card, .why-card, .process-step, .result-card, .capability-item, .section-header, .cta-inner'
);
fadeEls.forEach(el => el.classList.add('fade-up'));

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry, i) => {
    if (entry.isIntersecting) {
      setTimeout(() => entry.target.classList.add('visible'), i * 60);
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.1 });

fadeEls.forEach(el => observer.observe(el));

// Contact form
document.getElementById('contactForm').addEventListener('submit', (e) => {
  e.preventDefault();
  const btn = e.target.querySelector('button[type="submit"]');
  btn.textContent = 'Message Sent';
  btn.style.background = 'linear-gradient(135deg, #059669, #10b981)';
  btn.disabled = true;
});

// Smooth stagger for hero elements on load
document.addEventListener('DOMContentLoaded', () => {
  const heroEls = document.querySelectorAll('.hero-badge, .hero-headline, .hero-sub, .hero-actions, .hero-stats');
  heroEls.forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(24px)';
    el.style.transition = `opacity 0.7s ease ${i * 0.1 + 0.1}s, transform 0.7s ease ${i * 0.1 + 0.1}s`;
    requestAnimationFrame(() => {
      el.style.opacity = '1';
      el.style.transform = 'none';
    });
  });
});
