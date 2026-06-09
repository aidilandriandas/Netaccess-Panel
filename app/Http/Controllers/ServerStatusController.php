<?php

namespace App\Http\Controllers;

use App\Services\ServerMonitorService;

class ServerStatusController extends Controller
{
    public function index(ServerMonitorService $monitor)
    {
        $report = $monitor->getFullReport();
        return view('server-status.index', compact('report'));
    }
}
