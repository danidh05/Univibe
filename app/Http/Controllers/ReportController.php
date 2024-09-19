<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function reportUser(Request $request)
    {
        // Validate the request
        $request->validate([
            'reported_user_id' => 'required|exists:users,id',
            'Descreption_reason' => 'nullable|string|max:1000',
        ]);

        $reporterUserId = Auth::id();

        // Create the report
        $report = Report::create([
            'reporter_user_id' => $reporterUserId,
            'reported_user_id' => $request->input('reported_user_id'),
            'Descreption_reason' => $request->input('Descreption_reason'),
        ]);

        return response()->json([
            'message' => 'User has been successfully reported.',
            'report' => $report,
        ], 201);
    }
    public function getMyReports()
    {
        $userId = Auth::id();

        // Get all reports where the authenticated user is the reporter
        $reports = Report::where('reporter_user_id', $userId)->get();

        return response()->json([
            'reports' => $reports,
        ], 200);
    }
}
