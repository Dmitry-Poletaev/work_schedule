<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;

class WorkScheduleController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request['startDate'];
        $endDate = $request['endDate'];
        $id = $request['id'];
        $schedule = new Schedule();
        $schedule->getSchedule($startDate, $endDate, $id);
    }
}
