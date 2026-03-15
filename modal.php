  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style/modal.css">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-straight/css/uicons-bold-straight.css'>
    <title>Document</title>
  </head>

  <body>
    <div id="modal" class="modal">
      <form method="POST" class="modal-content">
        <div class="con-modal-content">
          <div class="header-modal">
            <div class="con-info-harga">
              <h1 id="mNama"></h1>

              <p class="harga-modal" id="hargaNormal">Rp.<span id="mHarga"></span></p>

              <div id="diskonInfo" style="display: none;">
                <p class="harga-modal" style="text-decoration: line-through; color: #999;">Rp.<span id="mHargaCoret"></span></p>
                <p style="color: #27ae60; font-weight: bold;" class="harga-modal">Rp.<span id="mHargaDiskon"></span></p>
                <p class="diskon-modal <?= $classForDiscount ?>"><span id="mDiskon"></span>%</p>
              </div>

            </div>
            <span onclick="closeModal()" class="close">
              <img src="./assets/close.png" alt="" srcset="">
            </span>
          </div>


          <div class="con-content">

            <div class="con-image">
              <img id="mGambar" src="">
            </div>

            <div class="con-context">
              <p>Stok Tersedia: <span id="mStok"></span></p>
              <p id="mDeskripsi"></p>


            </div>

            <input type="hidden" name="id_barang" id="formIdBarang">
            <input type="hidden" name="gambar_barang" id="formGambarBarang">
            <input type="hidden" name="nama_barang" id="formNamaBarang">
            <input type="hidden" name="harga_barang" id="formHargaBarang">
            <input type="hidden" name="deskripsi_barang" id="formDeskripsiBarang">
            <input type="hidden" name="diskon_barang" id="formDiskon">
            <input type="hidden" name="stok_barang" id="formStok">

          </div>

          <div class="con-button" id="con-button">
            <div class="con-jumlah">
              <p>Jumlah : </p>
              <div class="jumlah-wrapper">
                <button type="button" class="btn-jumlah" onclick="kurangiJumlah()">−</button>
                <input type="number" name="jumlah" id="formJumlah" value="1" min="1">
                <button type="button" class="btn-jumlah" onclick="tambahJumlah()">+</button>
              </div>
            </div>
            <button class="addtochart" type="submit" name="add_to_cart">Pesan <i class="fi fi-rr-cart-arrow-down"></i></button>
          </div>

        </div>
      </form>
    </div>

    <script>
      function showDetail(id, nama, deskripsi, harga, diskon, stok, gambar) {

        document.getElementById('mNama').innerText = nama;
        document.getElementById('mDeskripsi').innerText = deskripsi;
        document.getElementById('mDiskon').innerText = diskon;
        document.getElementById('mStok').innerText = stok;
        document.getElementById('mGambar').src = gambar;

        document.getElementById('formIdBarang').value = id;
        document.getElementById('formGambarBarang').value = gambar;
        document.getElementById('formNamaBarang').value = nama;
        document.getElementById('formHargaBarang').value = harga;
        document.getElementById('formDeskripsiBarang').value = deskripsi;
        document.getElementById('formDiskon').value = diskon;
        document.getElementById('formStok').value = stok;

        document.getElementById('formJumlah').max = stok;
        document.getElementById('formJumlah').value = 1; // Reset ke 1

        // Tampilkan atau sembunyikan diskon
        if (diskon > 0) {
          const hargaAsli = parseFloat(harga);
          const hargaSetelahDiskon = hargaAsli - (hargaAsli * diskon / 100);

          document.getElementById('mHargaCoret').innerText =
            new Intl.NumberFormat('id-ID').format(hargaAsli);

          document.getElementById('mHargaDiskon').innerText =
            new Intl.NumberFormat('id-ID').format(hargaSetelahDiskon);

          document.getElementById('hargaNormal').style.display = 'none';
          document.getElementById('diskonInfo').style.display = 'flex';
        } else {
          document.getElementById('mHarga').innerText =
            new Intl.NumberFormat('id-ID').format(harga);

          document.getElementById('hargaNormal').style.display = 'block';
          document.getElementById('diskonInfo').style.display = 'none';
        }

        const diskonElem = document.querySelector(".diskon-modal");
        diskonElem.className = "diskon-modal";

        if (diskon > 0) {
          if (diskon == 100) {
            diskonElem.classList.add("dsc100");
          } else if (diskon >= 75) {
            diskonElem.classList.add("dsc75");
          } else if (diskon >= 55) {
            diskonElem.classList.add("dsc55");
          } else if (diskon >= 35) {
            diskonElem.classList.add("dsc35");
          } else {
            diskonElem.classList.add("dscundr35");
          }
        }

        // ✅ Tampilkan modal dengan animasi
        const modal = document.getElementById('modal');
        const conButton = document.getElementById('con-button');
        const allHide = [modal, conButton]
        allHide.forEach(element => {
          element.classList.remove('hide');
          element.style.display = 'flex';

          // ✅ Trigger reflow untuk memastikan animasi berjalan
          void modal.offsetWidth;

          // ✅ Tambahkan class show dan prevent body scroll
          element.classList.add('show');

        });
        document.body.classList.add('modal-open');

      }


      function closeModal() {
        const modal = document.getElementById('modal');
        const conButton = document.getElementById('con-button');
        const allShow = [modal, conButton]
        allShow.forEach(element => {
          element.classList.remove('show');
          element.classList.add('hide');

          setTimeout(() => {
            element.style.display = 'none';
            element.classList.remove('hide');
            document.body.classList.remove('modal-open');
          }, 800); // Sesuai dengan transition duration di CSS (0.4s)
        });


        // ✅ Tunggu animasi selesai (400ms) baru hilangkan modal

      }

      // ✅ Tutup modal jika klik di luar konten modal (background)
      window.onclick = function(event) {
        const modal = document.getElementById('modal');
        if (event.target === modal) {
          closeModal();
        }
      }

      // ✅ Tutup modal dengan tombol ESC
      document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
          const modal = document.getElementById('modal');
          if (modal.classList.contains('show')) {
            closeModal();
          }
        }
      });

      function kurangiJumlah() {
        let input = document.getElementById('formJumlah');
        let value = parseInt(input.value);

        if (value > 1) {
          input.value = value - 1;
        }
      }

      function tambahJumlah() {
        let input = document.getElementById('formJumlah');
        let value = parseInt(input.value);
        let max = parseInt(input.max);

        if (value < max) {
          input.value = value + 1;
        }
      }
    </script>
  </body>

  </html>