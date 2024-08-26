<?php
session_start();
$name = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Login';
$initial = strtoupper($name[0]);

// Check cookies and update session if necessary
if (!isset($_SESSION['name']) && isset($_COOKIE['name'])) {
  $_SESSION['name'] = $_COOKIE['name'];
  $_SESSION['role'] = $_COOKIE['role'];
  if ($_COOKIE['role'] == 'student') {
      $_SESSION['student_id'] = isset($_COOKIE['student_id']) ? $_COOKIE['student_id'] : null;
  } elseif ($_COOKIE['role'] == 'landlord') {
      $_SESSION['landlord_id'] = isset($_COOKIE['landlord_id']) ? $_COOKIE['landlord_id'] : null;
  }
}

// Prepare variables for JavaScript output
$loginButtonStyle = isset($_SESSION['name']) ? 'none' : 'inline-block';
$submitAccommodationButtonStyle = (isset($_SESSION['role']) && $_SESSION['role'] == 'landlord') ? 'inline-block' : 'none';
$loginModalStyle = isset($_SESSION['name']) ? 'none' : 'block';

// Output JavaScript to manage UI elements based on session
echo "<script>
  document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('login-button').style.display = '$loginButtonStyle';
      document.getElementById('submit-accommodation-button').style.display = '$submitAccommodationButtonStyle';
      document.getElementById('login-modal').style.display = '$loginModalStyle';
  });
</script>";


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/Styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>


  <title>Mafikeng Student Connect</title>
  <!-- Profile Section -->
  <header class="profile-header">
    <div class="profile-info">
        <div class="profile-icon" onclick="toggleDropdown()">
            
            <div class="profile-initial"><?php echo $initial; ?></div>
        </div>
        <div class="profile-details">
            <span id="user-name"><?php echo $name; ?></span>
        </div>
        <!-- Dropdown Menu for Profile Features -->
        <div class="profile-dropdown">
            <div class="dropdown-toggle" onclick="toggleDropdown()">&#9660;</div>
            <ul class="dropdown-menu">
                <li><a href="profile_settings.php">Profile Settings</a></li>
                <li><a href="my_accommodations.php">My Accommodations</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <button class="nav-button" id="login-button"><i class="fas fa-sign-in-alt"></i><span>Login</span></button>
                <button class="nav-button" id="logout-button" onclick="logout()"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button>
            </ul>
        </div>
    </div>
</header>





  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const accommodationButton = document.getElementById('accommodation-button');
      const contactButton = document.getElementById('contact-button');
      const contactPopup = document.getElementById('contact-popup');
      const signUpButton = document.getElementById('sign-up-button');
      const loginButton = document.getElementById('login-button');
      const needHelpButton = document.getElementById('need-help-button');
      const logoutButton = document.getElementById('logout-button');
      const adminLoginButton = document.getElementById('admin-login-button');
      const landlordSignupButton = document.getElementById('landlord-signup-button');
      const landlordLoginButton = document.getElementById('landlord-login-button');
      const loginModalButton = document.getElementById('login-modal-button');
      const aboutUsButton = document.getElementById('about-us-button');

      document.getElementById('submit-accommodation-button').addEventListener('click', function() {
    navigateToSubmitAccommodation(); // Ensure this function is defined
});

document.getElementById('admin-dashboard-button').addEventListener('click', navigateToAdminDashboard);


function navigateToAdminDashboard() {
  window.location.href = 'admin.html';
}

function navigateToSubmitAccommodation() {
    window.location.href = 'submitAccommodation.php'; // Adjust this URL as needed
}

      // Event listener for the accommodation button
    accommodationButton.addEventListener('click', function() {
        window.location.href = 'Accommodation.php'; // Change this to your actual listings page
    });

      // Event listener for contact button toggle
      contactButton.addEventListener('click', () => {
        contactPopup.style.display = contactPopup.style.display === 'block' ? 'none' : 'block';
      });

      aboutUsButton.addEventListener('click', () => {
        document.getElementById('about-us-modal').style.display = 'block';
      });

      // Event listener for sign up button
      signUpButton.addEventListener('click', () => {
        document.getElementById('sign-up-modal').style.display = 'block';
      });

      // Event listener for login button
      loginButton.addEventListener('click', () => {
        document.getElementById('login-modal').style.display = 'block';
      });

      // Event listener for student login button in login modal
      loginModalButton.addEventListener('click', () => {
        studentLogin(); // Call the studentLogin function on button click
      });

      // Event listener for admin login button
      adminLoginButton.addEventListener('click', () => {
        document.getElementById('admin-login-modal').style.display = 'block';
      });

      // Event listener for need help button
      needHelpButton.addEventListener('click', () => {
        showNotification("Please sign up or log in to access accommodations.");
      });

      // Event listener for landlord signup button
      landlordSignupButton.addEventListener('click', () => {
        document.getElementById('landlord-signup-modal').style.display = 'block';
      });

      // Event listener for landlord login button
      landlordLoginButton.addEventListener('click', () => {
        document.getElementById('landlord-login-modal').style.display = 'block';
      });

      function closeAboutUsModal() {
        document.getElementById('about-us-modal').style.display = 'none';
      }

      // Check if user is signed up or logged in
      checkAuthentication();
    });

    document.getElementById('signup-form').addEventListener('submit', async function(event) {
    event.preventDefault();

    // Strict Input Validation
    const firstname = document.getElementById('firstname').value.trim();
    const lastname = document.getElementById('lastname').value.trim();
    const idNumber = document.getElementById('id-number').value.trim();
    const phoneNumber = document.getElementById('phone-number').value.trim();
    const email = document.getElementById('email').value.trim(); // Corrected ID
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value; // Corrected ID

    if (firstname === '' || lastname === '' || idNumber === '' || phoneNumber === '' || email === '' || password === '' || confirmPassword === '') {
        showNotification('All fields are required.');
        return;
    }

    // Input Validation
    if (!/^\+27\d{9}$/.test(phoneNumber)) {
        alert('Phone Number must start with +27 and be followed by 9 digits');
        return;
    }

    if (!/^\d{13}$/.test(idNumber)) {
        alert('ID Number must be 13 digits');
        return;
    }

    if (!/^[A-Za-z\s]+$/.test(firstname) || !/^[A-Za-z\s]+$/.test(lastname)) {
        alert('First Name and Last Name must contain only letters and spaces');
        return;
    }

    if (password !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }

    const formData = new FormData(this);

    try {
        const response = await fetch('signup.php', { // Adjusted URL
            method: 'POST',
            body: formData
        });

        if (response.ok) {
            const data = await response.json();
            if (data.loggedin) {
                // Update UI elements
                document.getElementById('signup-button').textContent = 'Logged In'; // Update the sign-up button text
                document.getElementById('accommodation-listings-button').style.display = 'block'; // Show the accommodation listings button
                document.getElementById('sign-up-modal').style.display = 'none'; // Hide the signup modal

                // Redirect to Mafikeng.html
                window.location.href = data.redirect;
            } else {
                showNotification(data.message);
            }
        } else {
            const errorData = await response.json();
            showNotification(errorData.message || 'Sign-up failed. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please check your connection and try again.');
    }
});

function showNotification(message) {
    alert(message); // Simple notification, you can enhance this
}

function login() {
    var email = document.getElementById('login-email').value;
    var password = document.getElementById('login-password').value;
    var stayLoggedIn = document.getElementById('stay-logged-in').checked;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "login.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.role) {
                // Hide the sign-up button for any logged-in user
                document.getElementById('sign-up-button').style.display = 'none';

                // Hide login button
                var loginButton = document.getElementById('login-button');
                loginButton.style.display = 'none';

                // Hide the modal
                document.getElementById('login-modal').style.display = 'none';

                // Show the submit accommodation button if the role is 'landlord'
                if (response.role.toLowerCase() === 'landlord') {
                    document.getElementById('submit-accommodation-button').style.display = 'inline-block';
                }

                // Update profile section
                updateProfileSection(response.role);

                // Show a success message with SweetAlert2
                Swal.fire({
                    icon: 'success',
                    title: 'Logged In',
                    text: 'Welcome, ' + response.role.charAt(0).toUpperCase() + response.role.slice(1) + '!',
                    showConfirmButton: false,
                    timer: 1500
                });

                // Set a cookie if "Stay logged in" is checked
                if (stayLoggedIn) {
                    document.cookie = "name=" + encodeURIComponent(response.name) + "; max-age=" + 3600 + "; path=/";
                }
            } else {
                // Show an error message with SweetAlert2
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: response.message
                });
            }
        }
    };

    var data = "email=" + encodeURIComponent(email) + "&password=" + encodeURIComponent(password);
    xhr.send(data);
}


function updateProfileSection(role) {
    var profileLetter = getCookie('name') || 'G';
    var profileLetterElement = document.querySelector('.profile-initial');
    var userNameElement = document.getElementById('user-name');

    if (profileLetterElement && userNameElement) {
        profileLetterElement.textContent = profileLetter.charAt(0).toUpperCase();
        userNameElement.textContent = profileLetter;
    }
}



// Call updateProfileSection on page load to set profile information if already logged in
document.addEventListener('DOMContentLoaded', () => {
    updateProfileSection();
});


    function showMessage(message) {
    const messageContainer = document.getElementById("message-container");
    const messageText = document.getElementById("message-text");


    messageText.textContent = message;
    messageContainer.classList.add('show');

    // Automatically hide the message after 3 seconds
    setTimeout(() => {
        messageContainer.classList.remove('show');
    }, 3000); // 3000 milliseconds = 3 seconds
}




    // Function to check authentication and update UI
    async function checkAuthentication() {
      const accommodationButton = document.getElementById('accommodation-button');
      const submitAccommodationButton = document.getElementById('submit-accommodation-button');
      const loginButton = document.getElementById('login-button');
      const signUpButton = document.getElementById('sign-up-button');
      const adminLoginButton = document.getElementById('admin-login-button');
      const landlordSignupButton = document.getElementById('landlord-signup-button');
      const landlordLoginButton = document.getElementById('landlord-login-button');
      const logoutButton = document.getElementById('logout-button');
      const landlordDashboardButton = document.getElementById('landlord-dashboard-button');
      const adminDashboardButton = document.getElementById('admin-dashboard-button');

      // Get user data from local storage
      const userDataString = localStorage.getItem("userData");

      if (userDataString) {
        const userData = JSON.parse(userDataString);
        const userEmail = userData.email;

        // Fetch user data from the server
        const userResponse = await fetch(`/user/${userEmail}`);
        if (userResponse.ok) {
          const user = await userResponse.json();
          const userRole = user.role;

          if (userRole === 'student') {
            // Hide certain buttons for student
            accommodationButton.style.display = 'block';
            loginButton.style.display = 'none';
            signUpButton.style.display = 'none';
            adminLoginButton.style.display = 'none';
            landlordSignupButton.style.display = 'none';
            landlordLoginButton.style.display = 'none';
            logoutButton.style.display = 'block';
          } else if (userRole === 'landlord') {
            // Show specific buttons for landlord
            submitAccommodationButton.style.display = 'block';
            landlordDashboardButton.style.display = 'block';
            adminDashboardButton.style.display = 'none';
            loginButton.style.display = 'none';
            signUpButton.style.display = 'none';
            adminLoginButton.style.display = 'none';
            landlordSignupButton.style.display = 'none';
            landlordLoginButton.style.display = 'none';
            logoutButton.style.display = 'block';
          } else if (userRole === 'admin') {
            // Show specific buttons for admin
            adminDashboardButton.style.display = 'block';
            submitAccommodationButton.style.display = 'none';
            landlordDashboardButton.style.display = 'none';
            loginButton.style.display = 'none';
            signUpButton.style.display = 'none';
            adminLoginButton.style.display = 'none';
            landlordSignupButton.style.display = 'none';
            landlordLoginButton.style.display = 'none';
            logoutButton.style.display = 'block';
          }
        } else {
          console.error('Failed to fetch user data');
          localStorage.removeItem('userData'); // Clear invalid data
        }
      } else {
        // Default view when no user is logged in
        accommodationButton.style.display = 'block';
        loginButton.style.display = 'block';
        signUpButton.style.display = 'block';
        adminLoginButton.style.display = 'block';
        landlordSignupButton.style.display = 'block';
        landlordLoginButton.style.display = 'block';
        submitAccommodationButton.style.display = 'none';
        logoutButton.style.display = 'none';
        landlordDashboardButton.style.display = 'none';
        adminDashboardButton.style.display = 'none';
      }
    }

   
    function updateUIOnStudentLogin() {
      // Hide buttons that are not needed for students
      const signUpButton = document.getElementById('sign-up-button');
      const adminLoginButton = document.getElementById('admin-login-button');
      const landlordLoginButton = document.getElementById('landlord-login-button');

      signUpButton.style.display = 'none';
      adminLoginButton.style.display = 'none';
      landlordLoginButton.style.display = 'none';

      // Update the login button text to "Student Logged In"
      const loginButton = document.getElementById('login-button');
      loginButton.innerHTML = 'Student Logged In';
      loginButton.disabled = true; // Optionally disable the login button
    }

    function updateUIOnLandlordLogin() {
      const studentLoginButton = document.getElementById('student-login-button');
      const landlordSignupButton = document.getElementById('landlord-signup-button');
      const aboutUsButton = document.getElementById('about-us-button');

      studentLoginButton.style.display = 'none';
      landlordSignupButton.style.display = 'none';
      aboutUsButton.style.display = 'none';

      const loginButton = document.getElementById('login-button');
      loginButton.innerHTML = 'Landlord Logged In';
      loginButton.disabled = true; // Optionally disable the login button
    }

    function updateUIOnAdminLogin() {
      const studentLoginButton = document.getElementById('student-login-button');
      const landlordSignupButton = document.getElementById('landlord-signup-button');
      const aboutUsButton = document.getElementById('about-us-button');

      studentLoginButton.style.display = 'none';
      landlordSignupButton.style.display = 'none';
      aboutUsButton.style.display = 'none';

      const loginButton = document.getElementById('login-button');
      loginButton.innerHTML = 'Admin Logged In';
      loginButton.disabled = true; // Optionally disable the login button
    }

   

    function updateUIOnLogout() {
      const accommodationButton = document.getElementById('accommodation-button');
      const submitAccommodationButton = document.getElementById('submit-accommodation-button');
      const loginButton = document.getElementById('login-button');
      const signUpButton = document.getElementById('sign-up-button');
      const adminLoginButton = document.getElementById('admin-login-button');
      const landlordSignupButton = document.getElementById('landlord-signup-button');
      const landlordLoginButton = document.getElementById('landlord-login-button');
      const logoutButton = document.getElementById('logout-button');
      const landlordDashboardButton = document.getElementById('landlord-dashboard-button');
      const adminDashboardButton = document.getElementById('admin-dashboard-button');

      accommodationButton.style.display = 'block';
      submitAccommodationButton.style.display = 'none';
      loginButton.style.display = 'block';
      signUpButton.style.display = 'block';
      adminLoginButton.style.display = 'block';
      landlordSignupButton.style.display = 'block';
      landlordLoginButton.style.display = 'block';
      logoutButton.style.display = 'none';
      landlordDashboardButton.style.display = 'none';
      adminDashboardButton.style.display = 'none';

      loginButton.innerHTML = '<i class="fas fa-sign-in-alt"></i> Student Login';
      loginButton.disabled = false;
    }

    function landlordSignup() {
    const form = document.getElementById('landlord-signup-form');
    const formData = new FormData(form);

    // Check if passwords match
    const password = formData.get('password');
    const confirmPassword = formData.get('confirm_password');
    if (password !== confirmPassword) {
        alert("Passwords do not match.");
        return;
    }

    fetch('landlord-signup.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log(data); // Check the response data in the console
        if (data.success) {
            alert("Signup successful!");

            // Update UI: Show Submit Accommodation button and unlock Accommodation Listings button
            document.getElementById('submit-accommodation-button').style.display = 'inline-block';
            document.getElementById('accommodation-button').disabled = false;
            document.getElementById('accommodation-button').innerHTML = '<i class="fas fa-unlock"></i> Accommodation Listings';  // Optional: Change the icon or text

            // Store login status in localStorage
            localStorage.setItem('landlordLoggedIn', 'true');

            // Redirect to Mafikeng.html
            window.location.href = 'Mafikeng.html';

            document.getElementById('landlord-signup-modal').style.display = 'none';
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during signup.');
    });

    function showMessage(message) {
    const messageContainer = document.getElementById("message-container");
    const messageText = document.getElementById("message-text");   


    messageText.textContent = message;
    messageContainer.classList.add('show');

    // Automatically hide the message after 3 seconds
    setTimeout(() => {
        messageContainer.classList.remove('show');
    }, 3000); // 3000 milliseconds = 3 seconds
}
}


// Function to open the landlord signup modal
function openLandlordSignupModal() {
    document.getElementById('landlord-signup-modal').style.display = 'block';
}

// Function to close the landlord signup modal
function closeLandlordSignupModal() {
    document.getElementById('landlord-signup-modal').style.display = 'none';
}

// Event listener for closing the modal when clicking outside of it
window.onclick = function(event) {
    if (event.target == document.getElementById('landlord-signup-modal')) {
        closeLandlordSignupModal();
    }
};


document.getElementById('signup-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    var formData = new FormData(this);

    fetch('signup.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.loggedin) {
            // Update UI elements
            document.getElementById('signup-button').textContent = 'Logged In'; // Update the sign-up button text
            document.getElementById('accommodation-listings-button').style.display = 'block'; // Show the accommodation listings button
            document.getElementById('sign-up-modal').style.display = 'none'; // Hide the signup modal

            // Redirect to Mafikeng.html
            window.location.href = data.redirect;
        } else {
            alert(data.message); // Show the error message
        }
    })
    .catch(error => console.error('Error:', error));
});

$.ajax({
    type: "POST",
    url: "signup.php",
    data: $("#signup-form").serialize(), // Ensure you have a form with this ID
    success: function(response) {
        if (response.loggedin) {
            window.location.href = response.redirect;
        } else {
            alert(response.message); // Display error message
        }
    },
    error: function() {
        alert("An error occurred while processing the request.");
    }
});

function logout() {
    fetch('logout.php')
        .then(response => response.text())
        .then(() => {
            // Update UI after logout
            document.getElementById('accommodation-button').disabled = true;
            document.getElementById('accommodation-button').innerHTML = '<i class="fas fa-lock"></i> Accommodation Listings';
            
            // Update the login button text
            var loginButton = document.getElementById('login-button');
            loginButton.innerHTML = '<i class="fas fa-sign-in-alt"></i> Student Login';
            loginButton.removeAttribute('disabled'); // Enable login button if needed

            // Optionally redirect to login page or update the UI
            window.location.href = 'Mafikeng.php'; // Or just refresh the page
        })
        .catch(error => {
            console.error('Error during logout:', error);
        });
}

function navigateToStudentGuidelines() {
  window.location.href = 'Student Guidelines.html';
}

function navigateToNews() {
    window.location.href = 'news.php';
  }


  

// Function to get a cookie value by name
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return ''; // Return an empty string if the cookie is not found
}

// Function to update the UI based on the user's role
function updateUIBasedOnRole(role) {
    document.getElementById('sign-up-button').style.display = 'none';
    document.getElementById('login-button').setAttribute('disabled', true);

    switch (role) {
        case 'student':
            document.getElementById('login-button').innerHTML = '<i class="fas fa-user-check"></i> Student Logged In';
            document.getElementById('landlord-signup').style.display = 'none';
            break;
        case 'landlord':
            document.getElementById('login-button').innerHTML = '<i class="fas fa-user-check"></i> Landlord Logged In';
            document.getElementById('submit-accommodation-button').style.display = 'inline-block';
            document.getElementById('landlord-signup').style.display = 'none';
            break;
        case 'admin':
            document.getElementById('login-button').innerHTML = '<i class="fas fa-user-check"></i> Admin Logged In';
            document.getElementById('admin-dashboard-button').style.display = 'inline-block';
            break;
        default:
            // No specific role or not logged in
            document.getElementById('sign-up-button').style.display = 'inline-block';
            document.getElementById('login-button').removeAttribute('disabled');
            break;
    }
}

// Function to update profile information
function updateProfile() {
    const userName = getCookie('name') || 'Guest';
    const profileLetter = userName.charAt(0).toUpperCase();

    const userNameElement = document.getElementById('user-name');
    const profileLetterElement = document.querySelector('.profile-initial');

    if (userNameElement) userNameElement.textContent = userName;
    if (profileLetterElement) profileLetterElement.textContent = profileLetter;
}

// On window load, check cookies and update UI
window.onload = function() {
    const userId = getCookie('user_id');
    const role = getCookie('role');

    if (userId && role) {
        updateUIBasedOnRole(role);
    } else {
        updateUIBasedOnRole(''); // Default case if no role is found
    }
};

// On DOM content loaded, update profile information
document.addEventListener('DOMContentLoaded', updateProfile);



function toggleDropdown() {
    var dropdownMenu = document.querySelector('.dropdown-menu');
    dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
    if (!event.target.matches('.profile-icon, .dropdown-toggle, .profile-initial')) {
        var dropdowns = document.getElementsByClassName("dropdown-menu");
        for (var i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.style.display === 'block') {
                openDropdown.style.display = 'none';
            }
        }
    }
}

window.onload = function() {
    var cookies = document.cookie.split('; ');
    var cookieObj = {};
    cookies.forEach(function(cookie) {
        var parts = cookie.split('=');
        cookieObj[parts[0]] = decodeURIComponent(parts[1]);
    });

    if (cookieObj['name']) {
        document.getElementById('login-button').style.display = 'none';
        if (cookieObj['role'] === 'landlord') {
            document.getElementById('submit-accommodation-button').style.display = 'inline-block';
        }
    } else {
        document.getElementById('login-modal').style.display = 'block';
    }
};

</script>

</head>
<body>
  <div class="header">
    
  </div>
    
  </div>
  <div id="notification-container" class="notification-container"></div>


  <div class="nav">
    <button class="nav-button" id="accommodation-button"><i class="fas fa-bed"></i><span>Accommodation Listings</span></button>


    <button class="nav-button" id="submit-accommodation-button" style="display: none;" onclick="navigateToSubmitAccommodation()"><i class="fas fa-plus-circle"></i><span>Submit Accommodation</span></button>
    <button class="nav-button" id="landlord-signup" onclick="openLandlordSignupModal()">
      <i class="fas fa-user-plus"></i><span>Are you a landlord? Sign up here</span>
    </button>
    <button class="nav-button" id="contact-button"><i class="fas fa-envelope"></i><span>Contact Us</span></button>
    <button class="nav-button" id="about-us-button"><i class="fas fa-info-circle"></i><span>About Us</span></button>
    <button class="nav-button" id="sign-up-button"><i class="fas fa-user-plus"></i><span>Sign Up</span></button>
   
    <button class="nav-button" id="need-help-button"><i class="fas fa-question-circle"></i><span>Need Help</span></button>
    <button class="nav-button" id="admin-dashboard-button" style="display: none;" onclick="navigateToAdminDashboard()">
      <i class="fas fa-tachometer-alt"></i><span>Admin Dashboard</span>
    </button>
  </div>
  

  <div class="slideshow-container">
    <div class="slide fade">
        <img src="css/images/Image1.png" alt="Slide 1">
        <div class="slide-content">
            <h1>LIST YOUR ACCOMMODATION TODAY!</h1>
           
            <button class="slider-button green-button">VIEW ACCOMMODATION</button>
            <button class="slider-button red-button">LIST ACCOMMODATION</button>
        </div>
    </div>
    <div class="slide fade">
        <img src="css/images/image2.png" alt="Slide 2">
        <div class="slide-content">
            <h1>LIST YOUR ACCOMMODATION TODAY!</h1>
           
            <button class="slider-button green-button">VIEW ACCOMMODATION</button>
            <button class="slider-button red-button">LIST ACCOMMODATION</button>
        </div>
    </div>
    <div class="slide fade">
        <img src="css/images/image3.jpg" alt="Slide 3">
        <div class="slide-content">
            <h1>LIST YOUR ACCOMMODATION TODAY!</h1>
            
            <button class="slider-button green-button">VIEW ACCOMMODATION</button>
            <button class="slider-button red-button">LIST ACCOMMODATION</button>
        </div>
    </div>
    <a class="prev" onclick="changeSlide(-1)">❮</a>
    <a class="next" onclick="changeSlide(1)">❯</a>
</div>
    <script>
       let slideIndex = 0;
        showSlides();

        function plusSlides(n) {
            showSlides(slideIndex += n);
        }

        function currentSlide(n) {
            showSlides(slideIndex = n);
        }

        function showSlides() {
            let i;
            let slides = document.getElementsByClassName("slide");
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            slideIndex = (slideIndex + slides.length) % slides.length;
            slides[slideIndex].style.display = "block";
        }
        

        // Automatic slideshow
        setInterval(function() {
            plusSlides(1);
        }, 3000); // Change image every 3 seconds

        
        
    </script>
<div id="message-container">
  <p id="message-text"></p>
</div>
 

<div class="content" id="weather-container">
<a class="weatherwidget-io" href="https://forecast7.com/en/n25d8625d64/mahikeng/" data-label_1="MAFIKENG" data-label_2="WEATHER" data-theme="retro-sky" >MAFIKENG WEATHER</a>
<script>
!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='https://weatherwidget.io/js/widget.min.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','weatherwidget-io-js');
</script>
</div>

  
  <!-- Contact Popup -->
  <div class="popup-container" id="contact-popup">
    <h3>Contact Information</h3>
    <p>Email: <a href="nqonkosi071@gmail.com">nqonkosi071@gmail.com</a></p>
    <p>Phone: +27 67 715 9623</p>
    <div class="social-icons">
      <a href="https://twitter.com/_Nqonkosi072?t=DcpeVlETfwbV02OGfRcBTQ&s=09" target="_blank"><i class="fab fa-twitter"></i></a>
      <a href="https://www.facebook.com/nqobile.nkosi.121772/" target="_blank"><i class="fab fa-facebook"></i></a>
      <a href="https://instagram.com/example" target="_blank"><i class="fab fa-instagram"></i></a>
    </div>
  </div>
  

  

  
    
    

    
  
  <!-- Verify Identity Modal -->
<div id="verify-identity-modal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeVerifyModal()">&times;</span>
    <h2>Verify Your Identity</h2>
    <div id="camera-feed"></div>
    <button onclick="capturePhoto()">Capture Photo</button>
    <div id="verification-status"></div>
  </div>
</div>

<!-- Signup Modal -->
<div id="sign-up-modal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('sign-up-modal').style.display = 'none';">&times;</span>
    <div class="video-background">
      <video autoplay loop muted poster="video/poster.jpg">
        <source src="css/images/move.mp4" type="video/mp4">
      </video>
    </div>
    <div class="login-form">
      <h2>Sign Up</h2>
      <form id="signup-form" action="signup.php" method="post">
        <label for="firstname">First Name:</label>
        <input type="text" id="firstname" name="firstname" required><br><br>
        <label for="lastname">Last Name:</label>
        <input type="text" id="lastname" name="lastname" required><br><br>
        <label for="id-number">ID Number:</label>
        <input type="text" id="id-number" name="id_number" required><br><br>
        <label for="phone-number">Phone Number:</label>
        <input type="text" id="phone-number" name="phone_number" required><br><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Sign Up">
      </form>
    </div>
  </div>
</div>



<!-- Login Modal -->
<div id="login-modal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('login-modal').style.display = 'none';">&times;</span>
    
    <div class="login-form">
      <h2>Login</h2>
      <form id="login-form" onsubmit="event.preventDefault(); login();">
        <label for="login-email">Email:</label>
        <input type="email" id="login-email" required><br><br>
        <label for="login-password">Password:</label>
        <input type="password" id="login-password" required><br><br>
        <label for="stay-logged-in">
        <input type="checkbox" id="stay-logged-in" name="remember_me" value="1"> Stay Logged In
        </label><br><br>
        <button type="submit" id="login-modal-button">Login</button>
        <button type="button" id="forgot-password-button" onclick="forgotPassword()">Forgot Password</button>
      </form>
    </div>
  </div>
</div>



 <!-- Landlord Signup Modal -->
<div id="landlord-signup-modal" class="modal" style="display: none;">
  <div class="modal-content">
    <span class="close" onclick="closeLandlordSignupModal()">&times;</span>
    <div class="signup-form">
      <h2>Landlord Signup</h2>
      <form id="landlord-signup-form" onsubmit="event.preventDefault(); landlordSignup();">
        <label for="landlord-firstname">First Name:</label>
        <input type="text" id="landlord-firstname" name="firstname" pattern="[A-Za-z]+" title="First Name must not contain special characters" required><br><br>
    
        <label for="landlord-lastname">Last Name:</label>
        <input type="text" id="landlord-lastname" name="lastname" pattern="[A-Za-z]+" title="Last Name must not contain special characters" required><br><br>
    
        <label for="landlord-id-number">ID Number:</label>
        <input type="text" id="landlord-id-number" name="id_number" pattern="\d{13}" title="ID Number must be 13 digits" required><br><br>
    
        <label for="landlord-phone-number">Phone Number:</label>
        <input type="text" id="landlord-phone-number" name="phone_number" pattern="\+27\d{9}" title="Phone Number must start with +27 and be followed by 9 digits" required><br><br>
    
        <label for="landlord-email">Email:</label>
        <input type="email" id="landlord-email" name="email" required><br><br>
    
        <label for="landlord-password">Password:</label>
        <input type="password" id="landlord-password" name="password" required><br><br>
    
        <label for="landlord-confirm-password">Confirm Password:</label>
        <input type="password" id="landlord-confirm-password" name="confirm_password" required><br><br>
    
        <button type="submit">Sign Up</button>
    </form>
    </div>
  </div>
</div>


  <!-- About Us Modal -->
  <div id="about-us-modal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('about-us-modal').style.display = 'none';">&times;</span>
      <div class="container">
        <h2>Who We Are</h2>
        <p>At Mafikeng Student Connect, we are more than just a student accommodation platform. We are a dedicated team committed to simplifying the process of finding suitable accommodation for students in Mafikeng. Our core values revolve around connectivity, safety, and empowerment.</p>

        <h2>Connectivity:</h2>
        <p>We believe in bridging the gap between students and property owners, creating a seamless connection that fosters trust and transparency.</p>

        <h2>Empowerment:</h2>
        <p>We empower students to make informed decisions about their accommodation options, and we support property owners in reaching their target audience effectively.</p>

        <h2>Our Mission</h2>
        <p>Our mission is to simplify the process of finding suitable student accommodation in Mafikeng by connecting students with available rental options. We strive to create a seamless platform that benefits both students seeking accommodation and property owners offering rentals.</p>


      </div>

      

  

  <!-- Footer -->
  <footer>
    <p>&copy; 2024 Mafikeng Student Connect. All rights reserved.</p>

    
  </footer>
</div>
</div>

  <!-- Landlord Login Modal -->
  <div id="landlord-login-modal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('landlord-login-modal').style.display = 'none';">&times;</span>
      <div class="login-form">
        <h2>Landlord Login</h2>
        <form onsubmit="event.preventDefault(); landlordLogin();">
          <label for="landlord-login-username">Username:</label>
          <input type="text" id="landlord-login-username" required><br><br>
          <label for="landlord-login-password">Password:</label>
          <input type="password" id="landlord-login-password" required><br><br>
          <button type="submit">Login</button>
        </form>
      </div>
    </div>
  </div>

  <div id="side-panel" class="side-panel">
    <span class="closebtn" onclick="closeProfilePanel()">&times;</span>
    <div id="profile-info">
      <!-- User information will be displayed here -->
    </div>
  </div>

  <!-- Bottom Buttons Container -->
<div class="bottom-buttons">
  <div class="bottom-button" onclick="navigateToStudentGuidelines()">
    <img src="css/images/college-students.jpg" alt="Button 1">
    <p>Student guidelines</p>
    <i class="fas fa-plus-circle"></i>
  </div>
  <div class="bottom-button"onclick="navigateToNews()">
    <img src="css/images/News.jpeg" alt="Button 2">
    <p>News</p>
    <i class="fas fa-plus-circle"></i>
  </div>
  <div class="bottom-button">
    <img src="css/images/Upcoming.jpg" alt="Button 3">
    <p>Upcoming Events</p>
    <i class="fas fa-plus-circle"></i>
  </div>
  <div class="bottom-button">
    <img src="css/images/Blacky.jpeg" alt="Button 4">
    <p>List Accommodation</p>
    <i class="fas fa-plus-circle"></i>
  </div>
</div>


<!-- Footer Content Section -->
<div class="footer-content">
  <div class="footer-section about-us">
    <h3>About Us</h3>
    <p>You are about to start looking for a place of your own – a place that will be your home for the coming months or years. This will be where you eat, sleep, study and relax. Depending on your specific wants and needs, there are many things to consider. Good luck in finding your new home!</p>
  </div>
  <div class="footer-section latest-news">
    <h3>Latest News</h3>
    <ul>
      <li><i class="fa fa-play"></i> Universities Universities of technology ...</li>
      <li><i class="fa fa-play"></i> Can you make the tough ...</li>
      <li><i class="fa fa-play"></i> Content Creator Internship ...</li>
      <li><i class="fa fa-play"></i> How to Keep Warm While on ...</li>
      <li><i class="fa fa-play"></i> Why we cannot wait to get back ...</li>
    </ul>
  </div>
  <div class="footer-section properties">
    <h3>Properties</h3>
    <ul>
      <li><i class="fa fa-play"></i> List your Property</li>
      <li><i class="fa fa-play"></i> Search Property</li>
      <li><i class="fa fa-play"></i> View latest added properties</li>
      <li><i class="fa fa-play"></i> View Student Guidelines</li>
      <li><i class="fa fa-play"></i> Contact us for Assistance to list</li>
    </ul>
  </div>
  
  <div class="footer-section contact-us">
    <h3>Contact Us</h3>
    <p>245 President Paul Kruger Avenue, Universitas, BLOEMFONTEIN, 9301</p>
    <p>Phone: 061 330 2785</p>
    <p>Email: infosa@studentaccommodation.co.za</p>
  </div>
</div>

<div class="footer">
  
  <div class="scroll-left">
    <p>Mafikeng Student Connect © 2024</p>
    </div>

</div>
</div>


</body>
</html>
