<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InternshipController extends Controller
{
    /**
     * @OA\PathItem(path="/api/internships")
     */
    /**
     * @OA\Get(
     *     path="/api/internships",
     *     tags={"Internships"},
     *     summary="Get all internships",
     *     description="Returns a list of all internships",
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    // Get all internships
    public function index()
    {
        $internships = Internship::all();
        return response()->json($internships);
    }

    /**
     * @OA\PathItem(path="/api/internships")
     */
    /**
     * @OA\Post(
     *     path="/api/internships",
     *     tags={"Internships"},
     *     summary="Store a new internship",
     *     description="Stores a new internship and returns the created internship",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Software Engineering Internship"),
     *             @OA\Property(property="major_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="A great opportunity to learn software development."),
     *             @OA\Property(property="link", type="string", format="url", example="https://example.com/internship")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Internship created successfully"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    // Store a new internship
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'major_id' => 'required|exists:majors,id',
            'description' => 'required|string',
            'link' => 'required|url',
        ]);

        $internship = Internship::create($request->all());

        return response()->json($internship, Response::HTTP_CREATED);
    }

    /**
     * @OA\PathItem(path="/api/internships/{id}")
     */
    /**
     * @OA\Get(
     *     path="/api/internships/{id}",
     *     tags={"Internships"},
     *     summary="Show a single internship",
     *     description="Returns a single internship by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the internship",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Internship not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    // Show a single internship
    public function show($id)
    {
        $internship = Internship::find($id);

        if (!$internship) {
            return response()->json(['message' => 'Internship not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($internship, 200);
    }

    /**
     * @OA\PathItem(path="/api/internships/{id}")
     */
    /**
     * @OA\Put(
     *     path="/api/internships/{id}",
     *     tags={"Internships"},
     *     summary="Update an existing internship",
     *     description="Updates an existing internship and returns the updated internship",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the internship",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Updated Internship Title"),
     *             @OA\Property(property="major_id", type="integer", example=2),
     *             @OA\Property(property="description", type="string", example="Updated description of the internship."),
     *             @OA\Property(property="link", type="string", format="url", example="https://example.com/updated-internship")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Internship updated successfully"),
     *     @OA\Response(response=404, description="Internship not found"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    // Update an existing internship
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'major_id' => 'sometimes|required|exists:majors,id',
            'description' => 'sometimes|required|string',
            'link' => 'sometimes|required|url',
        ]);

        $internship = Internship::find($id);

        if (!$internship) {
            return response()->json(['message' => 'Internship not found'], Response::HTTP_NOT_FOUND);
        }

        $internship->update($request->all());

        return response()->json($internship, Response::HTTP_OK);
    }

    /**
     * @OA\PathItem(path="/api/internships/{id}")
     */
    /**
     * @OA\Delete(
     *     path="/api/internships/{id}",
     *     tags={"Internships"},
     *     summary="Delete an internship",
     *     description="Deletes an existing internship by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the internship",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Internship deleted successfully"),
     *     @OA\Response(response=404, description="Internship not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    // Delete an internship
    public function destroy($id)
    {
        $internship = Internship::find($id);

        if (!$internship) {
            return response()->json(['message' => 'Internship not found'], 404);
        }

        $internship->delete();

        return response()->json(['message' => 'Internship deleted successfully']);
    }
}
