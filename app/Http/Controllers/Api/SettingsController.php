<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function index()
    {
        return response()->json([
            'settings' => [
                'fine_rate' => (int) cache('fine_rate', 2000),
                'max_loan_days' => (int) cache('max_loan_days', 7)
            ]
        ]);
    }

    public function update(\Illuminate\Http\Request $request)
    {
        $fineRate = (int) $request->input('fine_rate', 2000);
        $maxLoanDays = (int) $request->input('max_loan_days', 7);

        cache()->forever('fine_rate', $fineRate);
        cache()->forever('max_loan_days', $maxLoanDays);

        return response()->json([
            'message' => 'Pengaturan sirkulasi berhasil diperbarui',
            'settings' => [
                'fine_rate' => $fineRate,
                'max_loan_days' => $maxLoanDays
            ]
        ]);
    }
}
