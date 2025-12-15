<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dossier;

class DossierController extends Controller
{
    public function upload()
    {
        return view('dossier.upload');
    }
public function updateNote(Request $request, Dossier $dossier)
{
    $request->validate([
        'note_admin' => 'required|string|max:1000',
    ]);

    $dossier->update([
        'note_admin' => $request->note_admin,
    ]);

    return back()->with('success', '✔ تم حفظ الملاحظة بنجاح');
}


   public function store(Request $request)
{
    $request->validate([
        'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096'
    ]);

    if (!$request->hasFile('document')) {
        return back()->with('error', 'لم يتم اختيار أي ملف.');
    }

    $file = $request->file('document');
    $filename = time() . '_' . $file->getClientOriginalName();

    // ✔ رفع الملف فعليًا
    $file->move(public_path('uploads/dossiers'), $filename);

    // ✔ تحديث أو إنشاء الدوسيي (المشكل كان هنا)
    $dossier = Dossier::updateOrCreate(
        ['user_id' => auth()->id()],
        [
            'fichier' => $filename,
            'etat' => 'pending'
        ]
    );

    return back()->with('success', '✔ تم رفع الملف وتم تسجيله في قاعدة البيانات.');
}

public function index(Request $request)
{
    $query = Dossier::query();

    if ($request->has('owner_type') && $request->owner_type !== 'all') {
        $query->where('owner_type', $request->owner_type);
    }

    if ($request->has('etat') && $request->etat !== 'all') {
        $query->where('etat', $request->etat);
    }
$dossiers = Dossier::with('person')->get();

$dossiers->each(function ($d) {
    $d->age = $d->person && $d->person->birth_date
        ? \Carbon\Carbon::parse($d->person->birth_date)->age
        : null;
});

    $dossiers = $query->latest()->paginate(10);

    return view('admin.dossiers.index', compact('dossiers'));
}

    public function status()
    {
        $dossier = Dossier::where('user_id', auth()->id())->firstOrFail();
        return view('dossier.status', compact('dossier'));
    }

    public function admin()
    {
        $dossiers = Dossier::with('user')->get();
        return view('dossier.admin', compact('dossiers'));
    }

 public function approve($id)
{
    $d = Dossier::findOrFail($id);
    $d->etat = 'approved';
    $d->save();

    return back()->with('success', 'تم قبول الملف بنجاح ✔');
}

public function reject($id)
{
    $d = Dossier::findOrFail($id);
    $d->etat = 'rejected';
    $d->save();

    return back()->with('error', 'تم رفض الملف ❌');
}
public function person()
{
    return $this->belongsTo(Person::class);
}
public function user()
{
    return $this->belongsTo(User::class);
}

}
