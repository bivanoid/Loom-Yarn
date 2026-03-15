<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<style>
  .alert {
    display: none;
    padding: 20px;
    background-color: greenyellow;
    position: fixed;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    display: none;
  }
</style>
<body>
  <div class="alert" id="alert_popup"></div>

  <script>
    function showAlert(message) {
      const alertPopup = document.getElementById('alert_popup');
      alertPopup.style.display = 'block';
      alertPopup.textContent = message;
      setTimeout (() => {
        alertPopup.style.display = "none";
      }, 3000);
    }
  </script>
</body>
</html>