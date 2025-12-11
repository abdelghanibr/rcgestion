<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplexActivity as Capacity;
use App\Models\Complex;
use App\Models\Activity;
use App\Models\Season;
use Illuminate\Http\Request;

class CapacityController extends Controller
{
    public function index()
    {
        $capacities = Capacity::with(['complex', 'activity'])->get();
        return view('admin.capacities.index', compact('capacities'));
    }

    public function create()
    {
        $complexes = Complex::all();
        $activities = Activity::all();
        $seasons = Season::all();

        return view('admin.capacities.create', compact('complexes', 'activities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'complex_id' => 'required',
            'activity_id' => 'required',
          //  'season_id' => 'required',
            'capacity' => 'required|integer|min:0',
        ]);

        Capacity::create($request->all());

        return redirect()->route('admin.capacities.index')->with('success', 'تم إضافة السعة بنجاح');
    }

    public function edit($id)
    {
        $capacity = Capacity::findOrFail($id);
        $complexes = Complex::all();
        $activities = Activity::all();
        $seasons = Season::all();

        return view('admin.capacities.edit', compact('capacity', 'complexes', 'activities'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'complex_id' => 'required',
            'activity_id' => 'required',
         //   'season_id' => 'required',
            'capacity' => 'required|integer|min:0',
        ]);

        $capacity = Capacity::findOrFail($id);
        $capacity->update($request->all());

        return redirect()->route('admin.capacities.index')->with('success', 'تم تحديث السعة بنجاح');
    }

    public function destroy($id)
    {
        Capacity::findOrFail($id)->delete();
        return redirect()->route('admin.capacities.index')->with('success', 'تم الحذف بنجاح');
    }
}
