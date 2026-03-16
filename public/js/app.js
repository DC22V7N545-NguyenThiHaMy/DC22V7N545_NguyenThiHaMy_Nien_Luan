// Hiệu ứng scroll reveal đơn giản cho các khối có class .th-reveal
(function () {
  const elements = document.querySelectorAll('.th-reveal');
  if (!elements.length || typeof IntersectionObserver === 'undefined') return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('th-revealed');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.15 }
  );

  elements.forEach((el) => observer.observe(el));
})();

// Thêm blur cho navbar khi scroll xuống
(function () {
  const nav = document.querySelector('.navbar');
  if (!nav) return;

  const onScroll = () => {
    if (window.scrollY > 10) {
      nav.classList.add('th-navbar-blur');
    } else {
      nav.classList.remove('th-navbar-blur');
    }
  };

  window.addEventListener('scroll', onScroll);
  onScroll();
})();

