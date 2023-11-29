<?php 
require_once "koneksi.php";
if(function_exists($_GET['function'])) {
    $_GET['function']();
}

function cekTelephoneLogin(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);
    $tlp = $_GET['telepon'];

        $query = "select * from user where no_telepon = '$tlp'";
        $query_result = mysqli_query($conn, $query);
        $check = mysqli_fetch_array($query_result);
        if (isset($check)) {
            $query_result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($query_result)) {
                // $row['photo_profile'] = base64_encode($row['photo_profile']);
                $json_array[] = $row;
            }                
            $response = array(
                'code' => 200,
                'status' => 'User ditemukan',
                'user_list' => $json_array
            );
        } else {
            $response = array(
                'code' => 404,
                'status' => 'Data tidak ditemukan, silahkan registrasi'
            );    
        }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function register(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $json = file_get_contents('php://input', true);
    $obj = json_decode($json);

    $nama = $obj->nama;
    $alamat = $obj->alamat;
    $tlp = $obj->telepon;
    $pertanyaan = $obj->pertanyaan;
    $jawaban = $obj->jawaban;
    $pass = $obj->password;

    if (!empty($nama) && !empty($alamat) && !empty($tlp) && !empty($pertanyaan) && !empty($jawaban) && !empty($pass)) {
        
        $query = "SELECT * FROM user WHERE no_telepon = '$tlp'";
        $query_result = mysqli_query($conn, $query);
        $check = mysqli_fetch_array($query_result);

        if (isset($check)) {
            $cekPass = $check['password'];

            if ($cekPass != null) {
                $response = array(
                    'code' => 101,
                    'status' => 'No Telepon sudah terdaftar'
                );
            } else {
                $query = "UPDATE user SET nama='$nama',alamat='$alamat',pertanyaan='$pertanyaan', 
                            jawaban='$jawaban',password='$pass' WHERE no_telepon = '$tlp'";

                if ($result = mysqli_query($conn, $query)) {
                    $response = array(
                        'code' => 200,
                        'status' => 'Registrasi berhasil'
                    );
                } else {
                    $response = array(
                        'code' => 205,
                        'status' => 'Registrasi gagal'
                    );
                }
            }
        } else {
            // Email belum terdaftar, lanjutkan dengan penyisipan data
            $query = "INSERT INTO user (nama, alamat, no_telepon, pertanyaan, jawaban, password, akses) VALUES ('$nama', '$alamat', '$tlp', '$pertanyaan', '$jawaban', '$pass', 'customer')";

            if ($result = mysqli_query($conn, $query)) {
                $response = array(
                    'code' => 200,
                    'status' => 'Registrasi berhasil'
                );
            } else {
                $response = array(
                    'code' => 205,
                    'status' => 'Registrasi gagal'
                );
            }
        }
    } else {
        $response = array(
            'code' => 100,
            'status' => 'Lengkapi Data Yang Dibutuhkan'
        );
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function updateProfile(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);
    $json = file_get_contents('php://input', true);
    $obj = json_decode($json);

    $photo = $obj->photo_profile;
    // $photo_decode = base64_decode($photo);
    $nama = $obj->nama;
    $alamat = $obj->alamat;
    $tlp_old = $obj->telepon_old;
    $tlp_new = $obj->telepon_new;
    $id_photo = $tlp_new.".jpg";

    $photo_filename = "image/" . $tlp_new . ".jpg";
    if(file_put_contents($photo_filename, base64_decode($photo))){
        $responUpload = "berhasil upload";
    }else{
        $responUpload = "gagal upload";
    }

    // $query_update= "update user set photo_profile = '$photo_decode', nama = '$nama', alamat = '$alamat',
    //                  no_telepon = '$tlp_new' where no_telepon = '$tlp_old'";

    // $query = mysqli_query($conn, $query_update);
    // $check = mysqli_affected_rows($conn);
    // $json_array = array();
    // $response = "";

    $query_update = "UPDATE user SET photo_profile = ? , nama = ?, alamat = ?, no_telepon = ? WHERE no_telepon = ?";

    $stmt = mysqli_prepare($conn, $query_update);
    mysqli_stmt_bind_param($stmt, "sssss", $id_photo, $nama, $alamat, $tlp_new, $tlp_old);
    // mysqli_stmt_send_long_data($stmt, 0, $photo_decode); // Bind data gambar sebagai BLOB

    $query = mysqli_stmt_execute($stmt);

        if ($query) {
            $response = array(
                'code' => 200,
                'status' => 'Data sudah diperbarui!',
                'upload' => $responUpload
            );
        } else {
            $response = array(
                'code' => 400,
                'status' => 'Gagal diperbarui!',
                'upload' => $responUpload
            );
        }

        print(json_encode($response));
        mysqli_close($conn);
}

function gantiPassword(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $pass = $_GET["password"];
    $tlp = $_GET["telepon"];

    $query_update= "update user set password = '$pass' where no_telepon = '$tlp'";

    $query = mysqli_query($conn, $query_update);
    $check = mysqli_affected_rows($conn);
    $json_array = array();
    $response = "";

        if (isset($check)) {
            $response = array(
                'code' => 200,
                'status' => 'Password Berhasil Diganti'
            );
        } else {
            $response = array(
                'code' => 400,
                'status' => 'Password Gagal Diganti'
            );
        }
        header('Content-Type: application/json');
        echo json_encode($response);
}

function getBarang(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

        $query = "select * from barang where keterangan = 'normal'";
        $query_result = mysqli_query($conn, $query);
        $check = mysqli_fetch_array($query_result);
        if (isset($check)) {
            $query_result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($query_result)) {
                $json_array[] = $row;
            }                
            $response = array(
                'code' => 200,
                'status' => 'data ditemukan',
                'barang_list' => $json_array
            );
        } else {
            $response = array(
                'code' => 404,
                'status' => 'Data tidak ditemukan',
                'barang_list' => $json_array
            );    
        }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function getInfoBarang(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $namaBarang = $_GET["nama_barang"];

        $query = "select * from barang where nama_barang = '$namaBarang'";
        $query_result = mysqli_query($conn, $query);
        $check = mysqli_fetch_array($query_result);
        if (isset($check)) {
            $query_result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($query_result)) {
                $json_array[] = $row;
            }                
            $response = array(
                'code' => 200,
                'status' => 'data ditemukan',
                'barang_list' => $json_array
            );
        } else {
            $response = array(
                'code' => 404,
                'status' => 'Data tidak ditemukan',
                'barang_list' => $json_array
            );    
        }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function filterJenisBarang(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $jenis = $_GET["jenis_kue"];

        $query = "select * from barang where jenis_kue = '$jenis' AND keterangan = 'normal'";
        $query_result = mysqli_query($conn, $query);
        $check = mysqli_fetch_array($query_result);
        if (isset($check)) {
            $query_result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($query_result)) {
                $json_array[] = $row;
            }                
            $response = array(
                'code' => 200,
                'status' => 'data ditemukan',
                'barang_list' => $json_array
            );
        } else {
            $response = array(
                'code' => 404,
                'status' => 'Data tidak ditemukan',
                'barang_list' => $json_array
            );    
        }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function filterBestSeller(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

        $query = "SELECT barang.id_barang ,barang.nama_barang, barang.image_barang, barang.harga_jual, ".
                "barang.deskripsi, SUM(detail_transaksi.qty) AS total_qty FROM detail_transaksi ".
                "JOIN barang ON barang.id_barang = detail_transaksi.id_barang WHERE barang.keterangan = 'normal' ".
                "GROUP BY barang.id_barang ORDER BY `total_qty` DESC LIMIT 15";
        $query_result = mysqli_query($conn, $query);
        $check = mysqli_fetch_array($query_result);
        if (isset($check)) {
            $query_result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($query_result)) {
                $json_array[] = $row;
            }                
            $response = array(
                'code' => 200,
                'status' => 'data ditemukan',
                'barang_list' => $json_array
            );
        } else {
            $response = array(
                'code' => 404,
                'status' => 'Data tidak ditemukan',
                'barang_list' => $json_array
            );    
        }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function getPaket(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

        $query = "select * from paket";
        $query_result = mysqli_query($conn, $query);
        $check = mysqli_fetch_array($query_result);
        if (isset($check)) {
            $query_result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($query_result)) {
                $json_array[] = $row;
            }                
            $response = array(
                'code' => 200,
                'status' => 'data ditemukan',
                'paket_list' => $json_array
            );
        } else {
            $response = array(
                'code' => 404,
                'status' => 'Data tidak ditemukan',
                'paket_list' => $json_array
            );    
        }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function getDetailPaket(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $id_paket = $_GET["id_paket"];

    $query = "SELECT * FROM paket WHERE id_paket = '$id_paket'";
    $query_result = mysqli_query($conn, $query);
    $check = mysqli_fetch_array($query_result);
    if (isset($check)) {
        $query_result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($query_result)) {
            $json_array[] = $row;
        }                
        $response = array(
            'code' => 200,
            'status' => 'data ditemukan',
            'paket_list' => $json_array
        );
    } else {
        $response = array(
            'code' => 404,
            'status' => 'Data tidak ditemukan',
            'paket_list' => $json_array
        );    
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function getBarangPaket(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

        $query = "select * from barang where keterangan = 'paket' order by nama_barang ASC";
        $query_result = mysqli_query($conn, $query);
        $check = mysqli_fetch_array($query_result);
        if (isset($check)) {
            $query_result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($query_result)) {
                $json_array[] = $row;
            }                
            $response = array(
                'code' => 200,
                'status' => 'data ditemukan',
                'barang_list' => $json_array
            );
        } else {
            $response = array(
                'code' => 404,
                'status' => 'Data tidak ditemukan',
                'barang_list' => $json_array
            );    
        }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function transaksi(){ 
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $json = file_get_contents('php://input', true);
    $obj = json_decode($json);

    //for Tr
    $imgBukti = $obj->image_bukti;
    $grandTotal = $obj->grand_total;
    $dibayarkan = $obj->dibayarkan;
    $kembalian = $obj->kembalian;
    $kurang_bayar = $obj->kurang_bayar;
    $status_bayar = $obj->status_bayar;
    $tlpPembeli = $obj->tlpPembeli;
    $tlpPemesan = $obj->tlpPemesan;
    //for Pengambilan
    $tanggal_ambil = $obj->tanggal_ambil;
    $jam = $obj->jam;

    $query = "SELECT id_user FROM user WHERE no_telepon = '$tlpPembeli'";
    $query_result = mysqli_query($conn, $query);
    $query5 = "SELECT akses FROM user WHERE no_telepon = '$tlpPemesan'";
    $query_result5 = mysqli_query($conn, $query5);
    if ($query_result && $query_result5) {
        while ($row = mysqli_fetch_assoc($query_result)) {
            $id_cust = $row['id_user'];
        }
        while ($row1 = mysqli_fetch_assoc($query_result5)) {
            $akses = $row1['akses'];     //ini buat bedain no nota kalo jadi
        }
    
    if($akses == 'karyawan'){
        $query2 = "SELECT no_nota FROM transaksi WHERE SUBSTRING(no_nota, 1, 2) = 'KY' ORDER BY SUBSTRING(no_nota, 3) DESC LIMIT 1";
        $result = mysqli_query($conn, $query2);
    
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $nota = $row['no_nota'];
        }
    }else if($akses == 'customer'){
        $query2 = "SELECT no_nota FROM transaksi WHERE SUBSTRING(no_nota, 1, 2) = 'CS' ORDER BY SUBSTRING(no_nota, 3) DESC LIMIT 1";
        $result = mysqli_query($conn, $query2);
    
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $nota = $row['no_nota'];
        }
    }
        if(!empty($nota)) {
            if($akses == "karyawan"){
                $prefix = "KY";
            }else if($akses == "customer"){
                $prefix = "CS";
            }
            $number = (int)substr($nota, 2);
            $number++;

            // Format ulang angka ke dalam format yang diinginkan (misal: NT0011)
            $no_nota = $prefix . sprintf('%04d', $number);
        }else{
            if($akses == "karyawan"){
                $prefix = "KY";
            }else if($akses == "customer"){
                $prefix = "CS";
            }
            $number = 1;
            $no_nota = $prefix . sprintf('%04d', $number);
        }

        if($imgBukti != ""){
            $id_img = $no_nota.".jpg";
            $imgBukti_file = "image/" . $no_nota . ".jpg";
            if(file_put_contents($imgBukti_file, base64_decode($imgBukti))){
                $responUpload = "berhasil upload";
            }else{
                $responUpload = "gagal upload";
            }
        }else{
            $id_img = "default_img.jpg";
        }

        if($id_img != null){
        $query3 = "INSERT INTO transaksi (no_nota, tgl_transaksi, grand_total, dibayarkan, kembalian, kurang_bayar, status_bayar, bukti_bayar, id_customer) VALUES 
                    ('$no_nota',NOW() ,'$grandTotal', '$dibayarkan', '$kembalian', '$kurang_bayar','$status_bayar' ,'$id_img' ,'$id_cust')";
        }else{
            $query3 = "INSERT INTO transaksi (no_nota, tgl_transaksi, grand_total, dibayarkan, kembalian, kurang_bayar, status_bayar, bukti_bayar, id_customer) VALUES 
                    ('$no_nota',NOW() ,'$grandTotal', '$dibayarkan', '$kembalian', '$kurang_bayar','$status_bayar' ,null ,'$id_cust')";
        }
        $query_result1 = mysqli_query($conn, $query3);

        $query4 = "INSERT INTO status_transaksi (no_nota, tanggal_pengambilan, jam, status) VALUES 
                    ('$no_nota','$tanggal_ambil' ,'$jam', 'pesanan masuk')";
        $query_result2 = mysqli_query($conn, $query4);

        if ($query_result1 && $query_result2) {
            $response = array(
                'code' => 200,
                'status' => 'transaksi berhasil',
                'nota' => $no_nota
            );
        } else {
            $response = array(
                'code' => 400,
                'status' => 'transaksi gagal'
            );
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function barangTransaksi(){ 
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $json = file_get_contents('php://input', true);
    $obj = json_decode($json);

    //for Tr
    $no_nota = $obj->no_nota;
    $id_barang = $obj->id_barang;
    $qty = $obj->qty;
    $total = $obj->total;
   
    $query = $conn->prepare("INSERT INTO detail_transaksi (no_nota, id_barang, qty, total) VALUES (?, ?, ?, ?)");
    $query->bind_param("ssss", $no_nota, $id_barang, $qty, $total);
    $query_result = $query->execute();
    
    if ($query_result) {
        $response = array(
            'code' => 200,
            'status' => 'transaksi berhasil'
        );
    } else {
        $response = array(
            'code' => 400,
            'status' => 'transaksi gagal'
        );
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function barangPaketTr(){ 
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $json = file_get_contents('php://input', true);
    $obj = json_decode($json);

    $identitasPkt = $obj->identitas_pkt;
    $nota = $obj->no_nota;
    $idPaket = $obj->id_paket;
    $qty = $obj->qty;
    $total = $obj->total;
   
    $query = $conn->prepare("INSERT INTO detail_paket_tr (identitas_pkt, no_nota, id_paket, qty, total) VALUES (?, ?, ?, ?, ?)");
    $query->bind_param("sssss", $identitasPkt, $nota, $idPaket, $qty, $total);
    $query_result = $query->execute();
    
    if ($query_result) {
        $response = array(
            'code' => 200,
            'status' => 'transaksi berhasil'
        );
    } else {
        $response = array(
            'code' => 400,
            'status' => 'transaksi gagal'
        );
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function barangDetailPaket(){ 
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $json = file_get_contents('php://input', true);
    $obj = json_decode($json);

    $identitasPkt = $obj->identitas_pkt;
    $idBarang = $obj->id_barang;
    $qty = $obj->qty;
   
    $query = $conn->prepare("INSERT INTO detail_paket (identitas_pkt, id_barang, qty) VALUES (?, ?, ?)");
    $query->bind_param("sss", $identitasPkt, $idBarang, $qty);
    $query_result = $query->execute();
    
    if ($query_result) {
        $response = array(
            'code' => 200,
            'status' => 'transaksi berhasil'
        );
    } else {
        $response = array(
            'code' => 400,
            'status' => 'transaksi gagal'
        );
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function riwayatPesanTerjadwal(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $tlp = $_GET['telepon'];

        $query = "select * from user where no_telepon = '$tlp'";
        $query_result = mysqli_query($conn, $query);
        if (mysqli_num_rows($query_result) > 0) {
            while ($row = mysqli_fetch_assoc($query_result)) {
                $id = $row['id_user'];
            }
            $query1 = "SELECT transaksi.no_nota, status_transaksi.tanggal_pengambilan, transaksi.grand_total, status_transaksi.status ".
            "FROM transaksi JOIN status_transaksi ON transaksi.no_nota = status_transaksi.no_nota ".
            "WHERE transaksi.id_customer = '$id' AND status_transaksi.tanggal_pengambilan > CURDATE() ORDER BY status_transaksi.tanggal_pengambilan ASC";
            $query_result1 = mysqli_query($conn, $query1);
            if (mysqli_num_rows($query_result1) > 0) {
                while ($row1 = mysqli_fetch_assoc($query_result1)) {
                    $json_array[] = $row1;
                }
                $response = array(
                    'code' => 200,
                    'status' => 'Data ditemukan',
                    'transaksi_list' => $json_array
                );
            } else {
                $response = array(
                    'code' => 400,
                    'status' => 'Data tidak ditemukan',
                    'transaksi_list' => $json_array
                );    
            }
        } else {
            $response = array(
                'code' => 404,
                'status' => 'Data tidak ditemukan, no telepon salah',
                'transaksi_list' => $json_array
            );    
        }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function riwayatPesanProses(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $tlp = $_GET['telepon'];

        $query = "select * from user where no_telepon = '$tlp'";
        $query_result = mysqli_query($conn, $query);
        if (mysqli_num_rows($query_result) > 0) {
            while ($row = mysqli_fetch_assoc($query_result)) {
                $id = $row['id_user'];
            }
            $query1 = "SELECT transaksi.no_nota, status_transaksi.tanggal_pengambilan, transaksi.grand_total, status_transaksi.status ".
            "FROM transaksi JOIN status_transaksi ON transaksi.no_nota = status_transaksi.no_nota ".
            "WHERE transaksi.id_customer = '$id' AND status_transaksi.tanggal_pengambilan = CURDATE() ORDER BY status_transaksi.tanggal_pengambilan ASC";
            $query_result1 = mysqli_query($conn, $query1);
            if (mysqli_num_rows($query_result1) > 0) {
                while ($row1 = mysqli_fetch_assoc($query_result1)) {
                    $json_array[] = $row1;
                }
                $response = array(
                    'code' => 200,
                    'status' => 'Data ditemukan',
                    'transaksi_list' => $json_array
                );
            } else {
                $response = array(
                    'code' => 400,
                    'status' => 'Data tidak ditemukan',
                    'transaksi_list' => $json_array
                );    
            }
        } else {
            $response = array(
                'code' => 404,
                'status' => 'Data tidak ditemukan, no telepon salah',
                'transaksi_list' => $json_array
            );    
        }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function riwayatPesanRiwayat(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $tlp = $_GET['telepon'];

        $query = "select * from user where no_telepon = '$tlp'";
        $query_result = mysqli_query($conn, $query);
        if (mysqli_num_rows($query_result) > 0) {
            while ($row = mysqli_fetch_assoc($query_result)) {
                $id = $row['id_user'];
            }
            $query1 = "SELECT transaksi.no_nota, status_transaksi.tanggal_pengambilan, transaksi.grand_total, status_transaksi.status ".
            "FROM transaksi JOIN status_transaksi ON transaksi.no_nota = status_transaksi.no_nota ".
            "WHERE transaksi.id_customer = '$id' AND (status_transaksi.status = 'pesanan selesai' OR status_transaksi.status = 'pesanan dibatalkan') ORDER BY status_transaksi.tanggal_pengambilan DESC LIMIT 10";
            $query_result1 = mysqli_query($conn, $query1);
            if (mysqli_num_rows($query_result1) > 0) {
                while ($row1 = mysqli_fetch_assoc($query_result1)) {
                    $json_array[] = $row1;
                }
                $response = array(
                    'code' => 200,
                    'status' => 'Data ditemukan',
                    'transaksi_list' => $json_array
                );
            } else {
                $response = array(
                    'code' => 400,
                    'status' => 'Data tidak ditemukan',
                    'transaksi_list' => $json_array
                );    
            }
        } else {
            $response = array(
                'code' => 404,
                'status' => 'Data tidak ditemukan, no telepon salah',
                'transaksi_list' => $json_array
            );    
        }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function addCustomer(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $json = file_get_contents('php://input', true);
    $obj = json_decode($json);

    $nama = $obj->nama;
    $alamat = $obj->alamat;
    $tlp = $obj->telepon;

    if (!empty($nama) && !empty($alamat) && !empty($tlp)) {
        
        $query = "SELECT * FROM user WHERE no_telepon = '$tlp'";
        $query_result = mysqli_query($conn, $query);
        $check = mysqli_fetch_array($query_result);

        if (isset($check)) {
            $response = array(
                'code' => 101,
                'status' => 'No Telepon sudah terdaftar'
            );
        } else {
            // Email belum terdaftar, lanjutkan dengan penyisipan data
            $query = "INSERT INTO user (nama, alamat, no_telepon, akses) VALUES ('$nama', '$alamat', '$tlp', 'customer')";

            if ($result = mysqli_query($conn, $query)) {
                $response = array(
                    'code' => 200,
                    'status' => 'Registrasi berhasil'
                );
            } else {
                $response = array(
                    'code' => 205,
                    'status' => 'Registrasi gagal'
                );
            }
        }
    } else {
        $response = array(
            'code' => 100,
            'status' => 'Lengkapi Data Yang Dibutuhkan'
        );
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function riwayatPesanTerjadwalAdmin(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);
    
    $query1 = "SELECT transaksi.no_nota, user.nama, transaksi.grand_total FROM transaksi ".
        "JOIN status_transaksi ON transaksi.no_nota = status_transaksi.no_nota JOIN user ".
        "ON transaksi.id_customer = user.id_user WHERE status_transaksi.tanggal_pengambilan > CURDATE() ".
        "AND (status_transaksi.status = 'pesanan masuk' OR status_transaksi.status = 'pesanan diproses') ORDER BY status_transaksi.tanggal_pengambilan ASC";
    $query_result1 = mysqli_query($conn, $query1);
    if (mysqli_num_rows($query_result1) > 0) {
        while ($row1 = mysqli_fetch_assoc($query_result1)) {
            $json_array[] = $row1;
        }
        $response = array(
            'code' => 200,
            'status' => 'Data ditemukan',
            'transaksi_list' => $json_array
        );
    } else {
        $response = array(
        'code' => 400,
        'status' => 'Data tidak ditemukan',
        'transaksi_list' => $json_array
        );    
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function riwayatPesanProsesAdmin(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $query1 = "SELECT transaksi.no_nota, user.nama, transaksi.grand_total FROM transaksi ".
        "JOIN status_transaksi ON transaksi.no_nota = status_transaksi.no_nota JOIN user ".
        "ON transaksi.id_customer = user.id_user WHERE status_transaksi.tanggal_pengambilan = CURDATE() ".
        "AND (status_transaksi.status = 'pesanan masuk' OR status_transaksi.status = 'pesanan diproses') ORDER BY status_transaksi.tanggal_pengambilan ASC";
    $query_result1 = mysqli_query($conn, $query1);
    if (mysqli_num_rows($query_result1) > 0) {
        while ($row1 = mysqli_fetch_assoc($query_result1)) {
            $json_array[] = $row1;
        }
        $response = array(
            'code' => 200,
            'status' => 'Data ditemukan',
            'transaksi_list' => $json_array
        );
    } else {
        $response = array(
        'code' => 400,
        'status' => 'Data tidak ditemukan',
        'transaksi_list' => $json_array
        );    
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function riwayatPesanRiwayatAdmin(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $query1 = "SELECT transaksi.no_nota, user.nama, transaksi.grand_total FROM transaksi ".
        "JOIN status_transaksi ON transaksi.no_nota = status_transaksi.no_nota JOIN user ".
        "ON transaksi.id_customer = user.id_user WHERE (status_transaksi.status = 'pesanan selesai' ".
        "OR status_transaksi.status = 'pesanan dibatalkan') AND status_transaksi.tanggal_pengambilan >= DATE_SUB(NOW(), INTERVAL 2 DAY) ORDER BY status_transaksi.tanggal_pengambilan DESC;";
    $query_result1 = mysqli_query($conn, $query1);
    if (mysqli_num_rows($query_result1) > 0) {
        while ($row1 = mysqli_fetch_assoc($query_result1)) {
            $json_array[] = $row1;
        }
        $response = array(
            'code' => 200,
            'status' => 'Data ditemukan',
            'transaksi_list' => $json_array
        );
    } else {
        $response = array(
        'code' => 400,
        'status' => 'Data tidak ditemukan',
        'transaksi_list' => $json_array
        );    
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function getNotaTransaksi(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $nota = $_GET['no_nota'];

    $query1 = "SELECT transaksi.no_nota, user.nama, status_transaksi.tanggal_pengambilan, status_transaksi.jam, ".
                "transaksi.grand_total, transaksi.status_bayar, transaksi.kurang_bayar, transaksi.dibayarkan, CASE WHEN TIMESTAMPDIFF".
                "(DAY, CURDATE(), status_transaksi.tanggal_pengambilan) >= 2 THEN true ELSE false END AS status_batal ".
                "FROM transaksi JOIN user ON transaksi.id_customer = user.id_user JOIN status_transaksi ON ".
                "transaksi.no_nota = status_transaksi.no_nota WHERE transaksi.no_nota = '$nota';";
    $query_result1 = mysqli_query($conn, $query1);
    if (mysqli_num_rows($query_result1) > 0) {
        while ($row1 = mysqli_fetch_assoc($query_result1)) {
            $json_array[] = $row1;
        }
        $response = array(
            'code' => 200,
            'status' => 'Data ditemukan',
            'nota_list' => $json_array
        );
    } else {
        $response = array(
        'code' => 400,
        'status' => 'Data tidak ditemukan',
        'nota_list' => $json_array
        );    
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function getNotaBarang(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $nota = $_GET['no_nota'];

    $query1 = "SELECT barang.nama_barang, detail_transaksi.qty, barang.harga_jual, detail_transaksi.total, barang.id_barang, barang.image_barang, barang.deskripsi ".
                "FROM detail_transaksi JOIN barang ON barang.id_barang = detail_transaksi.id_barang JOIN transaksi ".
                "ON transaksi.no_nota = detail_transaksi.no_nota WHERE transaksi.no_nota = '$nota'";
    $query_result1 = mysqli_query($conn, $query1);
    if (mysqli_num_rows($query_result1) > 0) {
        while ($row1 = mysqli_fetch_assoc($query_result1)) {
            $json_array[] = $row1;
        }
        $response = array(
            'code' => 200,
            'status' => 'Data ditemukan',
            'nota_list' => $json_array
        );
    } else {
        $response = array(
        'code' => 400,
        'status' => 'Data tidak ditemukan',
        'nota_list' => null
        );    
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function getNotaPaket(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $nota = $_GET['no_nota'];

    $query1 = "SELECT paket.nama_paket, detail_paket_tr.qty, paket.harga_jual, detail_paket_tr.total ".
                "FROM detail_paket_tr JOIN paket ON paket.id_paket = detail_paket_tr.id_paket ".
                "JOIN transaksi ON transaksi.no_nota = detail_paket_tr.no_nota WHERE transaksi.no_nota = '$nota'";
    $query_result1 = mysqli_query($conn, $query1);
    if (mysqli_num_rows($query_result1) > 0) {
        while ($row1 = mysqli_fetch_assoc($query_result1)) {
            $json_array[] = $row1;
        }
        $response = array(
            'code' => 200,
            'status' => 'Data ditemukan',
            'nota_list' => $json_array
        );
    } else {
        $response = array(
        'code' => 400,
        'status' => 'Data tidak ditemukan',
        'nota_list' => null
        );    
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function reqPembatalan(){ 
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $json = file_get_contents('php://input', true);
    $obj = json_decode($json);

    $no_nota = $obj->no_nota;
    $alasanBatal = $obj->alasan_batal;
   
    $query = $conn->prepare("UPDATE status_transaksi SET alasan_batal = (?), status = 'permintaan batal' WHERE no_nota = ?");
    $query->bind_param("ss", $alasanBatal, $no_nota);
    $query_result = $query->execute();
    
    if ($query_result) {
        $response = array(
            'code' => 200,
            'status' => 'req pembatalan berhasil'
        );
    } else {
        $response = array(
            'code' => 400,
            'status' => 'req pembatalan gagal'
        );
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function pengambilanDP(){ 
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $json = file_get_contents('php://input', true);
    $obj = json_decode($json);

    $no_nota = $obj->no_nota;
    $dibayarkan = $obj->dibayarkan;
    $kembalian = $obj->kembalian;
    $buktiBayar = $obj->buktiBayar;
   
    if ($buktiBayar != "") {
        $id_img = $no_nota . ".jpg";
        $imgBukti_file = "image/" . $no_nota . ".jpg";
    
        // Cek apakah file dengan nama yang sama sudah ada
        if (file_exists($imgBukti_file)) {
            // Jika ada, hapus file yang sudah ada
            if (unlink($imgBukti_file)) {
                echo "File lama dihapus.";
            }else {
                echo "Gagal menghapus file lama.";
            }
        }
    
        // Proses upload file baru
        if (file_put_contents($imgBukti_file, base64_decode($buktiBayar))) {
            $responUpload = "Berhasil upload.";
        } else {
            $responUpload = "Gagal upload.";
        }
    } else {
        $id_img = "default_img.jpg";
    }

    $query = $conn->prepare("UPDATE transaksi JOIN status_transaksi ON transaksi.no_nota = status_transaksi.no_nota "."
                            SET transaksi.dibayarkan = ?, transaksi.kembalian = ?, transaksi.kurang_bayar = '0', ".
                            "transaksi.status_bayar = 'Lunas', transaksi.bukti_bayar = ?, status_transaksi.status = 'pesanan selesai' ".
                            "WHERE transaksi.no_nota = ?");
    $query->bind_param("ssss", $dibayarkan, $kembalian, $id_img, $no_nota);
    $query_result = $query->execute();
    
    if ($query_result) {
        $response = array(
            'code' => 200,
            'status' => 'pelunasan berhasil'
        );
    } else {
        $response = array(
            'code' => 400,
            'status' => 'pelunasan gagal'
        );
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function pengambilanLunas(){ 
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $nota = $_GET['no_nota'];
   
    $query = $conn->prepare("UPDATE status_transaksi SET status = 'pesanan selesai' WHERE no_nota = ?");
    $query->bind_param("s",$nota);
    $query_result = $query->execute();
    
    if ($query_result) {
        $response = array(
            'code' => 200,
            'status' => 'pengambilan berhasil'
        );
    } else {
        $response = array(
            'code' => 400,
            'status' => 'pengambilan gagal'
        );
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

function getIdentitasPaket(){
    include 'koneksi.php';
    $conn = mysqli_connect($HostName, $HostUser, $HostPass, $DatabaseName);

    $query = "SELECT identitas_pkt FROM detail_paket_tr ORDER BY SUBSTRING(identitas_pkt, 3) DESC LIMIT 1";
    $query_result = mysqli_query($conn, $query);

    if ($query_result) {
        $row = mysqli_fetch_assoc($query_result);

        if ($row) {
            $response = array(
                'code' => 200,
                'status' => 'Data ditemukan',
                'identitas' => $row['identitas_pkt']
            );
        } else {
            $response = array(
                'code' => 404,
                'status' => 'Data tidak ditemukan',
                'identitas' => null
            );
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}
?>