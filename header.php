<?php
  if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $namaKelasBarang = 'keranjang-float-berisi';
  } else {
    $namaKelasBarang = 'keranjang-float-kosong';
  }
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./style/header.css">
  <title>Document</title>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
</head>

<body>
<div class="header-chart header-home">
    <div class="title-page">
      <h1>Bynna's Shop</h1>
    </div>
    <a href="index.php" class="yak">
      <i class="fi fi-rr-angle-small-left"></i>
    </a>
    <a href="keranjang.php" class="yak2" id="toadd">
      <i class="fi fi-rr-shopping-cart"></i>
    </a>
  </div>

  <script>
    // Ketika input dikosongkan, redirect ke index.php tanpa parameter
    document.getElementById('searchInput').addEventListener('input', function() {
      if (this.value.trim() === '') {
        window.location.href = 'index.php';
      }
    });

    // Mencegah submit form jika input kosong
    document.getElementById('searchForm').addEventListener('submit', function(e) {
      const searchValue = document.getElementById('searchInput').value.trim();
      if (searchValue === '') {
        e.preventDefault();
        window.location.href = 'index.php';
      }
    });
  </script>
</body>

</html>