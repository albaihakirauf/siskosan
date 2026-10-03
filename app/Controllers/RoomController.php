<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Property.php';
require_once __DIR__ . '/../Models/Room.php';

class RoomController extends Controller {
    public function index() {
        $this->authorize('manage_rooms');
        $user = auth();
        $propertyId = $_GET['property_id'] ?? null;
        
        $query = Room::query();
        
        // Filter by property
        if ($propertyId) {
            $property = Property::find($propertyId);
            if (!$property || ($user->role === 'owner' && $property->owner_id !== $user->id)) {
                $this->abort(403);
            }
            $query->where('property_id', $propertyId);
        } elseif ($user->role === 'owner') {
            $propertyIds = Property::where('owner_id', $user->id)->get();
            $ids = array_column($propertyIds, 'id');
            if ($ids) {
                $query->whereIn('property_id', $ids);
            } else {
                $query->where('property_id', -1); // No results
            }
        }
        
        $search = $_GET['search'] ?? '';
        if ($search) {
            $query->where('room_number', 'LIKE', "%{$search}%");
        }
        
        $status = $_GET['status'] ?? '';
        if ($status) {
            $query->where('status', $status);
        }
        
        $rooms = $query->orderBy('property_id')->orderBy('room_number')->paginate(20);
        
        // Add property name
        $db = db();
        foreach ($rooms['data'] as &$room) {
            $prop = $db->table('properties')->find($room['property_id']);
            $room['property_name'] = $prop['name'] ?? '-';
        }
        
        // Get properties for filter
        $properties = Property::getUserProperties($user->id);
        
        $this->view('rooms.index', [
            'rooms' => $rooms,
            'properties' => $properties,
            'propertyId' => $propertyId,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create() {
        $this->authorize('manage_rooms');
        $user = auth();
        $propertyId = $_GET['property_id'] ?? null;
        
        $properties = Property::getUserProperties($user->id);
        
        if ($propertyId) {
            $property = Property::find($propertyId);
            if (!$property || ($user->role === 'owner' && $property->owner_id !== $user->id)) {
                $this->abort(403);
            }
        }
        
        $this->view('rooms.create', [
            'properties' => $properties,
            'selectedProperty' => $propertyId,
        ]);
    }

    public function store() {
        $this->authorize('manage_rooms');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $rules = [
            'property_id' => 'required|exists:properties,id',
            'room_number' => 'required|max:20',
            'type' => 'required|in:single,double,suite',
            'price' => 'required|numeric|min:0',
            'capacity' => 'integer|min:1|max:10',
            'floor' => 'integer|min:0|max:100',
            'size_sqm' => 'numeric|min:0',
            'description' => 'max:1000',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        $user = auth();
        $property = Property::find($_POST['property_id']);
        if (!$property || ($user->role === 'owner' && $property->owner_id !== $user->id)) {
            $this->abort(403);
        }

        // Check unique room number per property
        $db = db();
        $exists = $db->table('rooms')
            ->where('property_id', $_POST['property_id'])
            ->where('room_number', $_POST['room_number'])
            ->exists();
        
        if ($exists) {
            $this->flash('error', 'Nomor kamar sudah digunakan di properti ini');
            return $this->back();
        }

        $data = [
            'property_id' => $_POST['property_id'],
            'room_number' => trim($_POST['room_number']),
            'type' => $_POST['type'],
            'price' => $_POST['price'],
            'capacity' => $_POST['capacity'] ?? 1,
            'floor' => $_POST['floor'] ?? null,
            'size_sqm' => $_POST['size_sqm'] ?? null,
            'description' => $_POST['description'] ?? null,
            'status' => 'empty',
        ];

        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $result = upload_file($_FILES['photo'], 'uploads/rooms');
            if ($result['success']) {
                $data['photo'] = $result['filename'];
            } else {
                $this->flash('error', $result['message']);
                return $this->back();
            }
        }

        // Handle facilities
        $facilities = [];
        if (!empty($_POST['facilities'])) {
            $facilities = array_filter(array_map('trim', explode(',', $_POST['facilities'])));
        }
        $data['facilities'] = json_encode($facilities);

        $id = $db->table('rooms')->insert($data);
        
        log_activity('room_create', "Room created: {$data['room_number']}", 'room', $id);
        $this->flash('success', 'Kamar berhasil ditambahkan');
        return $this->redirect(url('rooms?property_id=' . $data['property_id']));
    }

    public function show($id) {
        $this->authorize('manage_rooms');
        $room = Room::find($id);
        
        if (!$room) {
            $this->abort(404, 'Kamar tidak ditemukan');
        }
        
        $user = auth();
        $property = $room->property();
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->abort(403);
        }

        $tenant = $room->tenant();
        $currentInvoice = $room->getCurrentInvoice();
        
        $this->view('rooms.show', [
            'room' => $room,
            'property' => $property,
            'tenant' => $tenant,
            'currentInvoice' => $currentInvoice,
        ]);
    }

    public function edit($id) {
        $this->authorize('manage_rooms');
        $room = Room::find($id);
        
        if (!$room) {
            $this->abort(404, 'Kamar tidak ditemukan');
        }
        
        $user = auth();
        $property = $room->property();
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->abort(403);
        }

        $properties = Property::getUserProperties($user->id);
        
        $this->view('rooms.edit', [
            'room' => $room,
            'properties' => $properties,
        ]);
    }

    public function update($id) {
        $this->authorize('manage_rooms');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $room = Room::find($id);
        if (!$room) {
            $this->abort(404, 'Kamar tidak ditemukan');
        }
        
        $user = auth();
        $property = $room->property();
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->abort(403);
        }

        $rules = [
            'room_number' => 'required|max:20',
            'type' => 'required|in:single,double,suite',
            'price' => 'required|numeric|min:0',
            'capacity' => 'integer|min:1|max:10',
            'floor' => 'integer|min:0|max:100',
            'size_sqm' => 'numeric|min:0',
            'description' => 'max:1000',
            'status' => 'in:empty,occupied,maintenance',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        // Check unique room number per property (excluding current)
        $db = db();
        $exists = $db->table('rooms')
            ->where('property_id', $room->property_id)
            ->where('room_number', $_POST['room_number'])
            ->where('id', '!=', $id)
            ->exists();
        
        if ($exists) {
            $this->flash('error', 'Nomor kamar sudah digunakan di properti ini');
            return $this->back();
        }

        $data = [
            'room_number' => trim($_POST['room_number']),
            'type' => $_POST['type'],
            'price' => $_POST['price'],
            'capacity' => $_POST['capacity'] ?? 1,
            'floor' => $_POST['floor'] ?? null,
            'size_sqm' => $_POST['size_sqm'] ?? null,
            'description' => $_POST['description'] ?? null,
            'status' => $_POST['status'] ?? $room->status,
        ];

        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            if ($room->photo) {
                delete_file('uploads/rooms/' . $room->photo);
            }
            
            $result = upload_file($_FILES['photo'], 'uploads/rooms');
            if ($result['success']) {
                $data['photo'] = $result['filename'];
            } else {
                $this->flash('error', $result['message']);
                return $this->back();
            }
        }

        // Handle facilities
        $facilities = [];
        if (!empty($_POST['facilities'])) {
            $facilities = array_filter(array_map('trim', explode(',', $_POST['facilities'])));
        }
        $data['facilities'] = json_encode($facilities);

        $db->table('rooms')->where('id', $id)->update($data);
        
        log_activity('room_update', "Room updated: {$data['room_number']}", 'room', $id);
        $this->flash('success', 'Kamar berhasil diperbarui');
        return $this->redirect(url('rooms/' . $id));
    }

    public function destroy($id) {
        $this->authorize('manage_rooms');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $room = Room::find($id);
        if (!$room) {
            $this->flash('error', 'Kamar tidak ditemukan');
            return $this->back();
        }
        
        $user = auth();
        $property = $room->property();
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->flash('error', 'Tidak memiliki izin');
            return $this->back();
        }

        // Check if room has active tenant
        $db = db();
        $tenant = $db->table('tenants')->where('room_id', $id)->where('status', 'active')->first();
        if ($tenant) {
            $this->flash('error', 'Kamar masih memiliki penyewa aktif');
            return $this->back();
        }

        if ($room->photo) {
            delete_file('uploads/rooms/' . $room->photo);
        }
        
        $room->delete();
        
        log_activity('room_delete', "Room deleted: {$room->room_number}", 'room', $id);
        $this->flash('success', 'Kamar berhasil dihapus');
        return $this->back();
    }

    public function updateStatus($id) {
        $this->authorize('manage_rooms');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $room = Room::find($id);
        if (!$room) {
            $this->flash('error', 'Kamar tidak ditemukan');
            return $this->back();
        }
        
        $user = auth();
        $property = $room->property();
        if ($user->role === 'owner' && $property && $property->owner_id !== $user->id) {
            $this->flash('error', 'Tidak memiliki izin');
            return $this->back();
        }

        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['empty', 'occupied', 'maintenance'])) {
            $this->flash('error', 'Status tidak valid');
            return $this->back();
        }

        $db = db();
        $db->table('rooms')->where('id', $id)->update(['status' => $status]);
        
        log_activity('room_status_update', "Room status changed to {$status}", 'room', $id);
        $this->flash('success', 'Status kamar diperbarui');
        return $this->back();
    }
}