<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $todos = Todo::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'todos' => $todos
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $todo = Todo::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_completed' => false,
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'todo_id' => $todo->id,
            'action' => 'todo_created',
            'details' => [
                'title' => $todo->title,
                'is_completed' => $todo->is_completed,
            ],
        ]);

        return response()->json([
            'message' => 'Todo créée avec succès',
            'todo' => $todo
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $todo = Todo::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_completed' => ['required', 'boolean'],
        ]);

        $oldData = [
            'title' => $todo->title,
            'description' => $todo->description,
            'is_completed' => $todo->is_completed,
        ];

        $todo->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_completed' => $validated['is_completed'],
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'todo_id' => $todo->id,
            'action' => 'todo_updated',
            'details' => [
                'before' => $oldData,
                'after' => [
                    'title' => $todo->title,
                    'description' => $todo->description,
                    'is_completed' => $todo->is_completed,
                ],
            ],
        ]);

        return response()->json([
            'message' => 'Todo mise à jour avec succès',
            'todo' => $todo
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $todo = Todo::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $details = [
            'title' => $todo->title,
            'description' => $todo->description,
            'is_completed' => $todo->is_completed,
        ];

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'todo_id' => $todo->id,
            'action' => 'todo_deleted',
            'details' => $details,
        ]);

        $todo->delete();

        return response()->json([
            'message' => 'Todo supprimée avec succès'
        ]);
    }
}