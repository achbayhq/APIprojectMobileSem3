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

        $query = "select * from barang";
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
    $tlp = $obj->tlp;
    //for Pengambilan
    $tanggal_ambil = $obj->tanggal_ambil;
    $jam = $obj->jam;

    $query = "SELECT id_user, akses FROM user WHERE no_telepon = '$tlp'";
    $query_result = mysqli_query($conn, $query);
    if ($query_result) {
        while ($row = mysqli_fetch_assoc($query_result)) {
            $id_cust = $row['id_user'];
            $akses = $row['akses'];     //ini buat bedain no nota kalo jadi
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

        $id_img = $no_nota.".jpg";
        $imgBukti_file = "image/" . $no_nota . ".jpg";
        if(file_put_contents($imgBukti_file, base64_decode($imgBukti))){
            $responUpload = "berhasil upload";
        }else{
            $responUpload = "gagal upload";
        }

        $query3 = "INSERT INTO transaksi (no_nota, tgl_transaksi, grand_total, dibayarkan, kembalian, kurang_bayar, status_bayar, bukti_bayar, id_customer) VALUES 
                    ('$no_nota',NOW() ,'$grandTotal', '$dibayarkan', '$kembalian', '$kurang_bayar','$status_bayar' ,'$id_img' ,'$id_cust')";
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
            "WHERE transaksi.id_customer = '$id' AND status_transaksi.tanggal_pengambilan > CURDATE()";
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
            "WHERE transaksi.id_customer = '$id' AND status_transaksi.tanggal_pengambilan = CURDATE()";
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
            "WHERE transaksi.id_customer = '$id' AND status_transaksi.status = 'pesanan selesai' OR status_transaksi.status = 'pesanan dibatalkan'";
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
?>