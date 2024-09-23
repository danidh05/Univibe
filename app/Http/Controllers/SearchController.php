<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Major;
use App\Models\University;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Profiler\Profile;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        try {
            // Get the search term from the request
            $searchTerm = $request->input('query');

            // Check if a search term was provided
            if (!$searchTerm) {
                return response()->json(['error' => 'No Data Found'], 400);
            }

            // Search for users by username or bio
            $users = User::where(function ($query) use ($searchTerm) {
                $query->where('username', 'like', '%' . $searchTerm . '%')
                      ->orWhere('bio', 'like', '%' . $searchTerm . '%');
            })->get();

            // Search for majors based only on the major name
            $majors = Major::where('major_name', 'like', '%' . $searchTerm . '%')->get();

            // Search for universities based on university name or location
            $universities = University::where('university_name', 'like', '%' . $searchTerm . '%')
                ->orWhere('location', 'like', '%' . $searchTerm . '%')
                ->get();

            // Combine the search results into a unified response, only showing relevant data
            return response()->json([
                'users' => $users->count() > 0 ? $users : [],
                'majors' => $majors->count() > 0 ? $majors : [],
                'universities' => $universities->count() > 0 ? $universities : []
            ]);

        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Log::error('Search query failed: ' . $e->getMessage());

            // Return a JSON response with a generic error message
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
