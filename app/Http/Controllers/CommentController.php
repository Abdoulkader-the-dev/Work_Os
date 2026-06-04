<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentStoreRequest;
use App\Http\Requests\CommentUpdateRequest;
use App\Models\Comment;
use App\Models\Item;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function index(Request $request, Item $item)
    {
        // Check if user has access to the workspace
        $workspace = $item->group->board->workspace;

        $hasAccess = $workspace->members()
            ->where('user_id', $request->user()->id)
            ->exists();

        if (!$hasAccess) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $comments = $item->comments()->with('user')->latest()->get();

        return response()->json(['comments' => $comments], 200);
    }

    public function store(CommentStoreRequest $request)
    {
        // Authorization and validation are now handled by CommentStoreRequest

        $comment = Comment::create([
            'body' => $request->validated()['body'],
            'item_id' => $request->validated()['item_id'],
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['comment' => $comment], 201);
    }

    public function update(CommentUpdateRequest $request, Comment $comment)
    {
        // Authorization and validation are now handled by CommentUpdateRequest

        $comment->update(['body' => $request->validated()['body']]);

        return response()->json(['comment' => $comment], 200);
    }

    public function destroy(Request $request, Comment $comment)
    {
        $workspace = $request->user()->activeWorkspace;

        $isAdmin = $workspace && $workspace->members()
            ->where('user_id', $request->user()->id)
            ->where('role', 'admin')
            ->exists();

        $isAuthor = $comment->user_id === $request->user()->id;

        // Author OR admin can delete
        if (!$isAuthor && !$isAdmin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted'], 200);
    }
}
