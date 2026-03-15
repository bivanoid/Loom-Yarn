<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<style>
  @import url(./style/global.css);

  .con-loading {
    width: 100%;
    height: 100vh;
    position: fixed;
    top: 0;
    display: grid;
    place-items: center;
    left: 0;
    z-index: 9999;
    background-color: var(--black);
    animation: con-loading 1.5s linear forwards;
  }

  .loading {
    border: 16px solid var(--grey);
    border-top: 16px solid var(--primary);
    border-radius: 50%;
    width: 2em;
    height: 2em;
    animation: spin 2s linear forwards;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }

  @keyframes con-loading {
    0% {
      opacity: 1;
    }

    80% {
      opacity: 1;
    }

    100% {
      opacity: 0;
      display: none;
    }
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }
</style>

<body>
  <div class="con-loading">
    <div class="loading">

    </div>
  </div>

</body>

</html>