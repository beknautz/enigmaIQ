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
document.getElementById('contactForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const form = e.target;
  const btn  = form.querySelector('button[type="submit"]');
  const inputs = form.querySelectorAll('input, select, textarea');

  btn.disabled = true;
  btn.textContent = 'Sending…';

  const body = new URLSearchParams({
    name:    form.querySelector('input[type="text"]').value,
    email:   form.querySelector('input[type="email"]').value,
    company: form.querySelectorAll('input[type="text"]')[1]?.value ?? '',
    service: form.querySelector('select').value,
    message: form.querySelector('textarea').value,
  });

  try {
    const res  = await fetch('contact.php', { method: 'POST', body });
    const data = await res.json();

    if (data.ok) {
      btn.textContent = 'Message Sent ✓';
      btn.style.background = 'linear-gradient(135deg, #059669, #10b981)';
      form.reset();
    } else {
      btn.disabled = false;
      btn.textContent = 'Send Message';
      showFormError(data.error || 'Something went wrong. Please try again.');
    }
  } catch {
    btn.disabled = false;
    btn.textContent = 'Send Message';
    showFormError('Could not reach the server. Please try again.');
  }
});

function showFormError(msg) {
  let el = document.getElementById('formError');
  if (!el) {
    el = document.createElement('p');
    el.id = 'formError';
    el.style.cssText = 'color:#fca5a5;font-size:14px;margin-top:12px;text-align:center';
    document.getElementById('contactForm').appendChild(el);
  }
  el.textContent = msg;
  setTimeout(() => { el.textContent = ''; }, 5000);
}

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
