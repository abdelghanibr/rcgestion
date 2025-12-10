<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complex;
use Illuminate\Http\Request;

class ComplexController extends Controller
{
    /**
     * 📌 عرض قائمة المركبات
     */
    public function index()
    {
        $complexes = Complex::orderBy('id', 'DESC')->get();
        return view('admin.complexes.index', compact('complexes'));
    }

    /**
     * 📌 عرض صفحة إضافة مركب جديد
     */
    public function create()
    {
        return view('admin.complexes.create');
    }

    /**
     * 📌 حفظ مركب جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'capacite' => 'nullable|numeric'
        ]);

        Complex::create($request->all());

        return redirect()->route('complexes.index')
            ->with('success', '✔ تم إضافة المركب بنجاح');
    }

    /**
     * 📌 عرض صفحة تعديل مركب
     */
    public function edit($id)
    {
        $complex = Complex::findOrFail($id);
        return view('admin.complexes.edit', compact('complex'));
    }

    /**
     * 📌 تحديث بيانات مركب
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'capacite' => 'nullable|numeric'
        ]);

        $complex = Complex::findOrFail($id);
        $complex->update($request->all());

        return redirect()->route('admin.complexes.index')
            ->with('success', '✔ تم تحديث بيانات المركب بنجاح');
    }

    /**
     * 📌 حذف مركب
     */
    public function destroy($id)
    {
        Complex::findOrFail($id)->delete();

        return redirect()->route('admin.complexes.index')
            ->with('success', '🗑 تم حذف المركب بنجاح');
    }
}
