<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class InternshipController extends Controller
{
    // Get all internships
    public function index()
    {
        $internships = Internship::all();
        return response()->json($internships);
    }

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

    // Show a single internship
    public function show($id)
    {
        $internship = Internship::find($id);

        if (!$internship) {
            return response()->json(['message' => 'Internship not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($internship, 200);
    }

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

    // Delete an internship
    public function destroy($id)
    {
        $internship = Internship::find($id);

        if (!$internship) {
            return response()->json(['message' => 'Internship not found'], Response::HTTP_NOT_FOUND);
        }

        $internship->delete();

        return response()->json(['message' => 'Internship deleted successfully']);
    }
}
