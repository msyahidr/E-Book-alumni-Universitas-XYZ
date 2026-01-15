<?php
// ================== CORS & HEADER ==================
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight request (Flutter Web)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ================== KONEKSI DB ==================
$koneksi = mysqli_connect('localhost', 'root', '', 'perguruan_tinggi');
if (!$koneksi) {
    echo json_encode(["status" => "error", "pesan" => "Koneksi database gagal"]);
    exit;
}

// ================== AMBIL PARAMETER ==================
$aksi        = $_REQUEST["aksi"] ?? "";
$nim         = $_REQUEST["nim"] ?? "";
$nm_alumni  = $_REQUEST["nm_alumni"] ?? "";
$prodi      = $_REQUEST["prodi"] ?? "";
$tmpt_lahir = $_REQUEST["tmpt_lahir"] ?? "";
$tgl_lahir  = $_REQUEST["tgl_lahir"] ?? "";
$alamat     = $_REQUEST["alamat"] ?? "";
$no_hp      = $_REQUEST["no_hp"] ?? "";
$thn_lulus  = $_REQUEST["thn_lulus"] ?? "";
$foto       = $_REQUEST["foto"] ?? "";

// ================== AKSI ==================
switch ($aksi) {

    // ====== TAMPIL DATA ======
    case "tampil":
        $sql = "SELECT * FROM alumni ORDER BY nm_alumni";
        $result = mysqli_query($koneksi, $sql);

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        echo json_encode($data);
        break;

    // ====== SIMPAN DATA ======
    case "simpan":
        $sql = "INSERT INTO alumni 
                (nim, nm_alumni, prodi, tmpt_lahir, tgl_lahir, alamat, no_hp, thn_lulus)
                VALUES 
                ('$nim', '$nm_alumni', '$prodi', '$tmpt_lahir', '$tgl_lahir', '$alamat', '$no_hp', '$thn_lulus')";

        if (mysqli_query($koneksi, $sql)) {

            if (!empty($foto)) {
                if (!is_dir("foto")) {
                    mkdir("foto", 0777, true);
                }
                file_put_contents("foto/$nim.jpeg", base64_decode($foto));
            }

            echo json_encode(["status" => "berhasil"]);
        } else {
            echo json_encode(["status" => "gagal", "error" => mysqli_error($koneksi)]);
        }
        break;

    // ====== UBAH DATA ======
    case "ubah":
        $sql = "UPDATE alumni SET 
                    nm_alumni='$nm_alumni',
                    prodi='$prodi',
                    tmpt_lahir='$tmpt_lahir',
                    tgl_lahir='$tgl_lahir',
                    alamat='$alamat',
                    no_hp='$no_hp',
                    thn_lulus='$thn_lulus'
                WHERE nim='$nim'";

        if (mysqli_query($koneksi, $sql)) {

            if (!empty($foto) && strlen($foto) > 100) {
                if (!is_dir("foto")) {
                    mkdir("foto", 0777, true);
                }
                file_put_contents("foto/$nim.jpeg", base64_decode($foto));
            }

            echo json_encode(["status" => "berhasil"]);
        } else {
            echo json_encode(["status" => "gagal"]);
        }
        break;

    // ====== HAPUS DATA ======
    case "hapus":
        $sql = "DELETE FROM alumni WHERE nim='$nim'";
        if (mysqli_query($koneksi, $sql)) {

            if (file_exists("foto/$nim.jpeg")) {
                unlink("foto/$nim.jpeg");
            }

            echo json_encode(["status" => "berhasil"]);
        } else {
            echo json_encode(["status" => "gagal"]);
        }
        break;

    // ====== AKSI TIDAK VALID ======
    default:
        echo json_encode(["status" => "error", "pesan" => "Aksi tidak valid"]);
        break;
}
?>
