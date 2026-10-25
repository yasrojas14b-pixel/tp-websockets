<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $todayAppointments = Appointment::query()
            ->with(['client', 'service', 'addons'])
            ->whereDate('starts_at', today())
            ->orderBy('starts_at')
            ->get();

        return view('admin.dashboard', [
            'todayAppointments' => $todayAppointments,
            'pendingCount' => Appointment::query()
                ->where('status', AppointmentStatus::Pending)
                ->where('starts_at', '>=', now())
                ->count(),
            'weekCount' => Appointment::query()
                ->occupying()
                ->whereBetween('starts_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'monthRevenue' => Appointment::query()
                ->where('status', AppointmentStatus::Completed)
                ->whereBetween('starts_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total_price'),
            'clientCount' => Client::count(),
        ]);
    }
}
