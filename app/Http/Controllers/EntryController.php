<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use Illuminate\Http\Request;

class EntryController extends Controllers
{
    public function index()
    {
        return Entry::orderBy('date', 'desc')->get();

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'mood' => 'required|string',
            'note'=> 'nullable|string',
        ]);
        return Entry::create($validated);
    }

    public function show($id)
    {
        return Entry::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $entry = Entry::findOrFail($id);

        $validated = $request -> validate([
            'date' => 'required|date',
            'mood' => 'required|string',
            'note' => 'required|string',
        ]);

        $entry->update($validated);
        return $entry;
    }

    public function destory($id)
    {
        Entry::destory($id);
        return response() -> json(['message' => 'Delete successfully']);
    }
}
