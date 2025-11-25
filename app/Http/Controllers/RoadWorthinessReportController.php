<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoadWorthinessReportController extends Controller
{
    /**
     * Show monthly road worthiness report.
     */
    public function index(Request $request)
    {
        if (!Gate::allows('view-reports')) {
            abort(403, 'Unauthorized. You do not have permission to view reports.');
        }
        $month = $request->query('month', now()->format('Y-m'));
        $startOfMonth = \Carbon\Carbon::parse($month)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::parse($month)->endOfMonth();

        // Expired vehicles
        $expired = Vehicle::with('customer')
            ->whereNotNull('road_worthiness_expires_at')
            ->where('road_worthiness_expires_at', '<', now()->toDateTimeString())
            ->orderBy('road_worthiness_expires_at', 'desc')
            ->get();

        // Expiring soon (within 30 days)
        $expiringSoon = Vehicle::with('customer')
            ->whereNotNull('road_worthiness_expires_at')
            ->where('road_worthiness_expires_at', '>=', now()->toDateTimeString())
            ->where('road_worthiness_expires_at', '<=', now()->addDays(30)->toDateTimeString())
            ->orderBy('road_worthiness_expires_at', 'asc')
            ->get();

        // Expiring this month
        $expiringThisMonth = Vehicle::with('customer')
            ->whereNotNull('road_worthiness_expires_at')
            ->whereBetween('road_worthiness_expires_at', [$startOfMonth->toDateTimeString(), $endOfMonth->toDateTimeString()])
            ->orderBy('road_worthiness_expires_at', 'asc')
            ->get();

        // Recently issued (this month)
        $recentlyIssued = Vehicle::with('customer')
            ->whereNotNull('road_worthiness_created_at')
            ->whereBetween('road_worthiness_created_at', [$startOfMonth->toDateTimeString(), $endOfMonth->toDateTimeString()])
            ->orderBy('road_worthiness_created_at', 'desc')
            ->get();

        return view('reports.road-worthiness', compact(
            'expired',
            'expiringSoon',
            'expiringThisMonth',
            'recentlyIssued',
            'month'
        ));
    }
}
