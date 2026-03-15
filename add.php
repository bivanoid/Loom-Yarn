<?php
include 'koneksi.php';
include 'alert_popup.php';

$show_preview = false;

// ✅ FUNGSI UNTUK KOMPRESI & RESIZE GAMBAR
function compressAndResizeImage($source, $destination, $quality = 75, $maxWidth = 800, $maxHeight = 800)
{

  // Dapatkan informasi gambar
  $imageInfo = getimagesize($source);
  $mime = $imageInfo['mime'];

  // Buat resource gambar berdasarkan tipe
  switch ($mime) {
    case 'image/jpeg':
      $image = imagecreatefromjpeg($source);
      break;
    case 'image/png':
      $image = imagecreatefrompng($source);
      break;
    case 'image/gif':
      $image = imagecreatefromgif($source);
      break;
    case 'image/webp':
      $image = imagecreatefromwebp($source);
      break;
    default:
      return false;
  }

  if (!$image) return false;

  // Dapatkan dimensi asli
  $originalWidth = imagesx($image);
  $originalHeight = imagesy($image);

  // Hitung dimensi baru dengan mempertahankan aspect ratio
  $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);

  // Jika gambar sudah lebih kecil dari max, gunakan ukuran asli
  if ($ratio >= 1) {
    $newWidth = $originalWidth;
    $newHeight = $originalHeight;
  } else {
    $newWidth = round($originalWidth * $ratio);
    $newHeight = round($originalHeight * $ratio);
  }

  // Buat gambar baru dengan ukuran yang sudah di-resize
  $newImage = imagecreatetruecolor($newWidth, $newHeight);

  // Pertahankan transparansi untuk PNG dan GIF
  if ($mime == 'image/png' || $mime == 'image/gif') {
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);
    $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
    imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
  }

  // Resize gambar
  imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

  // Simpan gambar berdasarkan tipe dengan kompresi
  $success = false;
  switch ($mime) {
    case 'image/jpeg':
      $success = imagejpeg($newImage, $destination, $quality);
      break;
    case 'image/png':
      // PNG quality: 0 (no compression) to 9 (max compression)
      $pngQuality = round((100 - $quality) / 10);
      $success = imagepng($newImage, $destination, $pngQuality);
      break;
    case 'image/gif':
      $success = imagegif($newImage, $destination);
      break;
    case 'image/webp':
      $success = imagewebp($newImage, $destination, $quality);
      break;
  }

  // Bersihkan memory
  imagedestroy($image);
  imagedestroy($newImage);

  return $success;
}

// ✅ FUNGSI UNTUK CONVERT KE WEBP (FORMAT PALING EFISIEN)
function convertToWebP($source, $destination, $quality = 80)
{
  $imageInfo = getimagesize($source);
  $mime = $imageInfo['mime'];

  switch ($mime) {
    case 'image/jpeg':
      $image = imagecreatefromjpeg($source);
      break;
    case 'image/png':
      $image = imagecreatefrompng($source);
      break;
    case 'image/gif':
      $image = imagecreatefromgif($source);
      break;
    default:
      return false;
  }

  if (!$image) return false;

  // Pertahankan transparansi
  imagealphablending($image, true);
  imagesavealpha($image, true);

  // Simpan sebagai WebP
  $success = imagewebp($image, $destination, $quality);

  imagedestroy($image);
  return $success;
}

if (isset($_POST['tambahkan_barang'])) {
  $nama_barang = $_POST['input_nama_barang'];
  $deskripsi_barang = $_POST['input_deskripsi_barang'];
  $harga_barang = $_POST['input_harga_barang'];
  $diskon_barang = $_POST['input_diskon_barang'];
  $stok_barang = $_POST['input_stok_barang'];

  $empty_barang_check = [$nama_barang, $harga_barang, $diskon_barang, $stok_barang];

  if (in_array("", $empty_barang_check, true)) {
    echo '<script>showAlert("Mohon diisi dulu yaaa")</script>';
  } else {
    if ($harga_barang < 500) {
      echo '<script>showAlert("Harga tidak boleh kurang dari Rp.500")</script>';
    } elseif ($diskon_barang < 0) {
      echo '<script>showAlert("Diskon tidak boleh kurang dari 0")</script>';
    } elseif ($stok_barang <= 0) {
      echo '<script>showAlert("Stok minimal 1 yaaa")</script>';
    } else {

      if ($diskon_barang > 0) {
        $hitung_diskon = $harga_barang * $diskon_barang / 100;
        $total_diskon = $harga_barang - $hitung_diskon;
      } else {
        $total_diskon = $harga_barang;
      }

      $gambar_barang = "";

      if (isset($_FILES['input_gambar']) && $_FILES['input_gambar']['error'] === 0) {

        // ✅ CEK UKURAN FILE (MAX 5MB)
        $maxFileSize = 10 * 1024 * 1024; // 5MB dalam bytes
        if ($_FILES['input_gambar']['size'] > $maxFileSize) {
          echo '<script>showAlert("Ukuran gambar terlalu besar! Maksimal 5MB")</script>';
        } else {

          $folder = "uploads/";
          if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
          }

          // ✅ VALIDASI TIPE FILE
          $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
          $fileType = $_FILES['input_gambar']['type'];

          if (!in_array($fileType, $allowedTypes)) {
            echo '<script>showAlert("Format gambar tidak didukung! Gunakan JPG, PNG, GIF, atau WebP")</script>';
          } else {

            // ✅ GENERATE NAMA FILE UNIK
            $extension = pathinfo($_FILES['input_gambar']['name'], PATHINFO_EXTENSION);
            $nama_file = uniqid('product_', true) . '.' . $extension;
            $temp_file = $_FILES['input_gambar']['tmp_name'];
            $target_file = $folder . $nama_file;

            // ✅ OPSI 1: COMPRESS & RESIZE (RECOMMENDED)
            // Resize ke max 800x800px dengan quality 75%
            if (compressAndResizeImage($temp_file, $target_file, 75, 800, 800)) {
              $gambar_barang = $nama_file;

              // ✅ DAPATKAN UKURAN FILE SEBELUM DAN SESUDAH
              $originalSize = $_FILES['input_gambar']['size'];
              $compressedSize = filesize($target_file);
              $savedSpace = $originalSize - $compressedSize;
              $percentage = round(($savedSpace / $originalSize) * 100, 2);

              // Log info (opsional)
              error_log("Compressed: " . number_format($originalSize / 1024, 2) . "KB -> " . number_format($compressedSize / 1024, 2) . "KB (Saved $percentage%)");
            } else {
              echo '<script>showAlert("Gagal memproses gambar!")</script>';
            }

            // ✅ OPSI 2: CONVERT KE WEBP (OPTIONAL - PALING EFISIEN)
            // Uncomment jika ingin convert semua gambar ke WebP
            /*
            $webp_file = $folder . pathinfo($nama_file, PATHINFO_FILENAME) . '.webp';
            if (convertToWebP($temp_file, $webp_file, 80)) {
              // Resize WebP
              if (compressAndResizeImage($webp_file, $webp_file, 80, 800, 800)) {
                $gambar_barang = pathinfo($webp_file, PATHINFO_BASENAME);
              }
            }
            */
          }
        }
      }

      // MASUKKAN KE DATABASE
      if (!empty($gambar_barang)) {
        mysqli_query(
          $koneksi,
          "INSERT INTO items (nama_barang, deskripsi_barang, gambar, harga_barang, diskon_barang, stok_barang, total_diskon)
           VALUES ('$nama_barang', '$deskripsi_barang', '$gambar_barang', '$harga_barang', '$diskon_barang', '$stok_barang', '$total_diskon')"
        );

        $show_preview = true;
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#451425">
  <title>Tambah barang</title>
  <link rel="stylesheet" href="./style/add.css">
</head>

<body>
  <div class="security" id="securityView">
    <div class="con-input">
      <h1 for="input-password">Password</h1>
      <p id="subtitlePass">Mastiin klo kamu tuh admind mwehe</p>
      <input type="text" name="input-password" id="inputPassword" placeholder="admin123 :3">
      <button id="cekPassword" type="submit" onclick="cek()">Cek <i class="fi fi-rr-unlock"></i></button>
      <a href="keranjang.php">Kembali</a>
    </div>
  </div>
  <?php include './header-add.php' ?>
  <div class="container">
    <div class="info-box">
      <b>Note :</b> Semua input wajib diisi yaa. Ukuran gambar maksimal 5MB. Gambarnya otomatis bakal kekompress kok, mohon maaf klo jadi burik dikit :v.
    </div>

    <form action="" method="POST" enctype="multipart/form-data">

      <div class="form-group">
        <label>Gambar Produk (Max 5MB)</label>
        <input type="file" name="input_gambar" id="fileInput" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
        <div class="file-info" id="fileInfo"></div>

        <!-- ✅ Preview gambar sebelum upload -->
        <div class="image-preview-container" id="previewContainer">
          <label>Preview:</label>
          <img id="imagePreview" class="image-preview" src="" alt="Preview">
        </div>
      </div>

      <div class="form-group">
        <label>Nama Produk</label>
        <input value="<?= $_POST['input_nama_barang'] ?? '' ?>" type="text" autocomplete="off" name="input_nama_barang" placeholder="Contohnya : Patung Bakwan">
      </div>

      <div class="form-group">
        <label>Deskripsi Produk</label>
        <textarea name="input_deskripsi_barang" autocomplete="off" placeholder="Deskripsiin Produk Barumu...."><?= $_POST['input_deskripsi_barang'] ?? '' ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Harga (Rp)</label>
          <input class="harganya" value="<?= $_POST['input_harga_barang'] ?? '' ?>" type="number" name="input_harga_barang" placeholder="Harga gak boleh dibawah Rp.500 yaa" min="500">
        </div>

        <div class="form-group">
          <label>Diskon (%)</label>
          <input value="<?= $_POST['input_diskon_barang'] ?? '0' ?>" type="number" name="input_diskon_barang" placeholder="0" min="0" max="100">
        </div>

        <div class="form-group">
          <label>Stok</label>
          <input value="<?= $_POST['input_stok_barang'] ?? '' ?>" type="number" name="input_stok_barang" placeholder="Stok gak boleh kurang dari 1" min="1">
        </div>
      </div>



      <button type="submit" name="tambahkan_barang">Tambahkan <i class="fi fi-rr-shopping-cart-add"></i></button>
    </form>
  </div>

  <!-- Preview overlay tetap sama -->
  <?php if ($show_preview) : ?>
    <div class="preview-overlay">
      <div class="preview">
        <h2>Produk Berhasil Ditambahkan!</h2>

        <div class="preview-grid">
          <?php if (isset($gambar_barang) && !empty($gambar_barang)) : ?>
            <img src="uploads/<?= $gambar_barang ?>" alt="Preview Produk" class="preview-image">
          <?php endif; ?>

          <div class="preview-item">
            <label>Nama Produk</label>
            <p><?= isset($nama_barang) ? $nama_barang : '' ?></p>
          </div>

          <div class="preview-item">
            <label>Harga Normal</label>
            <p class="preview-price">Rp. <?= isset($harga_barang) ? number_format($harga_barang, 0, ',', '.') : '0' ?></p>
          </div>

          <?php if (isset($diskon_barang) && $diskon_barang > 0) : ?>
            <div class="preview-item">
              <label>Diskon</label>
              <p><?= $diskon_barang ?>%</p>
              <span class="preview-discount">Discount <?= $diskon_barang ?>%</span>
            </div>

            <div class="preview-item">
              <label>Harga Setelah Diskon</label>
              <p class="preview-price">Rp. <?= number_format($total_diskon, 0, ',', '.') ?></p>
            </div>
          <?php endif; ?>

          <div class="preview-item">
            <label>Stok Tersedia</label>
            <p><?= isset($stok_barang) ? $stok_barang : '0' ?> unit</p>
          </div>

          <?php if (isset($deskripsi_barang) && !empty($deskripsi_barang)) : ?>
            <div class="preview-item" style="grid-column: 1 / -1;">
              <label>Deskripsi</label>
              <p><?= $deskripsi_barang ?></p>
            </div>
          <?php endif; ?>
        </div>



        <a href="index.php" class="btn-back">
          <p>Kembali ke Beranda</p>
        </a>
      </div>
    </div>
  <?php endif; ?>
  <script>
    // ✅ PREVIEW GAMBAR SEBELUM UPLOAD & CEK UKURAN
    document.getElementById('fileInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const fileInfo = document.getElementById('fileInfo');
      const previewContainer = document.getElementById('previewContainer');
      const imagePreview = document.getElementById('imagePreview');

      if (file) {
        const fileSize = file.size;
        const fileSizeInMB = (fileSize / (1024 * 1024)).toFixed(2);
        const maxSize = 5; // 5MB

        // Tampilkan info ukuran file
        if (fileSizeInMB > maxSize) {
          fileInfo.className = 'file-info warning';
          fileInfo.textContent = `⚠️ Ukuran file: ${fileSizeInMB}MB (Terlalu besar! Max ${maxSize}MB)`;
          previewContainer.classList.remove('show');
        } else {
          fileInfo.className = 'file-info';
          fileInfo.textContent = `✓ Ukuran file: ${fileSizeInMB}MB (Akan dikompresi otomatis)`;

          // Preview gambar
          const reader = new FileReader();
          reader.onload = function(event) {
            imagePreview.src = event.target.result;
            previewContainer.classList.add('show');
          };
          reader.readAsDataURL(file);
        }
      } else {
        fileInfo.textContent = '';
        previewContainer.classList.remove('show');
      }
    });

    const inputPassword = document.getElementById('inputPassword');
    const securityView = document.getElementById('securityView');
    const cekPassword = document.getElementById('cekPassword');
    const subtitlePass = document.getElementById('subtitlePass');

    const SECURITY_KEY = "admin_access_until"; // key penyimpanan

    // Durasi akses: misal 3 jam
    const ACCESS_DURATION = 3 * 60 * 60 * 1000; // 3 jam dalam ms

    function checkAdminAccess() {
      const savedTime = localStorage.getItem(SECURITY_KEY);

      if (savedTime) {
        const expireTime = parseInt(savedTime);
        const now = Date.now();

        if (now < expireTime) {
          // masih berlaku
          securityView.style.display = "none";
          document.body.style.overflow = "auto";
          return true;
        }
      }

      // expired → tetap tampil
      securityView.style.display = "flex";
      document.body.style.overflow = "hidden";
      return false;
    }

    // Jalankan saat halaman dibuka
    checkAdminAccess();


    let count = 0;
    var passwordnya = 'bynok'

    function cek() {
      if (inputPassword.value === passwordnya) {

        // Hitung waktu kadaluarsa
        const expireTime = Date.now() + ACCESS_DURATION;

        // Simpan ke localStorage
        localStorage.setItem(SECURITY_KEY, expireTime);

        securityView.style.display = 'none';
        document.body.style.overflow = 'auto';
        subtitlePass.textContent = 'Passwordnya benarr';

        count = 0;
        return;
      } else if (inputPassword.value === '') {
        subtitlePass.textContent = 'Isi passwordnya dlu lah banh';
      } else {
        count++;

        securityView.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        inputPassword.style.animation = 'none';
        inputPassword.offsetHeight;
        inputPassword.style.animation = 'shake 1s linear forwards';
        inputPassword.style.border = '1px solid #d00b0bff';
        inputPassword.value = '';

        function cobaLagi(a) {
          setTimeout(() => {
            subtitlePass.textContent = 'Coba lagii yaaa';
            inputPassword.placeholder = "Ketikan Password";
            inputPassword.disabled = false;
          }, a);
        }

        if (count == 1) {
          subtitlePass.textContent = 'Yahh passwordmu salahhh :(';
          var timer = 2500;
          inputPassword.placeholder = "Cooldown " + (timer / 1000) + " detik";
          inputPassword.disabled = true;
          cobaLagi(timer);
        }

        if (count == 2) {
          subtitlePass.textContent = 'Yahh salah lagi, coba inget-inget lagi :)';
          var timer = 3500;
          inputPassword.placeholder = "Cooldown " + (timer / 1000) + " detik";
          inputPassword.disabled = true;
          cobaLagi(timer);
        }

        if (count == 3) {
          subtitlePass.textContent = 'Lololo kok salah lagii?, km cape?';
          var timer = 3000;
          inputPassword.placeholder = "Cooldown " + (timer / 1000) + " detik";
          inputPassword.disabled = true;
          cobaLagi(timer);
        }

        if (count == 4) {
          subtitlePass.textContent = 'Dawg masi muda lhoo, jan pikun';
          var timer = 3500;
          inputPassword.placeholder = "Cooldown " + (timer / 1000) + " detik";
          inputPassword.disabled = true;
          cobaLagi(timer);
        }

        if (count == 5) {
          subtitlePass.textContent = 'Aku kasi clue deh. ' + passwordnya[0] + ' ... ' + passwordnya[passwordnya.length - 1];
          var timer = 5000;
          inputPassword.placeholder = "Cooldown " + (timer / 1000) + " detik";
          inputPassword.disabled = true;
          cobaLagi(timer);
        }

        if (count == 6) {
          subtitlePass.textContent = 'Yap anda bukan admin, pergi kao';
          inputPassword.disabled = true;
          setTimeout(() => {
            window.location.href = "keranjang.php"
          }, 2000);
        }
      }
    }
  </script>

</body>

</html>