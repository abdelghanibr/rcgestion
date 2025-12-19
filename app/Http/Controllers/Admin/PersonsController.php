<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\AgeCategory;
use App\Models\Club;
use Illuminate\Http\Request;

class PersonsController extends Controller
{
    /* ===================== INDEX ===================== */
    public function index()
    {
        $persons = Person::with(['ageCategory', 'club', 'user'])
            ->orderByDesc('id')
            ->get();

        return view('admin.persons.index', compact('persons'));
    }

    /* ===================== CREATE ===================== */
    public function create()
    {
        $ageCategories = AgeCategory::orderBy('name')->get();
        $clubs         = Club::orderBy('name')->get();

        return view('admin.persons.create', compact(
            'ageCategories',
            'clubs'
        ));
    }

    /* ===================== STORE ===================== */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname'        => 'required|string|max:255',
            'lastname'         => 'required|string|max:255',
            'gender'           => 'nullable|string|max:20',
            'birth_date'       => 'nullable|date',
            'age_category_id'  => 'nullable|exists:age_categories,id',
            'club_id'          => 'nullable|exists:clubs,id',
            'phone'            => 'nullable|string|max:20',
            'wilaya'           => 'nullable|string|max:100',
            'handicap'         => 'nullable|boolean',
        ]);

        Person::create($validated);

        return redirect()
            ->route('persons.index')
            ->with('success', '✅ تم إضافة الشخص بنجاح');
    }

    /* ===================== EDIT ===================== */
    public function edit(Person $person)
    {
        $ageCategories = AgeCategory::orderBy('name')->get();
        $clubs         = Club::orderBy('name')->get();

        return view('admin.persons.edit', compact(
            'person',
            'ageCategories',
            'clubs'
        ));
    }

    /* ===================== UPDATE ===================== */
    public function update(Request $request, Person $person)
    {
        $validated = $request->validate([
            'firstname'        => 'required|string|max:255',
            'lastname'         => 'required|string|max:255',
            'gender'           => 'nullable|string|max:20',
            'birth_date'       => 'nullable|date',
            'age_category_id'  => 'nullable|exists:age_categories,id',
            'club_id'          => 'nullable|exists:clubs,id',
            'phone'            => 'nullable|string|max:20',
            'wilaya'           => 'nullable|string|max:100',
            'handicap'         => 'nullable|boolean',
        ]);

        $person->update($validated);

        return redirect()
            ->route('persons.index')
            ->with('success', '✏️ تم تحديث بيانات الشخص بنجاح');
    }

    /* ===================== DESTROY (اختياري) ===================== */
    public function destroy(Person $person)
    {
        $person->delete();

        return redirect()
            ->route('persons.index')
            ->with('success', '🗑️ تم حذف الشخص');
    }
}
