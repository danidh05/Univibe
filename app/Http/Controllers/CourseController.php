<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        return response()->json($courses, 200);
    }
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required',
            'instructor_id' => 'nullable|exists:instructors,id',
            'isFree' => 'required|boolean',
            'price' => 'required|numeric|',
        ]);

        $course = Course::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'instructor_id' => $request->input('instructor_id'),
            'isFree' => $request->input('isFree'),
            'price' => $request->input('price'),
        ]);

        return response()->json($course, Response::HTTP_CREATED);
    }
    public function show($id)
    {
        // Find the course by ID
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($course, Response::HTTP_OK);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required',
            'instructor_id' => 'nullable|exists:instructors,id',
            'isFree' => 'required|boolean',
            'price' => 'numeric|required',
        ]);

        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], Response::HTTP_NOT_FOUND);
        }

        // Prepare the data to update
        $dataToUpdate = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'isFree' => $request->input('isFree'),
            'price' => $request->input('price'),
        ];

        // Only update instructor_id if it is provided in the request
        if ($request->has('instructor_id')) {
            $dataToUpdate['instructor_id'] = $request->input('instructor_id');
        } else {
            // Keep the existing instructor_id if not provided
            $dataToUpdate['instructor_id'] = $course->instructor_id;
        }

        $course->update($dataToUpdate);

        return response()->json($course, Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found'], Response::HTTP_NOT_FOUND);
        }

        $course->delete();

        return response()->json(['message' => 'Course deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}
