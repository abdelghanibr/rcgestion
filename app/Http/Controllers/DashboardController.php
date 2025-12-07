<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Person;
use App\Models\Dossier;
use App\Models\club;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();



  $ownerType = $user->type; // person / club / entreprise

    // 👤 الحالة 1: شخص
   
    // حالة غير متوقعة!
   

        /* ---------------------------------
        | 📌 Dashboard النادي
        --------------------------------- */
        if ($user->type === 'club') {

            $clubOwner = $user->id;

            $dossier =  Club::where('user_id', $user->id)->first();

            $playersCount = Person::where('user_id', $clubOwner)
                                ->where('education', 'لاعب')
                                ->count();

            $coachsCount = Person::where('user_id', $clubOwner)
                                ->where('education', 'مدرب')
                                ->count();

            $managersCount = Person::where('user_id', $clubOwner)
                                ->where('education', 'مسير')
                                ->count();

            return view('club.dashboard', [
            'user' => $user,
            'playersCount' => $playersCount,
            'coachsCount' => $coachsCount,
            'managersCount' => $managersCount ,
             'dossier' =>$dossier
        ]);
        }


        /* ---------------------------------
        | 📌 Dashboard المؤسسة
        --------------------------------- */
        if ($user->type === 'entreprise') {

            $enterpriseOwner = $user->id;
 $dossier =  Club::where('user_id', $user->id)->first();
            $playersCount = Person::where('user_id', $enterpriseOwner)
                                ->where('education', 'لاعب')
                                ->count();

            $coachsCount = Person::where('user_id', $enterpriseOwner)
                                ->where('education', 'مدرب')
                                ->count();

            $managersCount = Person::where('user_id', $enterpriseOwner)
                                ->where('education', 'مسير')
                                ->count();

            return view('entreprise.dashboard', compact(
                'playersCount',
                'coachsCount',
                'managersCount','dossier'
            ));
        }


        /* ---------------------------------
        | 📌 Dashboard الشخص
        --------------------------------- */
     $person = \App\Models\Person::where('user_id', $user->id)->first();
    
    if ($person) {
        $dossier = \App\Models\Dossier::where('owner_type', 'person')
                                      ->where('person_id', $person->id)
                                      ->first();}

        $registeredActivities = DB::table('reservations')
                                ->where('user_id', $user->id)
                                ->count();

        return view('person.dashboard', compact(
            'user',
            'dossier',
            'registeredActivities'
        ));
    }

    public function dashboard()
{
    return view('admin.dashboard', [
        'dossiersCount' => Dossier::count(),
        'clubsCount' => Club::count(),
        'personsCount' => Person::count()
    ]);
}
}
