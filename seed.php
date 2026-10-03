<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/app/Database.php';

$db = Database::getInstance();
$pdo = $db->getPdo();

$ownerId = 1;

// Property 1: Kos Putri Melati
$prop1Photo = downloadPhoto('https://picsum.photos/seed/kosmelati/800/600', 'prop-melati.jpg', 'properties');
$prop1 = insertProperty($pdo, $ownerId, [
    'name' => 'Kos Putri Melati',
    'address' => 'Jl. Diponegoro No. 45, RT 03/RW 12',
    'city' => 'Yogyakarta',
    'province' => 'DI Yogyakarta',
    'postal_code' => '55281',
    'description' => 'Kos putri eksklusif dekat kampus UGM dan UII. Lingkungan aman, tenang, dan nyaman. Fasilitas lengkap dengan internet cepat.',
    'photo' => $prop1Photo,
    'facilities' => json_encode(['WiFi', 'Parkir', 'CCTV', 'Rooftop', 'Dapur Bersama']),
    'latitude' => '-7.78290000',
    'longitude' => '110.36720000',
    'is_active' => 1,
]);

$prop2Photo = downloadPhoto('https://picsum.photos/seed/kosgiris/800/600', 'prop-giris.jpg', 'properties');
$prop2 = insertProperty($pdo, $ownerId, [
    'name' => 'Kos Campur Griya Asri',
    'address' => 'Jl. Malioboro No. 120, Gondokusuman',
    'city' => 'Yogyakarta',
    'province' => 'DI Yogyakarta',
    'postal_code' => '55223',
    'description' => 'Kos campur strategis di pusat kota. Dekat Mall Malioboro, ATM, dan transportasi umum. Cocok untuk mahasiswa dan pekerja.',
    'photo' => $prop2Photo,
    'facilities' => json_encode(['WiFi', 'Parkir Motor', 'Laundry', 'Warung', 'Security 24 Jam']),
    'latitude' => '-7.79250000',
    'longitude' => '110.36500000',
    'is_active' => 1,
]);

echo "Properties created: {$prop1}, {$prop2}\n";

// Rooms for Property 1 (Kos Putri Melati)
$prop1Rooms = [
    ['room_number' => 'A-01', 'type' => 'single', 'price' => 850000, 'capacity' => 1, 'floor' => 1, 'size' => 12.00, 'facilities' => ['AC', 'Kasur Busa', 'Lemari', 'Meja Belajar', 'Kipas Angin']],
    ['room_number' => 'A-02', 'type' => 'single', 'price' => 850000, 'capacity' => 1, 'floor' => 1, 'size' => 12.00, 'facilities' => ['AC', 'Kasur Busa', 'Lemari', 'Meja Belajar', 'Kipas Angin']],
    ['room_number' => 'A-03', 'type' => 'double', 'price' => 1200000, 'capacity' => 2, 'floor' => 1, 'size' => 16.00, 'facilities' => ['AC', 'Kasur Double', 'Lemari', 'Meja Belajar', 'Sofa']],
    ['room_number' => 'A-04', 'type' => 'single', 'price' => 900000, 'capacity' => 1, 'floor' => 1, 'size' => 14.00, 'facilities' => ['AC', 'Kasur Busa', 'Lemari', 'Meja Belajar', 'Kulkas Mini']],
    ['room_number' => 'B-01', 'type' => 'single', 'price' => 900000, 'capacity' => 1, 'floor' => 2, 'size' => 14.00, 'facilities' => ['AC', 'Kasur Busa', 'Lemari', 'Meja Belajar', 'Kulkas Mini']],
    ['room_number' => 'B-02', 'type' => 'double', 'price' => 1300000, 'capacity' => 2, 'floor' => 2, 'size' => 18.00, 'facilities' => ['AC', 'Kasur Double', 'Lemari Besar', 'Meja Belajar', 'Sofa', 'Kulkas Mini']],
    ['room_number' => 'B-03', 'type' => 'single', 'price' => 900000, 'capacity' => 1, 'floor' => 2, 'size' => 14.00, 'facilities' => ['AC', 'Kasur Busa', 'Lemari', 'Meja Belajar', 'Kulkas Mini']],
    ['room_number' => 'B-04', 'type' => 'single', 'price' => 950000, 'capacity' => 1, 'floor' => 2, 'size' => 15.00, 'facilities' => ['AC', 'Kasur Busa', 'Lemari', 'Meja Belajar', 'Kulkas Mini', 'Jendela Besar']],
    ['room_number' => 'C-01', 'type' => 'suite', 'price' => 1800000, 'capacity' => 2, 'floor' => 3, 'size' => 24.00, 'facilities' => ['AC', 'Kasur Double', 'Lemari Besar', 'Meja Kerja', 'Sofa', 'Kulkas', 'Balkon']],
    ['room_number' => 'C-02', 'type' => 'suite', 'price' => 2000000, 'capacity' => 2, 'floor' => 3, 'size' => 28.00, 'facilities' => ['AC', 'Kasur Double', 'Lemari Besar', 'Meja Kerja', 'Sofa', 'Kulkas', 'Balkon', 'Smart TV']],
];

$statuses1 = ['occupied','occupied','empty','occupied','empty','occupied','empty','empty','empty','empty'];

foreach ($prop1Rooms as $i => $room) {
    $photo = downloadPhoto("https://picsum.photos/seed/room1{$i}/600/400", "room-melati-{$room['room_number']}.jpg", 'rooms');
    insertRoom($pdo, $prop1, [
        'room_number' => $room['room_number'],
        'type' => $room['type'],
        'price' => $room['price'],
        'capacity' => $room['capacity'],
        'floor' => $room['floor'],
        'size_sqm' => $room['size'],
        'facilities' => json_encode($room['facilities']),
        'status' => $statuses1[$i],
        'photo' => $photo,
        'description' => "Kamar {$room['type']} di lantai {$room['floor']}, ukuran {$room['size']}m²",
    ]);
}

// Rooms for Property 2 (Kos Campur Griya Asri)
$prop2Rooms = [
    ['room_number' => '101', 'type' => 'single', 'price' => 750000, 'capacity' => 1, 'floor' => 1, 'size' => 10.00, 'facilities' => ['Kipas Angin', 'Kasur Busa', 'Lemari', 'Meja Belajar']],
    ['room_number' => '102', 'type' => 'single', 'price' => 750000, 'capacity' => 1, 'floor' => 1, 'size' => 10.00, 'facilities' => ['Kipas Angin', 'Kasur Busa', 'Lemari', 'Meja Belajar']],
    ['room_number' => '103', 'type' => 'double', 'price' => 1000000, 'capacity' => 2, 'floor' => 1, 'size' => 14.00, 'facilities' => ['AC', 'Kasur Double', 'Lemari', 'Meja Belajar']],
    ['room_number' => '104', 'type' => 'single', 'price' => 800000, 'capacity' => 1, 'floor' => 1, 'size' => 11.00, 'facilities' => ['AC', 'Kasur Busa', 'Lemari', 'Meja Belajar']],
    ['room_number' => '201', 'type' => 'single', 'price' => 800000, 'capacity' => 1, 'floor' => 2, 'size' => 11.00, 'facilities' => ['AC', 'Kasur Busa', 'Lemari', 'Meja Belajar']],
    ['room_number' => '202', 'type' => 'double', 'price' => 1100000, 'capacity' => 2, 'floor' => 2, 'size' => 15.00, 'facilities' => ['AC', 'Kasur Double', 'Lemari Besar', 'Meja Belajar', 'TV']],
    ['room_number' => '203', 'type' => 'single', 'price' => 850000, 'capacity' => 1, 'floor' => 2, 'size' => 12.00, 'facilities' => ['AC', 'Kasur Busa', 'Lemari', 'Meja Belajar', 'Kulkas Mini']],
    ['room_number' => '204', 'type' => 'single', 'price' => 850000, 'capacity' => 1, 'floor' => 2, 'size' => 12.00, 'facilities' => ['AC', 'Kasur Busa', 'Lemari', 'Meja Belajar', 'Kulkas Mini']],
    ['room_number' => '301', 'type' => 'suite', 'price' => 1500000, 'capacity' => 2, 'floor' => 3, 'size' => 20.00, 'facilities' => ['AC', 'Kasur Double', 'Lemari Besar', 'Meja Kerja', 'Sofa', 'TV']],
    ['room_number' => '302', 'type' => 'suite', 'price' => 1700000, 'capacity' => 2, 'floor' => 3, 'size' => 22.00, 'facilities' => ['AC', 'Kasur Double', 'Lemari Besar', 'Meja Kerja', 'Sofa', 'TV', 'Balkon']],
];

$statuses2 = ['occupied','empty','occupied','empty','occupied','occupied','empty','empty','empty','empty'];

foreach ($prop2Rooms as $i => $room) {
    $photo = downloadPhoto("https://picsum.photos/seed/room2{$i}/600/400", "room-giris-{$room['room_number']}.jpg", 'rooms');
    insertRoom($pdo, $prop2, [
        'room_number' => $room['room_number'],
        'type' => $room['type'],
        'price' => $room['price'],
        'capacity' => $room['capacity'],
        'floor' => $room['floor'],
        'size_sqm' => $room['size'],
        'facilities' => json_encode($room['facilities']),
        'status' => $statuses2[$i],
        'photo' => $photo,
        'description' => "Kamar {$room['type']} di lantai {$room['floor']}, ukuran {$room['size']}m²",
    ]);
}

echo "All 20 rooms created!\n";

function downloadPhoto($url, $filename, $subdir = 'rooms') {
    $dir = __DIR__ . '/public/uploads/' . $subdir . '/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $path = $dir . $filename;
    if (file_exists($path)) return $filename;
    $ctx = stream_context_create(['http' => ['timeout' => 15, 'ignore_errors' => true]]);
    $data = @file_get_contents($url, false, $ctx);
    if ($data !== false && strlen($data) > 1000) {
        file_put_contents($path, $data);
        echo "  Downloaded: {$filename}\n";
        return $filename;
    }
    echo "  Failed: {$filename}, using placeholder\n";
    return createPlaceholder($filename, $subdir);
}

function createPlaceholder($filename, $subdir = 'rooms') {
    $dir = __DIR__ . '/public/uploads/' . $subdir . '/';
    $path = $dir . $filename;
    if (file_exists($path)) return $filename;
    $img = imagecreatetruecolor(800, 600);
    $bg = imagecolorallocate($img, 200, 200, 200);
    $fg = imagecolorallocate($img, 100, 100, 100);
    imagefill($img, 0, 0, $bg);
    imagestring($img, 5, 300, 280, 'No Image', $fg);
    imagejpeg($img, $path, 85);
    imagedestroy($img);
    return $filename;
}

function insertProperty($pdo, $ownerId, $data) {
    $data['owner_id'] = $ownerId;
    $cols = implode(', ', array_keys($data));
    $vals = ':' . implode(', :', array_keys($data));
    $sql = "INSERT INTO properties ({$cols}) VALUES ({$vals})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    return $pdo->lastInsertId();
}

function insertRoom($pdo, $propertyId, $data) {
    $data['property_id'] = $propertyId;
    $cols = implode(', ', array_keys($data));
    $vals = ':' . implode(', :', array_keys($data));
    $sql = "INSERT INTO rooms ({$cols}) VALUES ({$vals})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    return $pdo->lastInsertId();
}
