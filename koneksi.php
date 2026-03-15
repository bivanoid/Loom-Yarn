
<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'bynna_db';
$koneksi = mysqli_connect($servername, $username, $password, $dbname);
if ($koneksi->connect_error) {
  die("Koneksi gagal" . $koneksi->connect_error);
}
?> 


<!-- 

<?php
// $servername = 'sql100.infinityfree.com';
// $username = 'if0_40534352';
// $password = 'bynnaaapw';
// $dbname = 'if0_40534352_items';
// $koneksi = mysqli_connect($servername, $username, $password, $dbname);
// if ($koneksi->connect_error) {
//   die("Koneksi gagal" . $koneksi->connect_error);
// }
?> -->
