document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const signForm = document.getElementById('signForm');
    const showSignup = document.getElementById('show-signup');
    const showLogin = document.getElementById('show-login');
  
    showSignup.addEventListener('click', (e) => {
      e.preventDefault();
      loginForm.classList.remove('active');
      signForm.classList.add('active');
    });
  
    showLogin.addEventListener('click', (e) => {
      e.preventDefault();
      signForm.classList.remove('active');
      loginForm.classList.add('active');
    });
  
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const formData = new FormData(loginForm);
      fetch('Backend/login.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.error) throw new Error(data.error);
          alert('Login successful!');
          window.location.href = data.redirect;
        })
        .catch(error => {
          alert(error.message);
        });
    });
  
    signForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const formData = new FormData(signForm);
      fetch('Backend/signup.php', {
        method: 'POST',
        headers:{
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.error) throw new Error(data.error);
          alert('Registration successful! Please log in.');
          signForm.classList.remove('active');
          loginForm.classList.add('active');
        })
        .catch(error => {
          alert(error.message);
        });
    });
  });