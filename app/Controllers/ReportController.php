<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Property.php';
require_once __DIR__ . '/../Models/Room.php';
require_once __DIR__ . '/../Models/Tenant.php';
require_once __DIR__ . '/../Models/Invoice.php';
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Complaint.php';

class ReportController extends Controller {
    public function index() {
        $this->authorize('view_reports');
        $user = auth();
        
        $this->view('reports.index');
    }

    public function financial() {
        $this->authorize('view_reports');
        $user = auth();
        $propertyId = $_GET['property_id'] ?? null;
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        $db = db();
        
        // Get properties
        $properties = Property::getUserProperties($user->id);
        
        // Build property filter
        $propertyFilter = '';
        $params = [];
        if ($propertyId) {
            $property = Property::find($propertyId);
            if ($property && ($user->role !== 'owner' || $property->owner_id === $user->id)) {
                $propertyFilter = 'AND r.property_id = ?';
                $params[] = $propertyId;
            }
        } elseif ($user->role === 'owner') {
            $propIds = array_column($properties, 'id');
            if ($propIds) {
                $placeholders = implode(',', array_fill(0, count($propIds), '?'));
                $propertyFilter = "AND r.property_id IN ({$placeholders})";
                $params = array_merge($params, $propIds);
            }
        }
        
        // Revenue by month
        $revenueByMonth = $db->query("
            SELECT 
                DATE_FORMAT(p.payment_date, '%Y-%m') as month,
                SUM(p.amount) as total
            FROM payments p
            JOIN invoices i ON p.invoice_id = i.id
            JOIN tenants t ON p.tenant_id = t.id
            JOIN rooms r ON t.room_id = r.id
            WHERE p.status = 'verified'
            AND p.payment_date BETWEEN ? AND ?
            {$propertyFilter}
            GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m')
            ORDER BY month
        ", array_merge([$startDate, $endDate], $params))->fetchAll();
        
        // Revenue by property
        $revenueByProperty = $db->query("
            SELECT 
                pr.name as property_name,
                SUM(p.amount) as total
            FROM payments p
            JOIN invoices i ON p.invoice_id = i.id
            JOIN tenants t ON p.tenant_id = t.id
            JOIN rooms r ON t.room_id = r.id
            JOIN properties pr ON r.property_id = pr.id
            WHERE p.status = 'verified'
            AND p.payment_date BETWEEN ? AND ?
            {$propertyFilter}
            GROUP BY pr.id, pr.name
            ORDER BY total DESC
        ", array_merge([$startDate, $endDate], $params))->fetchAll();
        
        // Revenue by payment method
        $revenueByMethod = $db->query("
            SELECT 
                p.payment_method,
                COUNT(*) as count,
                SUM(p.amount) as total
            FROM payments p
            JOIN invoices i ON p.invoice_id = i.id
            JOIN tenants t ON p.tenant_id = t.id
            JOIN rooms r ON t.room_id = r.id
            WHERE p.status = 'verified'
            AND p.payment_date BETWEEN ? AND ?
            {$propertyFilter}
            GROUP BY p.payment_method
        ", array_merge([$startDate, $endDate], $params))->fetchAll();
        
        // Outstanding invoices
        $outstanding = $db->query("
            SELECT 
                i.invoice_number,
                i.total_amount,
                i.due_date,
                u.name as tenant_name,
                r.room_number,
                pr.name as property_name,
                (i.total_amount - COALESCE((
                    SELECT SUM(amount) FROM payments 
                    WHERE invoice_id = i.id AND status = 'verified'
                ), 0)) as remaining
            FROM invoices i
            JOIN tenants t ON i.tenant_id = t.id
            JOIN users u ON t.user_id = u.id
            JOIN rooms r ON t.room_id = r.id
            JOIN properties pr ON r.property_id = pr.id
            WHERE i.status IN ('unpaid', 'partial', 'overdue')
            {$propertyFilter}
            ORDER BY i.due_date
        ", $params)->fetchAll();
        
        // Total stats
        $totalRevenue = array_sum(array_column($revenueByMonth, 'total'));
        $totalOutstanding = array_sum(array_column($outstanding, 'remaining'));
        
        $this->view('reports.financial', [
            'properties' => $properties,
            'propertyId' => $propertyId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'revenueByMonth' => $revenueByMonth,
            'revenueByProperty' => $revenueByProperty,
            'revenueByMethod' => $revenueByMethod,
            'outstanding' => $outstanding,
            'totalRevenue' => $totalRevenue,
            'totalOutstanding' => $totalOutstanding,
        ]);
    }

    public function occupancy() {
        $this->authorize('view_reports');
        $user = auth();
        $propertyId = $_GET['property_id'] ?? null;
        
        $db = db();
        $properties = Property::getUserProperties($user->id);
        
        $propertyFilter = '';
        $params = [];
        if ($propertyId) {
            $property = Property::find($propertyId);
            if ($property && ($user->role !== 'owner' || $property->owner_id === $user->id)) {
                $propertyFilter = 'WHERE p.id = ?';
                $params[] = $propertyId;
            }
        } elseif ($user->role === 'owner') {
            $propIds = array_column($properties, 'id');
            if ($propIds) {
                $placeholders = implode(',', array_fill(0, count($propIds), '?'));
                $propertyFilter = "WHERE p.id IN ({$placeholders})";
                $params = $propIds;
            }
        }
        
        // Occupancy by property
        $occupancy = $db->query("
            SELECT 
                p.name as property_name,
                COUNT(r.id) as total_rooms,
                SUM(CASE WHEN r.status = 'occupied' THEN 1 ELSE 0 END) as occupied,
                SUM(CASE WHEN r.status = 'empty' THEN 1 ELSE 0 END) as empty,
                SUM(CASE WHEN r.status = 'maintenance' THEN 1 ELSE 0 END) as maintenance
            FROM properties p
            LEFT JOIN rooms r ON p.id = r.property_id
            {$propertyFilter}
            GROUP BY p.id, p.name
        ", $params)->fetchAll();
        
        // Occupancy by room type
        $occupancyByType = $db->query("
            SELECT 
                r.type,
                COUNT(r.id) as total,
                SUM(CASE WHEN r.status = 'occupied' THEN 1 ELSE 0 END) as occupied
            FROM rooms r
            JOIN properties p ON r.property_id = p.id
            {$propertyFilter}
            GROUP BY r.type
        ", $params)->fetchAll();
        
        // Occupancy trend (last 12 months) - propertyFilter starts with WHERE, convert to AND
        $trendFilter = $propertyFilter ? preg_replace('/^WHERE\s+/i', 'AND ', $propertyFilter) : '';
        $occupancyTrend = $db->query("
            SELECT 
                DATE_FORMAT(i.period_year, '%Y') as year,
                DATE_FORMAT(i.period_month, '%m') as month,
                COUNT(DISTINCT i.tenant_id) as active_tenants
            FROM invoices i
            JOIN tenants t ON i.tenant_id = t.id
            JOIN rooms r ON t.room_id = r.id
            JOIN properties p ON r.property_id = p.id
            WHERE i.status IN ('paid', 'partial')
            AND CONCAT(i.period_year, '-', LPAD(i.period_month, 2, '0')) >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 12 MONTH), '%Y-%m')
            {$trendFilter}
            GROUP BY i.period_year, i.period_month
            ORDER BY i.period_year, i.period_month
        ", $params)->fetchAll();
        
        $totalRooms = array_sum(array_column($occupancy, 'total_rooms'));
        $totalOccupied = array_sum(array_column($occupancy, 'occupied'));
        $overallRate = $totalRooms > 0 ? round(($totalOccupied / $totalRooms) * 100, 1) : 0;
        
        $this->view('reports.occupancy', [
            'properties' => $properties,
            'propertyId' => $propertyId,
            'occupancy' => $occupancy,
            'occupancyByType' => $occupancyByType,
            'occupancyTrend' => $occupancyTrend,
            'totalRooms' => $totalRooms,
            'totalOccupied' => $totalOccupied,
            'overallRate' => $overallRate,
        ]);
    }

    public function arrears() {
        $this->authorize('view_reports');
        $user = auth();
        $propertyId = $_GET['property_id'] ?? null;
        
        $db = db();
        $properties = Property::getUserProperties($user->id);
        
        $propertyFilter = '';
        $params = [];
        if ($propertyId) {
            $property = Property::find($propertyId);
            if ($property && ($user->role !== 'owner' || $property->owner_id === $user->id)) {
                $propertyFilter = 'AND pr.id = ?';
                $params[] = $propertyId;
            }
        } elseif ($user->role === 'owner') {
            $propIds = array_column($properties, 'id');
            if ($propIds) {
                $placeholders = implode(',', array_fill(0, count($propIds), '?'));
                $propertyFilter = "AND pr.id IN ({$placeholders})";
                $params = $propIds;
            }
        }
        
        // Arrears by tenant
        $arrears = $db->query("
            SELECT 
                t.id as tenant_id,
                u.name as tenant_name,
                u.phone as tenant_phone,
                r.room_number,
                pr.name as property_name,
                COUNT(i.id) as unpaid_count,
                SUM(i.total_amount) as total_billed,
                SUM(i.total_amount - COALESCE((
                    SELECT SUM(amount) FROM payments 
                    WHERE invoice_id = i.id AND status = 'verified'
                ), 0)) as total_arrears,
                MAX(i.due_date) as oldest_due_date
            FROM invoices i
            JOIN tenants t ON i.tenant_id = t.id
            JOIN users u ON t.user_id = u.id
            JOIN rooms r ON t.room_id = r.id
            JOIN properties pr ON r.property_id = pr.id
            WHERE i.status IN ('unpaid', 'partial', 'overdue')
            {$propertyFilter}
            GROUP BY t.id, u.name, u.phone, r.room_number, pr.name
            HAVING total_arrears > 0
            ORDER BY total_arrears DESC
        ", $params)->fetchAll();
        
        // Arrears aging
        $today = date('Y-m-d');
        $aging = [
            'current' => ['label' => 'Belum Jatuh Tempo', 'amount' => 0, 'count' => 0],
            '1-30' => ['label' => '1-30 Hari', 'amount' => 0, 'count' => 0],
            '31-60' => ['label' => '31-60 Hari', 'amount' => 0, 'count' => 0],
            '61-90' => ['label' => '61-90 Hari', 'amount' => 0, 'count' => 0],
            '90+' => ['label' => '90+ Hari', 'amount' => 0, 'count' => 0],
        ];
        
        $overdueInvoices = $db->query("
            SELECT 
                i.id,
                i.total_amount,
                i.due_date,
                COALESCE((
                    SELECT SUM(amount) FROM payments 
                    WHERE invoice_id = i.id AND status = 'verified'
                ), 0) as paid_amount
            FROM invoices i
            JOIN tenants t ON i.tenant_id = t.id
            JOIN rooms r ON t.room_id = r.id
            JOIN properties pr ON r.property_id = pr.id
            WHERE i.status IN ('unpaid', 'partial', 'overdue')
            {$propertyFilter}
        ", $params)->fetchAll();
        
        foreach ($overdueInvoices as $inv) {
            $remaining = $inv['total_amount'] - $inv['paid_amount'];
            if ($remaining <= 0) continue;
            
            $due = new DateTime($inv['due_date']);
            $now = new DateTime($today);
            $isOverdue = $now > $due;
            
            if (!$isOverdue) {
                $aging['current']['amount'] += $remaining;
                $aging['current']['count']++;
            } else {
                $diff = $now->diff($due)->days;
                if ($diff <= 30) {
                    $aging['1-30']['amount'] += $remaining;
                    $aging['1-30']['count']++;
                } elseif ($diff <= 60) {
                    $aging['31-60']['amount'] += $remaining;
                    $aging['31-60']['count']++;
                } elseif ($diff <= 90) {
                    $aging['61-90']['amount'] += $remaining;
                    $aging['61-90']['count']++;
                } else {
                    $aging['90+']['amount'] += $remaining;
                    $aging['90+']['count']++;
                }
            }
        }
        
        $totalArrears = array_sum(array_column($arrears, 'total_arrears'));
        $totalTenantsWithArrears = count($arrears);
        
        $this->view('reports.arrears', [
            'properties' => $properties,
            'propertyId' => $propertyId,
            'arrears' => $arrears,
            'aging' => $aging,
            'totalArrears' => $totalArrears,
            'totalTenantsWithArrears' => $totalTenantsWithArrears,
        ]);
    }

    public function export($type) {
        $this->authorize('view_reports');
        
        // Simple CSV export
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $type . '_' . date('Ymd') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        switch ($type) {
            case 'financial':
                fputcsv($output, ['Tanggal', 'Nomor Tagihan', 'Penyewa', 'Kamar', 'Properti', 'Jumlah', 'Metode', 'Status']);
                // Would add actual data here
                break;
            case 'occupancy':
                fputcsv($output, ['Properti', 'Total Kamar', 'Terisi', 'Kosong', 'Maintenance', 'Occupancy Rate']);
                break;
            case 'arrears':
                fputcsv($output, ['Penyewa', 'Telepon', 'Kamar', 'Properti', 'Jumlah Tagihan', 'Total Tunggakan', 'Tunggakan Terlama']);
                break;
        }
        
        fclose($output);
        exit;
    }
}