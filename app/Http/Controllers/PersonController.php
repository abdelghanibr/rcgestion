<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PersonController extends Controller
{
    /* =====================================================
       📄 INDEX
    ===================================================== */
    public function index()
    {
        $user = Auth::user();

        $views = [
            'club'     => 'club.persons.index',
            'company'  => 'entreprise.persons.index',
            'person'   => 'person.profile',
        ];

        if (!isset($views[$user->type])) {
            abort(403, 'Unauthorized');
        }

        $persons = Person::where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        return view($views[$user->type], compact('persons'));
    }

    /* =====================================================
       ➕ CREATE
    ===================================================== */
    public function create()
    {
        $user = Auth::user();

        return match ($user->type) {
            'club'    => view('club.persons.create'),
            'company' => view('entreprise.persons.create'),
            default   => abort(403, 'نوع المستخدم غير مدعوم'),
        };
    }

    /* =====================================================
       💾 STORE
    ===================================================== */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname'  => 'required|string|max:100',
            'lastname'   => 'required|string|max:100',
            'birth_date' => 'required|date',
            'gender'     => 'required|in:ذكر,أنثى',
            'education'  => 'required|string|max:50',
            'photo'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        /* ===== Upload Photo (Local / Production) ===== */
        $photoPath = null;

        if ($request->hasFile('photo')) {

            if (app()->environment('local')) {
                $storagePath = storage_path('app/public');
                $storageUrl  = '/storage';
            } else {
                $storagePath = rtrim(env('PUBLIC_STORAGE_PATH'), '/');
                $storageUrl  = rtrim(env('PUBLIC_STORAGE_URL'), '/');
            }

            $directory = $storagePath . '/photos/persons';

            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $file     = $request->file('photo');
            $filename = uniqid('person_') . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);

            $photoPath = $storageUrl . '/photos/persons/' . $filename;
        }

        /* ===== Create Person ===== */
        $person = new Person();
        $person->user_id    = $user->id;
        $person->firstname  = $validated['firstname'];
        $person->lastname   = $validated['lastname'];
        $person->birth_date = $validated['birth_date'];
        $person->gender     = $validated['gender'];
        $person->education  = $validated['education'];
        $person->photo      = $photoPath;

        /* ===== Link to Club (Club or Company) ===== */
        if ($user->type === 'club') {

            if (!$user->club) {
                abort(403, 'لا يوجد نادي مرتبط بالحساب');
            }

            $person->club_id = $user->club->id;

        } elseif ($user->type === 'company') {

            $club = Club::where('user_id', $user->id)->first();

            if (!$club) {
                abort(403, 'لا توجد مؤسسة مرتبطة بالحساب');
            }

            $person->club_id = $club->id;

        } else {
            abort(403, 'نوع المستخدم غير مدعوم');
        }

        $person->save();

        return redirect()
            ->route($user->type === 'club'
                ? 'club.persons.index'
                : 'entreprise.persons.index'
            )
            ->with('success', '✅ تم الحفظ بنجاح');
    }

    /* =====================================================
       ✏️ EDIT
    ===================================================== */
    public function edit($id)
    {
        $user   = Auth::user();
        $person = Person::findOrFail($id);

        if ($person->user_id !== $user->id) {
            abort(403);
        }

        return match ($user->type) {
            'club'    => view('club.persons.edit', compact('person')),
            'company' => view('entreprise.persons.edit', compact('person')),
            default   => abort(403),
        };
    }

    /* =====================================================
       🔄 UPDATE
    ===================================================== */
    public function update(Request $request, $id)
    {
        $user   = Auth::user();
        $person = Person::findOrFail($id);

        if ($person->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'firstname'  => 'required|string|max:50',
            'lastname'   => 'required|string|max:50',
            'birth_date' => 'required|date',
            'gender'     => 'required',
            'education'  => 'required',
        ]);

        $person->update($request->only([
            'firstname',
            'lastname',
            'birth_date',
            'gender',
            'education'
        ]));

        return redirect()
            ->route($user->type === 'club'
                ? 'club.persons.index'
                : 'entreprise.persons.index'
            )
            ->with('success', '✔ تم تحديث البيانات بنجاح');
    }

    /* =====================================================
       🗑 DELETE
    ===================================================== */
    public function destroy($id)
    {
        $user   = Auth::user();
        $person = Person::findOrFail($id);

        if ($person->user_id !== $user->id) {
            abort(403);
        }

        $person->delete();

        return back()->with('success', '❌ تم حذف المستخدم بنجاح');
    }
}
