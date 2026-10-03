<?php

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Property.php';
require_once __DIR__ . '/../Models/Room.php';
require_once __DIR__ . '/../Models/Tenant.php';
require_once __DIR__ . '/../Models/Invoice.php';
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Complaint.php';
require_once __DIR__ . '/../Models/Notification.php';

class DashboardController extends Controller {
    public function index() {
        $user = auth();
        $this->authorize('view_dashboard');
        
        $db = db();
        $data = [];

        if ($user->role === 'owner' || $user->role === 'admin') {
            // Get properties for owner/admin
            $propertyQuery = Property::query()->where('is_active', 1);
            if ($user->role === 'owner') {
                $propertyQuery->where('owner_id', $user->id);
            }
            $properties = $propertyQuery->get();
            $propertyIds = array_column($properties, 'id');

            // Room stats
            $totalRooms = 0;
            $occupiedRooms = 0;
            $emptyRooms = 0;
            $maintenanceRooms = 0;
            
            if ($propertyIds) {
                $rooms = $db->table('rooms')
                    ->whereIn('property_id', $propertyIds)
                    ->get();
                
                $totalRooms = count($rooms);
                $occupiedRooms = count(array_filter($rooms, fn($r) => $r['status'] === 'occupied'));
                $emptyRooms = count(array_filter($rooms, fn($r) => $r['status'] === 'empty'));
                $maintenanceRooms = count(array_filter($rooms, fn($r) => $r['status'] === 'maintenance'));
            }

            // Tenant stats
            $totalTenants = 0;
            $activeTenants = 0;
            if ($propertyIds) {
                $tenants = $db->table('tenants t')
                    ->join('rooms r', 't.room_id', '=', 'r.id')
                    ->whereIn('r.property_id', $propertyIds)
                    ->get();
                
                $totalTenants = count($tenants);
                $activeTenants = count(array_filter($tenants, fn($t) => $t['status'] === 'active'));
            }

            // Financial stats - current month
            $currentMonth = (int) date('m');
            $currentYear = (int) date('Y');
            
            $totalRevenue = 0;
            $totalArrears = 0;
            $paidInvoices = 0;
            $unpaidInvoices = 0;
            $overdueInvoices = 0;
            
            if ($propertyIds) {
                // Revenue this month
                $monthStart = sprintf('%04d-%02d-01', $currentYear, $currentMonth);
                $monthEnd = date('Y-m-t', strtotime($monthStart));
                
                $totalRevenue = $db->table('payments p')
                    ->join('invoices i', 'p.invoice_id', '=', 'i.id')
                    ->join('tenants t', 'i.tenant_id', '=', 't.id')
                    ->join('rooms r', 't.room_id', '=', 'r.id')
                    ->whereIn('r.property_id', $propertyIds)
                    ->where('p.status', 'verified')
                    ->where('p.payment_date', '>=', $monthStart)
                    ->where('p.payment_date', '<=', $monthEnd)
                    ->sum('p.amount');

                // Invoice stats
                $invoices = $db->table('invoices i')
                    ->join('tenants t', 'i.tenant_id', '=', 't.id')
                    ->join('rooms r', 't.room_id', '=', 'r.id')
                    ->whereIn('r.property_id', $propertyIds)
                    ->where('i.period_month', $currentMonth)
                    ->where('i.period_year', $currentYear)
                    ->get();

                foreach ($invoices as $inv) {
                    switch ($inv['status']) {
                        case 'paid':
                            $paidInvoices++;
                            break;
                        case 'unpaid':
                        case 'partial':
                            $unpaidInvoices++;
                            $totalArrears += $inv['total_amount'];
                            break;
                        case 'overdue':
                            $overdueInvoices++;
                            $totalArrears += $inv['total_amount'];
                            break;
                    }
                }
            }

            // Recent activities
            $recentPayments = $db->table('payments p')
                ->join('invoices i', 'p.invoice_id', '=', 'i.id')
                ->join('tenants t', 'i.tenant_id', '=', 't.id')
                ->join('users u', 't.user_id', '=', 'u.id')
                ->join('rooms r', 't.room_id', '=', 'r.id')
                ->whereIn('r.property_id', $propertyIds)
                ->where('p.status', 'verified')
                ->select('p.*', 'u.name as tenant_name', 'r.room_number')
                ->orderBy('p.verified_at', 'DESC')
                ->limit(5)
                ->get();

            $recentComplaints = $db->table('complaints c')
                ->join('tenants t', 'c.tenant_id', '=', 't.id')
                ->join('users u', 't.user_id', '=', 'u.id')
                ->join('rooms r', 'c.room_id', '=', 'r.id')
                ->whereIn('r.property_id', $propertyIds)
                ->select('c.*', 'u.name as tenant_name', 'r.room_number')
                ->orderBy('c.created_at', 'DESC')
                ->limit(5)
                ->get();
            
            $categoryLabels = [
                'electricity' => 'Listrik', 'water' => 'Air', 'internet' => 'Internet',
                'furniture' => 'Furniture', 'cleanliness' => 'Kebersihan', 'security' => 'Keamanan', 'other' => 'Lainnya',
            ];
            $priorityLabels = ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'urgent' => 'Mendesak'];
            $priorityBadges = ['low' => 'bg-green-100 text-green-800', 'medium' => 'bg-blue-100 text-blue-800', 'high' => 'bg-orange-100 text-orange-800', 'urgent' => 'bg-red-100 text-red-800'];
            $statusLabels = ['open' => 'Dibuka', 'in_progress' => 'Diproses', 'resolved' => 'Selesai', 'closed' => 'Ditutup'];
            $statusBadges = ['open' => 'bg-blue-100 text-blue-800', 'in_progress' => 'bg-yellow-100 text-yellow-800', 'resolved' => 'bg-green-100 text-green-800', 'closed' => 'bg-gray-100 text-gray-800'];
            
            foreach ($recentComplaints as &$complaint) {
                $complaint['category_label'] = $categoryLabels[$complaint['category']] ?? $complaint['category'];
                $complaint['priority_label'] = $priorityLabels[$complaint['priority']] ?? $complaint['priority'];
                $complaint['priority_badge'] = $priorityBadges[$complaint['priority']] ?? 'bg-gray-100 text-gray-800';
                $complaint['status_label'] = $statusLabels[$complaint['status']] ?? $complaint['status'];
                $complaint['status_badge'] = $statusBadges[$complaint['status']] ?? 'bg-gray-100 text-gray-800';
            }

            $data = array_merge($data, [
                'properties' => $properties,
                'totalRooms' => $totalRooms,
                'occupiedRooms' => $occupiedRooms,
                'emptyRooms' => $emptyRooms,
                'maintenanceRooms' => $maintenanceRooms,
                'occupancyRate' => $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0,
                'totalTenants' => $totalTenants,
                'activeTenants' => $activeTenants,
                'totalRevenue' => $totalRevenue,
                'totalArrears' => $totalArrears,
                'paidInvoices' => $paidInvoices,
                'unpaidInvoices' => $unpaidInvoices,
                'overdueInvoices' => $overdueInvoices,
                'recentPayments' => $recentPayments,
                'recentComplaints' => $recentComplaints,
            ]);

        } elseif ($user->role === 'tenant') {
            $tenant = Tenant::where('user_id', $user->id)->where('status', 'active')->first();
            
            $data['tenant'] = $tenant;
            $data['currentInvoice'] = null;
            $data['unpaidInvoices'] = [];
            $data['totalArrears'] = 0;
            $data['recentPayments'] = [];
            $data['recentComplaints'] = [];
            
            if ($tenant) {
                $data['currentInvoice'] = $tenant->getCurrentInvoice();
                $data['unpaidInvoices'] = $tenant->getUnpaidInvoices();
                $data['totalArrears'] = $tenant->getTotalArrears();
                $data['recentPayments'] = $tenant->payments()->where('status', 'verified')->limit(5)->get();
                $data['recentComplaints'] = $tenant->complaints()->limit(5)->get();
            }
        }

        // Notifications for all users
        $notifications = Notification::where('user_id', $user->id)
            ->where('is_read', 0)
            ->limit(10)
            ->get();
        $unreadCount = Notification::getUnreadCount($user->id);

        $data['notifications'] = $notifications;
        $data['unreadCount'] = $unreadCount;
        $data['currentMonth'] = date('F Y');

        $this->view('dashboard.index', $data);
    }
}