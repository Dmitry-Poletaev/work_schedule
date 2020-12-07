<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkersController extends Controller
{
    public function index()
    {
        $workers = DB::table('workers')->get();

        return view('index', compact('workers'));
    }
}
