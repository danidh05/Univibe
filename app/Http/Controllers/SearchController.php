<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Major;
use App\Models\University;
use Illuminate\Http\Request;

class SearchController extends Controller
{

      /**
     * @OA\Get(
     *     path="/search",
     *     summary="Search across users, majors, and universities",
     *     description="Allows users to search by a term across multiple resources, including users by username or bio, majors by name, and universities by name or location.",
     *     tags={"Search"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         description="Search term used to query users, majors, and universities",
     *         @OA\Schema(type="string", example="engineering")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful search results",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="users",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="username", type="string", example="johndoe"),
     *                     @OA\Property(property="bio", type="string", example="Avid reader and nature enthusiast."),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2022-01-01T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2022-01-02T12:00:00Z")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="majors",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="major_name", type="string", example="Computer Science"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2022-01-01T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2022-01-02T12:00:00Z")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="universities",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="university_name", type="string", example="Harvard University"),
     *                     @OA\Property(property="location", type="string", example="Cambridge, MA"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2022-01-01T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2022-01-02T12:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No Data Found - No search term provided",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="No Data Found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="An error occurred while processing your request.")
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        try {
            // Get the search term from the request
            $searchTerm = $request->input('query');
    
            // Check if a search term was provided
            if (!$searchTerm) {
                return response()->json(['error' => 'No Data Found'], 400);
            }
    
            // Search for users by username, bio, major name, or university name
            $users = User::where('username', 'like', '%' . $searchTerm . '%')
                         ->orWhere('bio', 'like', '%' . $searchTerm . '%')
                         ->orWhereHas('major', function ($query) use ($searchTerm) {
                             $query->where('major_name', 'like', '%' . $searchTerm . '%');
                         })
                         ->orWhereHas('university', function ($query) use ($searchTerm) {
                             $query->where('university_name', 'like', '%' . $searchTerm . '%');
                         })
                         ->with('major', 'university') // Eager-load the major and university relationships
                         ->get();
    
            // Search for majors based on major name
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
            // Log::error('Search query failed: ' . $e->getMessage());
    
            // Return a JSON response with a generic error message
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}