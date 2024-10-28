<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\PathItem(path="/api/courses")
 */
class CourseController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/courses",
     *     tags={"Courses"},
     *     summary="Get all courses",
     *     description="Returns a list of all courses",
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    // Get all courses
    public function index()
    {
        $courses = Course::all();
        return response()->json($courses);
    }

    /**
     * @OA\Post(
     *     path="/api/courses",
     *     tags={"Courses"},
     *     summary="Store a new course",
     *     description="Stores a new course and returns the created course",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Introduction to Programming"),
     *             @OA\Property(property="description", type="string", example="An introductory course on programming concepts."),
     *             @OA\Property(property="credits", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Course created successfully"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    // Store a new course
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'credits' => 'required|integer',
        ]);

        $course = Course::create($request->all());

        return response()->json($course, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/courses/{id}",
     *     tags={"Courses"},
     *     summary="Show a single course",
     *     description="Returns a single course by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the course",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Course not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    // Show a single course
    public function show($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($course, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/courses/{id}",
     *     tags={"Courses"},
     *     summary="Update an existing course",
     *     description="Updates an existing course and returns the updated course",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the course",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Updated Course Title"),
     *             @OA\Property(property="description", type="string", example="Updated description of the course."),
     *             @OA\Property(property="credits", type="integer", example=4)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Course updated successfully"),
     *     @OA\Response(response=404, description="Course not found"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    // Update an existing course
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'credits' => 'sometimes|required|integer',
        ]);

        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], Response::HTTP_NOT_FOUND);
        }

        $course->update($request->all());

        return response()->json($course, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/courses/{id}",
     *     tags={"Courses"},
     *     summary="Delete a course",
     *     description="Deletes an existing course by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the course",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Course deleted successfully"),
     *     @OA\Response(response=404, description="Course not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    // Delete a course
    public function destroy($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }
}
