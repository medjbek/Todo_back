<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
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

        $todo->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_completed' => $validated['is_completed'],
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

        $todo->delete();

        return response()->json([
            'message' => 'Todo supprimée avec succès'
        ]);
    }
}