<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Support\Facades\Auth;

class ClubController extends Controller
{
    public function index()
    {
        $clubs = Club::with('user')->orderByDesc('id')->get();
        return view('admin.clubs.index', compact('clubs'));
    }

    public function approve($id)
    {
        $club = Club::findOrFail($id);
        $club->update([
            'etat' => 'approved',
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);

        return back()->with('success','تم قبول النادي ✔');
    }

    public function reject($id)
    {
        $club = Club::findOrFail($id);
        $club->update([
            'etat' => 'rejected',
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);

        return back()->with('error','تم رفض النادي ❌');
    }
}
