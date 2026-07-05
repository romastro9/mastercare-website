document.addEventListener('click', function (event) {
  if (event.target.matches('.nav nav a')) {
    document.body.classList.remove('menu-open');
  }
});
