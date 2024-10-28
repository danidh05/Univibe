<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
 *     name="Instructors",
 *     description="Instructor management endpoints"
 * )
 */
class InstructorController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/instructors",
     *     tags={"Instructors"},
     *     summary="Store a new instructor",
     *     description="Stores a new instructor and returns the created instructor",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="rating", type="number", format="float", example=4.5),
     *             @OA\Property(property="university_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Instructor created successfully"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    // Create a new instructor
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rating' => 'required|numeric|between:0,5',
            'university_id' => 'required|exists:universities,id',
        ]);

        $instructor = Instructor::create([
            'name' => $request->input('name'),
            'rating' => $request->input('rating'),
            'university_id' => $request->input('university_id'),
        ]);

        return response()->json($instructor, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/instructors/{id}",
     *     tags={"Instructors"},
     *     summary="Show a specific instructor",
     *     description="Returns a single instructor by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the instructor",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Instructor not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    // Show a specific instructor
    public function show($id)
    {
        $instructor = Instructor::find($id);

        if (!$instructor) {
            return response()->json(['message' => 'Instructor not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($instructor, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *     path="/api/instructors/{id}",
     *     tags={"Instructors"},
     *     summary="Update an existing instructor",
     *     description="Updates an existing instructor and returns the updated instructor",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the instructor",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Updated Name"),
     *             @OA\Property(property="rating", type="number", format="float", example=4.7),
     *             @OA\Property(property="university_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Instructor updated successfully"),
     *     @OA\Response(response=404, description="Instructor not found"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    // Update an instructor
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'rating' => 'sometimes|required|numeric|between:0,5',
            'university_id' => 'sometimes|required|exists:universities,id',
        ]);

        $instructor = Instructor::find($id);

        if (!$instructor) {
            return response()->json(['message' => 'Instructor not found'], Response::HTTP_NOT_FOUND);
        }

        $instructor->update($request->only(['name', 'rating', 'university_id']));

        return response()->json($instructor, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/instructors/{id}",
     *     tags={"Instructors"},
     *     summary="Delete an instructor",
     *     description="Deletes an existing instructor by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the instructor",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Instructor deleted successfully"),
     *     @OA\Response(response=404, description="Instructor not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    // Delete an instructor
    public function destroy($id)
    {
        $instructor = Instructor::find($id);

        if (!$instructor) {
            return response()->json(['message' => 'Instructor not found'], Response::HTTP_NOT_FOUND);
        }

        $instructor->delete();

        return response()->json(['message' => 'Instructor deleted successfully']);
    }
}
