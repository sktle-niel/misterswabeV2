// Initialize view on page load
document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const view = urlParams.get("view");

  if (view === "login") {
    showLoginForm();
  } else if (view === "create") {
    showCreatePasswordView();
  } else if (view === "forgot") {
    showForgotPassword();
  }
  // If view is 'initial' or null, the default initial view is already shown
});

function updateURL(view) {
  const url = new URL(window.location);
  url.searchParams.set("view", view);
  window.history.pushState({}, "", url);
}

function showLoginForm() {
  const initialView = document.getElementById("initialView");
  const loginFormView = document.getElementById("loginFormView");
  const headerTitle = document.getElementById("headerTitle");
  const headerSubtitle = document.getElementById("headerSubtitle");

  headerTitle.textContent = "Login";
  headerSubtitle.textContent = "Enter username and password";

  initialView.classList.add("hidden");
  loginFormView.classList.remove("hidden");
  loginFormView.classList.add("fade-in");

  updateURL("login");
}

function showInitialView() {
  const initialView = document.getElementById("initialView");
  const loginFormView = document.getElementById("loginFormView");
  const createPasswordView = document.getElementById("createPasswordView");
  const forgotPasswordView = document.getElementById("forgotPasswordView");
  const headerTitle = document.getElementById("headerTitle");
  const headerSubtitle = document.getElementById("headerSubtitle");

  headerTitle.textContent = "Sign in";
  headerSubtitle.innerHTML = 'Sign in or <a href="#">create an account</a>';

  loginFormView.classList.add("hidden");
  createPasswordView.classList.add("hidden");
  forgotPasswordView.classList.add("hidden");
  initialView.classList.remove("hidden");
  initialView.classList.add("fade-in");

  updateURL("initial");
}

function showForgotPassword() {
  const loginFormView = document.getElementById("loginFormView");
  const forgotPasswordView = document.getElementById("forgotPasswordView");
  const headerTitle = document.getElementById("headerTitle");
  const headerSubtitle = document.getElementById("headerSubtitle");

  headerTitle.textContent = "Forgot Password";
  headerSubtitle.textContent = "Reset your password";

  loginFormView.classList.add("hidden");
  forgotPasswordView.classList.remove("hidden");
  forgotPasswordView.classList.add("fade-in");

  updateURL("forgot");
}

function backToLogin() {
  const loginFormView = document.getElementById("loginFormView");
  const forgotPasswordView = document.getElementById("forgotPasswordView");
  const headerTitle = document.getElementById("headerTitle");
  const headerSubtitle = document.getElementById("headerSubtitle");

  headerTitle.textContent = "Login";
  headerSubtitle.textContent = "Enter username and password";

  forgotPasswordView.classList.add("hidden");
  loginFormView.classList.remove("hidden");
  loginFormView.classList.add("fade-in");

  updateURL("login");
}

function showInvalidMessage(message) {
  const invalidMessage = document.getElementById("invalidMessage");
  const invalidText = document.querySelector(".invalid-text");
  invalidText.textContent = message;
  invalidMessage.style.display = "block";
  setTimeout(() => {
    invalidMessage.style.display = "none";
  }, 3000);
}

function handleEmailContinue() {
  const emailInput = document.getElementById("emailOnly");
  const email = emailInput.value.trim();

  // Check if email is a valid Gmail
  const gmailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
  if (!gmailRegex.test(email)) {
    showInvalidMessage("Please enter a valid Gmail address.");
    return;
  }

  // If valid Gmail, show create password view
  showCreatePasswordView(email);
}

function handleLogin(event) {
  event.preventDefault();
  const email = document.getElementById("loginEmail").value;
  const password = document.getElementById("loginPassword").value;
  alert("Login attempt:\nEmail: " + email + "\nPassword: " + password);
}

function handleForgotPassword(event) {
  event.preventDefault();
  const email = document.getElementById("forgotEmail").value;
  alert("Password reset link sent to: " + email);
}

function showCreatePasswordView(email) {
  const initialView = document.getElementById("initialView");
  const createPasswordView = document.getElementById("createPasswordView");
  const headerTitle = document.getElementById("headerTitle");
  const headerSubtitle = document.getElementById("headerSubtitle");

  // Store email for later use
  if (email) {
    createPasswordView.setAttribute("data-email", email);
    document.getElementById("createEmailHidden").value = email;
  }

  // Change header text
  headerTitle.textContent = "Create Password";
  headerSubtitle.textContent = "Set up your account password";

  // Switch views
  initialView.classList.add("hidden");
  createPasswordView.classList.remove("hidden");
  createPasswordView.classList.add("fade-in");

  updateURL("create");
}

function handleCreatePassword(event) {
  event.preventDefault();

  const password = document.getElementById("createPassword").value;
  const confirmPassword = document.getElementById("confirmPassword").value;
  const passwordHelp = document.getElementById("passwordHelp");
  const confirmHelp = document.getElementById("confirmHelp");

  // Reset error messages
  passwordHelp.classList.remove("text-danger");
  confirmHelp.style.display = "none";

  // Validate password
  const hasUpperCase = /[A-Z]/.test(password);
  const hasLowerCase = /[a-z]/.test(password);
  const hasMinLength = password.length >= 8;

  if (!hasUpperCase || !hasLowerCase || !hasMinLength) {
    passwordHelp.classList.add("text-danger");
    return;
  }

  // Check if passwords match
  if (password !== confirmPassword) {
    confirmHelp.style.display = "block";
    return;
  }

  // Get the email from the stored data attribute
  const createPasswordView = document.getElementById("createPasswordView");
  const email = createPasswordView.getAttribute("data-email");

  // Check if email already exists
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "../back-end/read/checkEmail.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      const response = JSON.parse(xhr.responseText);
      if (response.exists) {
        showInvalidMessage("Email already exists.");
        return;
      } else {
        // Submit the form
        document
          .getElementById("createPasswordView")
          .querySelector("form")
          .submit();
      }
    }
  };
  xhr.send("email=" + encodeURIComponent(email));
}
