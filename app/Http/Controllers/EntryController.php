<?php

namespace App\Http\Controllers;
use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class EntryController extends Controller
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

    public function destroy($id)
    {
        $entry = Entry::findOrFail($id);
        Entry::destroy($id);
        return response() -> json(['message' => 'Delete successfully']);
    }

    // 計算每月記錄了幾天的心情 & 每月心情統計圖

    public function monthlyStats(){
        // 使用子查詢來正確計算每月的記錄天數
        $entries = DB::table('entries')
        ->select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
            DB::raw("mood"),
            DB::raw("COUNT(*) as count")
        )
        ->groupBy('month', 'mood')
        ->orderBy('month', 'desc')
        ->get();

        // 單獨計算每月的記錄天數
        $monthlyDays = DB::table('entries')
        ->select(
            DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
            DB::raw("COUNT(DISTINCT date) as days_with_entries")
        )
        ->groupBy('month')
        ->get()
        ->keyBy('month');

        // 組合數據
        $grouped = $entries->groupBy('month')->map(function($monthEntries, $month) use ($monthlyDays) {
        return [
            'days_with_entries' => $monthlyDays[$month]->days_with_entries ?? 0,
            'mood_counts' => $monthEntries->mapWithKeys(function ($entry) {
                return [$entry->mood => $entry->count];
            }),
        ];
    });

    return response()->json($grouped);
    }
}
