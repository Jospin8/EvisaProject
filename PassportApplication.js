document.addEventListener('DOMContentLoaded', () => {
  const step1 = document.getElementById('step1');
  const step2 = document.getElementById('step2');
  const progress = document.getElementById('progress');
  const form = document.querySelector('.application-form');
  const menuToggle = document.querySelector('.menu-toggle');
  const navbarLinks = document.querySelector('.navbar-links');

  if (!step1 || !step2 || !progress || !form || !menuToggle || !navbarLinks) {
      console.error('Missing elements');
      return;
  }

  // Start with Step 1
  step1.classList.add('active');
  progress.style.width = '50%';

  window.goToStep = (step) => {
      document.querySelectorAll('.form-step').forEach(stepEl => stepEl.classList.remove('active'));
      document.getElementById(`step${step}`).classList.add('active');
      progress.style.width = step === 2 ? '100%' : '50%';
  };

  // Menu toggle
  menuToggle.addEventListener('click', () => {
      navbarLinks.classList.toggle('active');
      menuToggle.setAttribute('name', navbarLinks.classList.contains('active') ? 'close-outline' : 'menu-outline');
  });

  form.addEventListener('submit', (e) => {
      e.preventDefault();

      let isValid = true;
      const requiredFields = form.querySelectorAll('[required]');

      //Check if required fields are filled
      requiredFields.forEach((field) => {
          if (!field.value.trim()) {
              isValid = false;
              field.classList.add('error');
          } else {
              field.classList.remove('error');
          }
      });

      if (isValid) {
          const formData = new FormData(form);
          fetch('/project/Backend/PassportApplication.php', {
              method: 'POST',
              body: formData
          })
          .then(response => response.json())
          .then(data => {
              if (data.error) throw new Error(data.error);
              alert(data.message);
              window.location.href = 'index.php';
          })
          .catch(error => alert('Error: ' + error.message));
      } else {
          alert('Please fill all required fields.');
      }
  });
});