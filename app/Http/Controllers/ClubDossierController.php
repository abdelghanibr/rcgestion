<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Club;

class ClubDossierController extends Controller
{
    // عرض الدوسيي الخاص بالنادي الحالي
    public function index()
    {
        $club = Club::where('user_id', Auth::id())->firstOrFail();
        return view('club.dossier.index', compact('club'));
    }

    // صفحة التعديل
    public function edit()
    {
        $club = Club::where('user_id', Auth::id())->firstOrFail();
        return view('club.dossier.edit', compact('club'));
    }

    // حفظ التعديلات
    public function update(Request $request)
    {
        $club = Club::where('user_id', Auth::id())->firstOrFail();

        $attachments = json_decode($club->attachments, true) ?? [];

        // رفع الملفات
        $files = [
            'agrement',
            'statut',
            'bureau_members',
            'coaches_certificates',
            'federation_affiliation',
            'insurance_certificate',
            'rules_book',
            'minutes_meeting',
            'exploitation_request'
        ];

        foreach ($files as $file) {
            if ($request->hasFile($file)) {
                $path = $request->file($file)->store('clubs/dossiers', 'public');
                $attachments[$file] = 'storage/' . $path;
            }
        }

        $club->update([
            'attachments' => json_encode($attachments),
            'etat' => 'pending', // يعاد للدراسة
        ]);

        return redirect()
            ->route('club.dossier.index')
            ->with('success', '✅ تم تحديث ملف النادي وإرساله للمراجعة');
    }
}
