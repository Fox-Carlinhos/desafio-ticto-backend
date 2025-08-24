<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimeRecordController extends Controller
{
    /**
     * Display employee's time records
     */
    public function index(): View
    {
        return view('employee.time-records.index');
    }

    /**
     * Store a new time record
     */
    public function store(Request $request)
    {
        return redirect()->route('time-records.index')->with('success', 'Ponto registrado com sucesso!');
    }

    /**
     * Display all time records
     */
    public function adminIndex(): View
    {
        return view('admin.time-records.index');
    }

    /**
     * Display time records for a specific employee
     */
    public function adminEmployee(Employee $employee): View
    {
        return view('admin.time-records.employee', compact('employee'));
    }
}
