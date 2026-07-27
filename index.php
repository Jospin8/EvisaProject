<?php
// index.php
session_start();
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Evisa Portal</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="flag">
    <img src="./assets/poils.png" alt="Flag">
  </div>

  <header class="navbar">
    <div class="logo">
      <ion-icon class="menu-toggle" name="menu-outline"></ion-icon>
      <div class="logo-img">
        <img src="./assets/images.png" alt="Logo">
      </div>
      Evisa and Passport
    </div>
    <div class="navbar-links">
      <a href="index.php"><ion-icon name="home-outline"></ion-icon>Home</a>
      <a href="#hero-section" id="apply-link"><ion_icon name="documents-outline"></ion-icon>Apply</a>
  
      <a href="status.php"><ion-icon name="checkmark-done-outline"></ion-icon>Status</a>
      <a href="#faq"><ion-icon name="help-circle-outline"></ion-icon>FAQ</a>
      <a href="#contact"><ion-icon name="mail-outline"></ion-icon>Contact</a>
    </div>
  </header>
    <section class="hero-section" id="hero-section">
    <div class="auth-box">
      <div class="form-wrapper">
        <form class="form login-form active" id="loginForm" action="Backend/login.php" method="POST">
          <h2>Login</h2>
          <input type="email" name="email" placeholder="Email" required>
          <input type="password" name="password" placeholder="Password" required>
          <select id="applicationType" name="applicationType" required>
            <option value="">Select Application Type</option>
            <option value="visa">Visa Application</option>
            <option value="passport">Passport Application</option>
          </select>
          <button type="submit">Login</button>
          <p class="toggle-text">Don't have an account? <a href="#" id="show-signup">Sign up</a></p>
        </form>

        <form class="form signup-form" id="signForm" action="Backend/signup.php" method="POST">
          <h2>Sign Up</h2>
          <input type="text" name="id_number" placeholder="ID number" required>
          <input type="text" name="first_name" placeholder="First name" required>
          <input type="text" name="last_name" placeholder="Last name" required>
          <input type="text" name="phone" placeholder="Phone" required>
          <input type="date" name="dob" placeholder="Date of birth" required>
          <input type="email" name="email" placeholder="Email" required>
          <input type="text" name="address" placeholder="Address" required>
          <div class="form-row">
            <select name="role" required>
              <option value="">role</option>
              <option>Applicant</option>
              <option>Officer</option>
            </select>
          <input type="password" name="password" placeholder="Password" required>
          <button type="submit">Register</button>
          <p class="toggle-text">Already have an account? <a href="#" id="show-login">Login</a></p>
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        </form>
      </div>
</div>
  </section>

    <div class="overlay-boxes">
      <div class="overlay-box">
        <div class="box-details">
          <div class="box-name"><h1>Passport Application</h1></div>
          <div class="box-image">
            <img src="./assets/passP.png" alt="Passport">
          </div>
          <p>Ready to explore the world<br>+ Easy document upload<br>+ Real-time status tracking<br>+ Safe and secure process</p>
        </div>
      </div>
      <div class="overlay-box">
        <div class="box-details">
          <div class="box-name"><h1>Visa Application</h1></div>
          <div class="box-image">
            <img src="./assets/visa.png" alt="Visa">
          </div>
          <p>Ready to explore the world<br>+ Easy document upload<br>+ Real-time status tracking<br>+ Safe and secure process</p>
        </div>
      </div>
      <div class="overlay-box">
        <div class="box-details">
          <div class="box-name"><h1>Status</h1></div>
          <div class="box-image">
            <img src="./assets/track.png" alt="Status">
          </div>
          <p>Check your application status<br>+ Instant results<br>+ Track application steps<br>+ Email notifications</p>
        </div>
      </div>
    </div>
  <footer class="footer">
    <div class="socials">
      <a href="#home"><ion-icon name="logo-instagram"></ion-icon></a>
      <a href="#status"><ion-icon name="logo-twitter"></ion-icon></a>
      <a href="#faq"><ion-icon name="logo-linkedin"></ion-icon></a>
      <a href="#contact"><ion-icon name="mail-outline"></ion-icon></a>
    </div>
    <div class="divider"></div>
    <div class="navbar-links">
      <a href="index.php"><ion-icon name="home-outline"></ion-icon>Home</a>
      <a href="status.php"><ion-icon name="checkmark-done-outline"></ion-icon>Status</a>
      <a href="#faq"><ion-icon name="help-circle-outline"></ion-icon>FAQ</a>
      <a href="#contact"><ion-icon name="mail-outline"></ion-icon>Contact</a>
    </div>
    <div class="copyR">
      <p>© 2025 Evisa Portal. All rights reserved.</p>
    </div>
  </footer>

  <script>
  const loginForm = document.getElementById('loginForm');
  const signupForm = document.getElementById('signForm');

  function showForm(targetForm, hideForm) {
    hideForm.classList.remove('slide-in', 'active');
    targetForm.classList.add('slide-in', 'active');
  }

  document.getElementById('show-signup').addEventListener('click', (e) => {
    e.preventDefault();
    showForm(signupForm, loginForm);
  });

  document.getElementById('show-login').addEventListener('click', (e) => {
    e.preventDefault();
    showForm(loginForm, signupForm);
  });

  document.getElementById('apply-link').addEventListener('click', (e) => {
    e.preventDefault();
    showForm(loginForm, signupForm);
  });
  signupForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(signupForm);

    console.log('CSRF Token:', formData.get('csrf_token'));

    for (let [key, value] of formData.entries()) {
      console.log(`${key}: ${value}`);
    }

    fetch('/EvisaProject/Backend/signup.php', {
      method: 'POST',
      body: formData,
      headers: {
        'Accept': 'application/json'
      }
    })
      .then(response => {
        if (!response.ok) {
          return response.text().then(text => {
            throw new Error(`HTTP ${response.status}: ${text}`);
          });
        }
        return response.json();
      })
      .then(data => {
        if (data.error) {
          throw new Error(data.error);
        }
        alert('Registration successful! Please log in.');
        showForm(loginForm, signupForm);
        signupForm.reset();
      })
      .catch(error => {
        console.error('Fetch error:', error.message);
        alert(`Error: ${error.message}`);
      });
  });
  loginForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const data = new FormData(this);

    fetch('/EvisaProject/Backend/login.php', {
      method: 'POST',
      body: data
    })
      .then(response => response.json())
      .then(data => {
        if (data.error) throw new Error(data.error);
        if (data.redirect) window.location.href = data.redirect;
        else alert(data.message);
      })
      .catch(error => alert(`Error: ${error.message}`));
  });
</script>
