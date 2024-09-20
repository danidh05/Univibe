<?php

namespace App\Http\Controllers;

use App\Models\Aboutus;
use App\Models\AboutusDetails;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function createAboutUsTitle(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $aboutUs = Aboutus::create([
            'title' => $request->input('title'),
        ]);

        return response()->json([
            'message' => 'About Us title created successfully!',
            'data' => $aboutUs
        ], 201);
    }
    public function createAboutUsDetail(Request $request, $aboutUsId)
    {
        $request->validate([
            'description' => 'required|string|max:500',
        ]);



        $aboutUsDetail = AboutusDetails::create([
            'description' => $request->input('description'),
            'about_us_id' => $aboutUsId
        ]);
        return response()->json([
            'message' => 'About Us detail added successfully!',
            'data' => $aboutUsDetail
        ], 201);
    }

    public function getAllAboutUsWithDetails()
    {
        $aboutUsEntries = Aboutus::with('details')->get();

        return response()->json([
            'message' => 'About Us entries retrieved successfully!',
            'data' => $aboutUsEntries
        ], 200);
    }
    public function getsingleAboutUsWithDetails(Request $request, $aboutUsId)
    {
        $aboutUs = Aboutus::with('details')->findOrFail($aboutUsId);
        $aboutUsWithDetails = $aboutUs->details;
        return response()->json([
            'data' => $aboutUsWithDetails
        ], 200);
    }
}