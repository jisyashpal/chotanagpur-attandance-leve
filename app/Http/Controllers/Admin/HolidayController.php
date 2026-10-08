<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $monthParam = $request->input('month', Carbon::now()->format('Y-m'));
        
        try {
            $currentDate = Carbon::createFromFormat('Y-m-d', $monthParam . '-01');
        } catch (\Exception $e) {
            $currentDate = Carbon::now()->startOfMonth();
            $monthParam = $currentDate->format('Y-m');
        }

        $year = $currentDate->year;
        $month = $currentDate->month;

        // Fetch holidays for the selected month
        $holidays = Holiday::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        // Fetch top 5 upcoming holidays
        $upcomingHolidays = Holiday::where('date', '>=', Carbon::today()->toDateString())
            ->orderBy('date')
            ->take(5)
            ->get();

        return view('admin.holidays.index', compact(
            'holidays',
            'upcomingHolidays',
            'monthParam',
            'currentDate'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date',
            'description' => 'nullable|string|max:1000',
        ], [
            'date.unique' => 'A holiday is already scheduled on this date.',
        ]);

        Holiday::create([
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Holiday added successfully.');
    }

    public function update(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date,' . $id,
            'description' => 'nullable|string|max:1000',
        ], [
            'date.unique' => 'A holiday is already scheduled on this date.',
        ]);

        $holiday->update([
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Holiday updated successfully.');
    }

    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        return redirect()->back()->with('success', 'Holiday deleted successfully.');
    }
}
