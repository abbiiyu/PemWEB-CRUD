<?php
header('Content-Type: application/json');

// Konfigurasi database
$host = "localhost";
$user = "root";
$pass = "";
$db = "barang_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["error" => $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'create') {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];

        // Upload gambar
        $targetDir = "uploads/";
        $imageName = basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $imageName;
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);

        $stmt = $conn->prepare("INSERT INTO barang (name, price, image, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $name, $price, $imageName, $description);
        $stmt->execute();

        echo json_encode(["message" => "Barang berhasil ditambahkan"]);
    } elseif ($action === 'delete') {
        $id = $_POST['id'];

        // Hapus data dan file gambar
        $stmt = $conn->prepare("SELECT image FROM barang WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        unlink("uploads/" . $row['image']);

        $stmt = $conn->prepare("DELETE FROM barang WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(["message" => "Barang berhasil dihapus"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT * FROM barang");
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $row['image'] = 'uploads/' . $row['image']; // Tambahkan path ke file gambar
        $data[] = $row;
    }

    echo json_encode($data);
}

$conn->close();
