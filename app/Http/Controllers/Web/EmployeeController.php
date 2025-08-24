<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees
     */
    public function index(): View
    {
        return view('admin.employees.index');
    }

    /**
     * Show the form for creating a new employee
     */
    public function create(): View
    {
        return view('admin.employees.create');
    }

    /**
     * Store a newly created employee in storage
     */
    public function store(Request $request)
    {
        return redirect()->route('employees.index')->with('success', 'Funcionário criado com sucesso!');
    }

    /**
     * Display the specified employee
     */
    public function show(Employee $employee): View
    {
        return view('admin.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit(Employee $employee): View
    {
        return view('admin.employees.edit', compact('employee'));
    }

    /**
     * Update the specified employee in storage
     */
    public function update(Request $request, Employee $employee)
    {
        return redirect()->route('employees.index')->with('success', 'Funcionário atualizado com sucesso!');
    }

    /**
     * Remove the specified employee from storage
     */
    public function destroy(Employee $employee)
    {
        return redirect()->route('employees.index')->with('success', 'Funcionário removido com sucesso!');
    }
}
