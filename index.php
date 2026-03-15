<?php

session_start();
include 'koneksi.php';

// Handler untuk tambah ke keranjang
if (isset($_POST['add_to_cart'])) {
  $id_barang = $_POST['id_barang'];
  $nama_barang = $_POST['nama_barang'];
  $harga_barang = $_POST['harga_barang'];
  $diskon_barang = isset($_POST['diskon_barang']) ? $_POST['diskon_barang'] : 0; // ✅ TAMBAHKAN INI
  $jumlah = isset($_POST['jumlah']) ? intval($_POST['jumlah']) : 1;
  $gambar_barang = $_POST['gambar_barang'];

  // Inisialisasi cart jika belum ada
  if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
  }

  // Cek apakah item sudah ada di keranjang
  $found = false;
  foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['id'] == $id_barang) {
      $_SESSION['cart'][$key]['jumlah'] += $jumlah;
      $found = true;
      break;
    }
  }

  // Jika belum ada, tambahkan item baru
  if (!$found) {
    $_SESSION['cart'][] = array(
      'id' => $id_barang,
      'nama' => $nama_barang,
      'harga' => $harga_barang,
      'diskon' => $diskon_barang, // ✅ TAMBAHKAN INI
      'jumlah' => $jumlah,
      'gambar' => $gambar_barang
    );
  }

  // Redirect untuk mencegah form resubmission
  header("Location: index.php?added=1");
  exit;
}
// Ambil keyword pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Query dengan prepared statement untuk keamanan
if (!empty($search)) {
  $stmt = mysqli_prepare($koneksi, "
    SELECT *,
      (harga_barang - (harga_barang * diskon_barang / 100)) AS total_diskon
    FROM items
    WHERE nama_barang LIKE ?
    ORDER BY (stok_barang = 0) ASC, id DESC
  ");
  $searchParam = "%{$search}%";
  mysqli_stmt_bind_param($stmt, "s", $searchParam);
  mysqli_stmt_execute($stmt);
  $hasil = mysqli_stmt_get_result($stmt);
} else {
  $hasil = mysqli_query($koneksi, "
    SELECT *,
      (harga_barang - (harga_barang * diskon_barang / 100)) AS total_diskon
    FROM items
    ORDER BY (stok_barang = 0) ASC, id DESC
  ");
}

if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
  $namaKelasBarang = 'keranjang-float-berisi';
} else {
  $namaKelasBarang = 'keranjang-float-kosong';
}

// isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./style/index.css">
  <meta name="theme-color" content="#451425">
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-straight/css/uicons-regular-straight.css'>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
  <link rel="icon" href="/assets/logobynna.svg" type="image/svg+xml">
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-brands/css/uicons-brands.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
  <title>Douce shop</title>
</head>

<body>


  <div class="con-item" id="here">

    <form action="index.php#here" method="GET" class="con-search" id="searchForm">
      <div class="con-input-dan-clear">
        <input type="text" name="search" id="searchInput" autocomplete="off" placeholder="Search Items..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <div id="clear"><i class="fi fi-rr-delete"></i></div>
      </div>


      <button type="submit"><i class="fi fi-rr-search"></i></button>
    </form>

    <?php
    if (mysqli_num_rows($hasil) > 0) {
      while ($row = mysqli_fetch_assoc($hasil)) {

        $gambarnya = "uploads/" . $row['gambar'];
        $harganya = 0;
        if ($row['diskon_barang'] == 0) {
          $harganya = $row['harga_barang'];
        } else {
          $harganya = $row['total_diskon'];
        }

        // Tentukan class untuk item habis
        $classHabis = ($row['stok_barang'] == 0) ? 'habis' : '';

        $classForDiscount = '';
        if ($row['diskon_barang'] == 0) {
          $showDiscount = false;
        } else {
          $showDiscount = true;
          if ($row['diskon_barang'] == 100) {
            $classForDiscount = 'dsc100';
          } elseif ($row['diskon_barang'] >= 75) {
            $classForDiscount = 'dsc75';
          } elseif ($row['diskon_barang'] >= 55) {
            $classForDiscount = 'dsc55';
          } elseif ($row['diskon_barang'] >= 35) {
            $classForDiscount = 'dsc35';
          } else {
            $classForDiscount = 'dscundr35';
          }
        }
    ?>

<div class="item <?= $classHabis ?>" onclick="showDetail(
  '<?= $row['id'] ?>',
  '<?= addslashes($row['nama_barang']) ?>',
  '<?= addslashes($row['deskripsi_barang']) ?>',
  '<?= $row['harga_barang'] ?>', // ✅ Kirim harga ASLI
  '<?= $row['diskon_barang'] ?>',
  '<?= $row['stok_barang'] ?>',
  '<?= $gambarnya ?>'
)">

        <div class="con-img">
          <img src="<?= $gambarnya ?>" alt="gambar barang">
        </div>
        <div class="con-konten">
          <p class="id"><?= $row['id'] ?></p>
          <h1 class="title"><?= $row['nama_barang'] ?></h1>
          <?php if ($row['diskon_barang'] > 0) : ?>
            <p class="diskon <?= $classForDiscount ?>"><?= $row['diskon_barang'] ?>% Diskon</p>
          <?php endif; ?>
          <p class="harga">Rp.<?= number_format($row['total_diskon']) ?></p>
        </div>
      </div>

    <?php
      }
    } else {
      if (!empty($search)) {
        echo "<p class='no-result'>Tidak ada barang dengan kata kunci '<strong>" . htmlspecialchars($search) . "</strong>'</p>";
      } else {
        echo "<p class='no-result'>Belum ada barang tersedia.</p>";
      }
    }
    ?>
  </div>
  <script>
    let clear = document.getElementById('clear');
    let searchInput = document.getElementById('searchInput');

    if (searchInput.value !== '') {
      clear.style.display = 'grid';
      searchInput.style.paddingRight = 'calc(50px + 1rem)';
    } else {
      clear.style.display = 'none';
    }

    if (clear) {
      clear.addEventListener('click', function() {
        searchInput.value = '';
        window.location.href = 'index.php?search=#here';
      });
    }


    const keranjangFloat = document.getElementById('keranjangFloat');
    addEventListener('scroll', () => {
      if (window.scrollY > 100) {
        keranjangFloat.style.bottom = '2rem'
      } else {
        keranjangFloat.style.bottom = '-6rem'
      }
    });
  </script>
</body>

</html>