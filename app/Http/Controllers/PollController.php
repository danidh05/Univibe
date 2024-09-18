<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PollOption;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function vote(Request $request, $postId)
    {
        $request->validate([
            'option_id' => 'required|exists:poll_options,id'
        ]);

        try {

            $pollOption = PollOption::where('id', $request->option_id)
                ->where('post_id', $postId)
                ->firstOrFail();

            $pollOption->increment('votes');

            return response()->json(['message' => 'Vote registered successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to vote'], 500);
        }
    }

    // public function getPollResults($postId)
    // {
    //     try {

    //         $post = Post::with('pollOptions')->findOrFail($postId);

    //         return response()->json($post, 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Failed to fetch poll results'], 500);
    //     }
    // }
}
