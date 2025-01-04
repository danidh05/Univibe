<?php

namespace App\Http\Controllers;

use App\Models\Aboutus;
use App\Models\AboutusDetails;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Univibe API",
 *     version="1.0.0",
 *     description="API documentation for Univibe project",
 *     @OA\Contact(
 *         name="Your Name",
 *         email="your.email@example.com"
 *     )
 * )
 */

/**
 * @OA\Tag(
 *     name="About Us",
 *     description="About Us management endpoints"
 * )
 */
class AdminController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/about-us/titles",
     *     tags={"About Us"},
     *     summary="Create an About Us title",
     *     description="Creates a new About Us title and returns the created title",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="About Our Company")
     *         )
     *     ),
     *     @OA\Response(response=201, description="About Us title created successfully"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/about-us/{aboutUsId}/details",
     *     tags={"About Us"},
     *     summary="Add detail to About Us",
     *     description="Creates a new detail for the specified About Us entry",
     *     @OA\Parameter(
     *         name="aboutUsId",
     *         in="path",
     *         required=true,
     *         description="ID of the About Us entry",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="description", type="string", example="We are dedicated to excellence...")
     *         )
     *     ),
     *     @OA\Response(response=201, description="About Us detail added successfully"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="About Us entry not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function createAboutUsDetail(Request $request, $aboutUsId)
    {
        // Validate the request data
        $request->validate([
            'description' => 'required|string|max:500',
        ]);
    
        // Find the Aboutus record by ID
        $aboutUs = Aboutus::findOrFail($aboutUsId);
    
        // Create the AboutusDetails record with the correct about_us_id
        $aboutUsDetail = AboutusDetails::create([
            'description' => $request->input('description'),
            'about_us_id' => $aboutUs->id, // Use the ID of the Aboutus model
        ]);
    
        // Return the response with the created AboutusDetails
        return response()->json([
            'message' => 'About Us detail added successfully!',
            'data' => [
                'id' => $aboutUsDetail->id,
                'description' => $aboutUsDetail->description,
                'about_us_id' => $aboutUsDetail->about_us_id,
            ],
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/about-us",
     *     tags={"About Us"},
     *     summary="Get all About Us entries with details",
     *     description="Retrieves all About Us entries along with their details",
     *     @OA\Response(response=200, description="About Us entries retrieved successfully"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getAllAboutUsWithDetails()
    {
        $aboutUsEntries = Aboutus::with('details')->get();

        return response()->json([
            'message' => 'About Us entries retrieved successfully!',
            'data' => $aboutUsEntries
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/about-us/{aboutUsId}",
     *     tags={"About Us"},
     *     summary="Get a single About Us entry with details",
     *     description="Retrieves a single About Us entry by ID along with its details",
     *     @OA\Parameter(
     *         name="aboutUsId",
     *         in="path",
     *         required=true,
     *         description="ID of the About Us entry",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="About Us entry retrieved successfully"),
     *     @OA\Response(response=404, description="About Us entry not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getsingleAboutUsWithDetails(Request $request, $aboutUsId)
    {
        $aboutUs = Aboutus::with('details')->findOrFail($aboutUsId);
        
        return response()->json([
            'message' => 'About Us entry retrieved successfully!',
            'data' => $aboutUs
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/about-us/{id}",
     *     tags={"About Us"},
     *     summary="Update an About Us title",
     *     description="Updates the specified About Us title",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the About Us entry",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Updated About Us Title")
     *         )
     *     ),
     *     @OA\Response(response=200, description="About Us updated successfully"),
     *     @OA\Response(response=404, description="About Us entry not found"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function updateAboutUs(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $aboutUs = Aboutus::findOrFail($id);
        $aboutUs->title = $request->input('title');
        $aboutUs->save();

        return response()->json([
            'message' => 'About Us updated successfully!',
            'data' => $aboutUs
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/about-us/{aboutUsId}/details",
     *     tags={"About Us"},
     *     summary="Update an About Us detail",
     *     description="Updates the specified detail for the About Us entry",
     *     @OA\Parameter(
     *         name="aboutUsId",
     *         in="path",
     *         required=true,
     *         description="ID of the About Us entry",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="description", type="string", example="Updated description for About Us")
     *         )
     *     ),
     *     @OA\Response(response=200, description="About Us detail updated successfully"),
     *     @OA\Response(response=404, description="About Us entry not found"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function updateAboutUsDetail(Request $request, $aboutUsId)
    {
        $request->validate([
            'description' => 'required|string|max:500',
        ]);

        $aboutUs = AboutUs::findOrFail($aboutUsId);

        $aboutUsDetail = AboutusDetails::where('about_us_id', $aboutUsId)->firstOrFail();
        
        $aboutUsDetail->description = $request->input('description');
        $aboutUsDetail->save();

        return response()->json([
            'message' => 'About Us detail updated successfully!',
            'data' => $aboutUsDetail
        ], 200);
    }
}