<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Property.php';
require_once __DIR__ . '/../Models/Room.php';

class PropertyController extends Controller {
    public function index() {
        $this->authorize('manage_properties');
        $user = auth();
        
        $query = Property::query()->where('is_active', 1);
        if ($user->role === 'owner') {
            $query->where('owner_id', $user->id);
        }
        
        $search = $_GET['search'] ?? '';
        if ($search) {
            $like = "%{$search}%";
            $query->whereRaw("(name LIKE :s1 OR address LIKE :s2 OR city LIKE :s3)", [
                's1' => $like,
                's2' => $like,
                's3' => $like,
            ]);
        }
        
        $properties = $query->orderBy('created_at', 'DESC')->paginate(15);
        
        // Add room counts
        $db = db();
        foreach ($properties['data'] as &$prop) {
            $prop['rooms_count'] = $db->table('rooms')->where('property_id', $prop['id'])->count();
            $prop['occupied_count'] = $db->table('rooms')->where('property_id', $prop['id'])->where('status', 'occupied')->count();
            $prop['occupancy_rate'] = $prop['rooms_count'] > 0 ? round(($prop['occupied_count'] / $prop['rooms_count']) * 100, 1) : 0;
        }
        
        $this->view('properties.index', [
            'properties' => $properties,
            'search' => $search,
        ]);
    }

    public function create() {
        $this->authorize('manage_properties');
        $this->view('properties.create');
    }

    public function store() {
        $this->authorize('manage_properties');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $rules = [
            'name' => 'required|min:2|max:100',
            'address' => 'required',
            'city' => 'required|max:50',
            'province' => 'max:50',
            'postal_code' => 'max:10',
            'description' => 'max:1000',
            'latitude' => 'max:20',
            'longitude' => 'max:20',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        $user = auth();
        $data = [
            'owner_id' => $user->id,
            'name' => trim($_POST['name']),
            'address' => trim($_POST['address']),
            'city' => trim($_POST['city']),
            'province' => $_POST['province'] ?? null,
            'postal_code' => $_POST['postal_code'] ?? null,
            'description' => $_POST['description'] ?? null,
            'latitude' => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null,
            'is_active' => true,
        ];

        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $result = upload_file($_FILES['photo'], 'uploads/properties');
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

        $db = db();
        $id = $db->table('properties')->insert($data);
        
        log_activity('property_create', "Property created: {$data['name']}", 'property', $id);
        $this->flash('success', 'Properti berhasil ditambahkan');
        return $this->redirect(url('properties'));
    }

    public function show($id) {
        $this->authorize('manage_properties');
        $property = Property::find($id);
        
        if (!$property) {
            $this->abort(404, 'Properti tidak ditemukan');
        }
        
        $user = auth();
        if ($user->role === 'owner' && $property->owner_id !== $user->id) {
            $this->abort(403);
        }

        $rooms = Room::where('property_id', $id)->orderBy('room_number')->get();
        
        $totalRooms = count($rooms);
        $occupiedRooms = 0;
        $emptyRooms = 0;
        foreach ($rooms as $r) {
            if ($r->status === 'occupied') $occupiedRooms++;
            elseif ($r->status === 'empty') $emptyRooms++;
        }

        $this->view('properties.show', [
            'property' => $property,
            'rooms' => $rooms,
            'totalRooms' => $totalRooms,
            'occupiedRooms' => $occupiedRooms,
            'emptyRooms' => $emptyRooms,
            'occupancyRate' => $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0,
        ]);
    }

    public function edit($id) {
        $this->authorize('manage_properties');
        $property = Property::find($id);
        
        if (!$property) {
            $this->abort(404, 'Properti tidak ditemukan');
        }
        
        $user = auth();
        if ($user->role === 'owner' && $property->owner_id !== $user->id) {
            $this->abort(403);
        }

        $this->view('properties.edit', ['property' => $property]);
    }

    public function update($id) {
        $this->authorize('manage_properties');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $property = Property::find($id);
        if (!$property) {
            $this->abort(404, 'Properti tidak ditemukan');
        }
        
        $user = auth();
        if ($user->role === 'owner' && $property->owner_id !== $user->id) {
            $this->abort(403);
        }

        $rules = [
            'name' => 'required|min:2|max:100',
            'address' => 'required',
            'city' => 'required|max:50',
            'province' => 'max:50',
            'postal_code' => 'max:10',
            'description' => 'max:1000',
        ];

        if (!$this->validate($_POST, $rules)) {
            return $this->back();
        }

        $data = [
            'name' => trim($_POST['name']),
            'address' => trim($_POST['address']),
            'city' => trim($_POST['city']),
            'province' => $_POST['province'] ?? null,
            'postal_code' => $_POST['postal_code'] ?? null,
            'description' => $_POST['description'] ?? null,
            'latitude' => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null,
        ];

        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            // Delete old photo
            if ($property->photo) {
                delete_file('uploads/properties/' . $property->photo);
            }
            
            $result = upload_file($_FILES['photo'], 'uploads/properties');
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

        $db = db();
        $db->table('properties')->where('id', $id)->update($data);
        
        log_activity('property_update', "Property updated: {$data['name']}", 'property', $id);
        $this->flash('success', 'Properti berhasil diperbarui');
        return $this->redirect(url('properties/' . $id));
    }

    public function destroy($id) {
        $this->authorize('manage_properties');
        
        if (!verify_csrf()) {
            $this->flash('error', 'Token CSRF tidak valid');
            return $this->back();
        }

        $property = Property::find($id);
        if (!$property) {
            $this->flash('error', 'Properti tidak ditemukan');
            return $this->back();
        }
        
        $user = auth();
        if ($user->role === 'owner' && $property->owner_id !== $user->id) {
            $this->flash('error', 'Tidak memiliki izin');
            return $this->back();
        }

        $db = db();
        $db->table('properties')->where('id', $id)->update(['is_active' => false]);
        
        log_activity('property_delete', "Property deleted: {$property->name}", 'property', $id);
        $this->flash('success', 'Properti berhasil dihapus');
        return $this->redirect(url('properties'));
    }
}