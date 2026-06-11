<?php

namespace App\Http\Controllers\API\NDF;

use App\Http\Controllers\Controller;
use App\Services\NDF\ExpenseReportExcelService;
use Illuminate\Http\Request;

class ExpenseReportController extends Controller
{
    public function export(Request $request, ExpenseReportExcelService $service)
    {
        $data = $request->validate([
            'users' => ['required', 'array'],
        ]);

        return $service->export($data['users']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
