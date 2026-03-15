<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<style>
  @import url(./style/global.css);
  .success-msg {
    background: var(--black);
    color: white;
    padding: 10px;
    text-align: center;
    display: grid;
    place-items: center;
    width: 100%;
    height: 70px;
    position: fixed;
    bottom: 2em;
    left: 0;
    z-index: 999;
    bottom: -100%;
    animation: pupup_success 4s linear forwards;
    animation-delay: 2s;
  }

  @keyframes pupup_success {
    0% {
      bottom: -100%;
    }

    10% {
      bottom: 0%;
    }

    50% {
      bottom: 0%;
    }

    90% {
      bottom: 0%;
    }

    100% {
      bottom: -100%;
    }
  }
</style>

<body>
  <?php

  if (isset($_GET['added'])) : ?>
    <div id="succes" class="success-msg">
      <h3>Berhasil Ditambahkan ke Keranjang</h3>
    </div>
    <script>
      setTimeout(() => {
        let pupupSuccces = document.getElementById('succes');
        pupupSuccces.style.display = 'none';
        pupupSuccces.style.animation = 'pupup_success 4s linear forwards';
      }, 6000)
    </script>
  <?php endif; ?>
</body>

</html>