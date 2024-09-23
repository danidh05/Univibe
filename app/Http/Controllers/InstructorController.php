<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InstructorController extends Controller
{
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

    // Show a specific instructor
    public function show($id)
    {
        $instructor = Instructor::find($id);

        if (!$instructor) {
            return response()->json(['message' => 'Instructor not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($instructor, Response::HTTP_OK);
    }

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
