<?php
session_start();

// Clear the session and logout the user
session_unset();
session_destroy();

// Show loading screen with auto-redirect to login page after a short delay
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Logging Out...</title>
<style>
  body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: #2c80b4; /* Background color */
    margin: 0;
    font-family: Arial, sans-serif;
    position: relative;
  }

  body::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 0;
  }

  .container {
    position: relative;
    z-index: 1;
    width: 80%;
    max-width: 400px;
    background: white;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
    text-align: center;
  }

  .loading-msg {
    font-size: 1.3rem;
    margin-bottom: 20px;
    color: #333;
  }

  .progress-bar {
    width: 100%;
    background-color: #f3f3f3;
    border-radius: 25px;
    overflow: hidden;
    height: 20px;
  }

  .progress-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #2c80b4, #2077a5);
    border-radius: 25px;
    animation: fillProgress 3s forwards;
  }

  @keyframes fillProgress {
    from { width: 0%; }
    to { width: 100%; }
  }
</style>
</head>
<body>
  <div class="container">
    <div class="loading-msg">Logging you out, please wait...</div>
    <div class="progress-bar">
      <div class="progress-fill"></div>
    </div>
  </div>

  <script>
    // Redirect to login page after a short delay
    setTimeout(function() {
      window.location.href = 'login.php'; // Change this to your desired redirect page
    }, 1000); // 3 second loading bar duration
  </script>
</body>
</html>
