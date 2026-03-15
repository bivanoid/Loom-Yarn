<?php
session_start();


require 'vendor/autoload.php';
include 'koneksi.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!$koneksi) {
  die("Koneksi gagal: " . mysqli_connect_error());
}

// Hapus item
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['id'] == $id) {
      unset($_SESSION['cart'][$key]);
      break;
    }
  }
  $_SESSION['cart'] = array_values($_SESSION['cart']);
  header("Location: keranjang.php");
  exit;
}

// Hapus semua
if (isset($_GET['hapus_semua'])) {
  unset($_SESSION['cart']);
  header("Location: keranjang.php");
  exit;
}

if (isset($_POST['kirim_wa'])) {
  if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {

    // Format pesan WhatsApp
    $pesan = "*WAHH ADA PESANAN BARUU*%0A";
    $pesan .= "=================================%0A%0A";

    $totalSemua = 0;
    $nomorUrut = 1;

    foreach ($_SESSION['cart'] as $item) {
      // Hitung harga setelah diskon
      $hargaSetelahDiskon = $item['harga'];
      if (isset($item['diskon']) && $item['diskon'] > 0) {
        $hargaSetelahDiskon = $item['harga'] - ($item['harga'] * $item['diskon'] / 100);
      }

      $totalPerBarang = $hargaSetelahDiskon * $item['jumlah'];
      $totalSemua += $totalPerBarang;

      $pesan .= $nomorUrut . ". " . $item['nama'] . "%0A";

      // Tampilkan harga asli jika ada diskon
      if (isset($item['diskon']) && $item['diskon'] > 0) {
        $pesan .= "   - Harga Normal: Rp" . number_format($item['harga'], 0, ',', '.') . "%0A";
        $pesan .= "   - Diskon: " . $item['diskon'] . "%%0A";
        $pesan .= "   - Harga Diskon: Rp" . number_format($hargaSetelahDiskon, 0, ',', '.') . "%0A";
      } else {
        $pesan .= "   - Harga: Rp" . number_format($item['harga'], 0, ',', '.') . "%0A";
      }

      $pesan .= "   - Jumlah: " . $item['jumlah'] . " unit%0A";
      $pesan .= "   - Subtotal: Rp" . number_format($totalPerBarang, 0, ',', '.') . "%0A%0A";

      // Update stok di database
      $id_barang = mysqli_real_escape_string($koneksi, $item['id']);
      $jumlah_beli = (int)$item['jumlah'];

      $query = "UPDATE items SET stok_barang = stok_barang - $jumlah_beli WHERE id = $id_barang";
      mysqli_query($koneksi, $query);

      $nomorUrut++;
    }

    $pesan .= "=================================%0A";
    $pesan .= "*TOTAL PEMBAYARAN: Rp" . number_format($totalSemua, 0, ',', '.') . "*%0A";
    $pesan .= "=================================%0A%0A";
    $pesan .= "_Makasii udaa mau belanjaa 💗_";

    // ✅ Kosongkan keranjang SEBELUM redirect
    unset($_SESSION['cart']);

    // ✅ Regenerate session untuk keamanan
    session_regenerate_id(true);

    $nomorWA = $_ENV['NO_HP'];

    // ✅ Pastikan tidak ada output sebelum ini
    header("Location: https://wa.me/$nomorWA?text=$pesan");
    exit;
  } else {
    // ✅ Kalau keranjang kosong, redirect ke keranjang
    header("Location: keranjang.php");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">


<head>
  <meta charset="UTF-8">
  <meta name="theme-color" content="#451425">
  <title>Keranjang Belanja</title>
  <link rel="stylesheet" href="./style/keranjang.css">
  <link rel="icon" href="/assets/logobynna.svg" type="image/svg+xml">
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-brands/css/uicons-brands.css'>
</head>

<body>
  <?php include './header-chart.php';
  include './loading.php'; ?>
  <?php if (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) : ?>
    <div class="kalau-kosong">
      <h1>Keranjangmu masih kosong.</h1>
      <p>Coba deh pesan sesuatu :3</p> <a href="index.php">Beranda</a>
    </div>
  <?php else : ?>
    <div class="con-item-chart">
      <?php
      $totalSemua = 0;
      foreach ($_SESSION['cart'] as $item) :
        // Hitung harga setelah diskon
        $hargaSetelahDiskon = $item['harga'];
        $adaDiskon = false;

        if (isset($item['diskon']) && $item['diskon'] > 0) {
          $hargaSetelahDiskon = $item['harga'] - ($item['harga'] * $item['diskon'] / 100);
          $adaDiskon = true;
        }

        $total = $hargaSetelahDiskon * $item['jumlah'];
        $totalSemua += $total;
      ?>

        <div class="item">
          <div class="con-img">
            <img src="<?= $item['gambar']; ?>" alt="<?= $item['nama']; ?>">
          </div>
          <div class="con-isi">
            <p class="id"><?php echo $item['id'] ?></p>
            <div class="con-harga-diskon">
              <h1 class="title"><?php echo $item['nama'] ?></h1>
            </div>

            <?php if ($adaDiskon) : ?>
              <div class="con-diskon">
                <span class="badge-diskon"><?= $item['diskon'] ?>% OFF</span>
                <p class="harga-coret">Rp.<?php echo number_format($item['harga'], 0, ',', '.') ?></p>
                <p class="harga">Rp.<?php echo number_format($hargaSetelahDiskon, 0, ',', '.') ?></p>
              </div>
            <?php else : ?>
              <div class="con-diskon">
                <p class="harga">Rp.<?php echo number_format($item['harga'], 0, ',', '.') ?></p>
              </div>

            <?php endif; ?>

            <p class="unit"><?= $item['jumlah'] ?> unit</p>
          </div>

          <div class="con-hapus">
            <a href="?hapus=<?= $item['id'] ?>">
              <i class="fi fi-rr-trash"></i>
            </a>
          </div>
        </div>

      <?php endforeach; ?>

    </div>

    <div class="con-total">
      <div class="con-pembayaran">
        <p><span>Total : </span>Rp<?= number_format($totalSemua, 0, ',', '.') ?></p>
        <a href="?hapus_semua=true">Delete All</a>
      </div>

      <form method="POST" action="">
        <button type="submit" name="kirim_wa">Chat Whatsapp <i class="fi fi-brands-whatsapp"></i></button>
      </form>
    </div>

    <?php include './modal.php' ?>
  <?php endif; ?>

</body>

</html>