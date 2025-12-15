<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DossierController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ComplexeController;
use App\Http\Controllers\Admin\ComplexController;
use App\Http\Controllers\Admin\ActivitysController;
use App\Http\Controllers\Admin\PricingsPlanController ;
use App\Http\Controllers\Admin\capacityController ;
use App\Http\Controllers\Admin\ScheduleController ;
use App\Http\Controllers\Admin\AgeCategoryController;


use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\ClubAuthController;
use App\Http\Controllers\Auth\CompanyAuthController;
use App\Http\Controllers\Auth\PersonAuthController;

use App\Http\Controllers\PersonController;
use App\Http\Controllers\ClubController; 

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;

 use App\Http\Controllers\PricingPlanController ;
/*
|--------------------------------------------------------------------------
| PAGE D'ACCUEIL PUBLIQUE (SANS AUTH)
|--------------------------------------------------------------------------
*/



// صفحة تأكيد إعادة تعيين كلمة المرور
Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/', function () {
    return view('welcome');
})->name('home');


/*
|--------------------------------------------------------------------------
| AUTHENTIFICATION
|--------------------------------------------------------------------------
*/
// Admin
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
Route::get('/admin/dashboard', fn()=>view('admin.dashboard'))->middleware('auth')->name('admin.dashboard');

// Club
Route::get('/club/login', [ClubAuthController::class, 'showLogin'])->name('club.login');
Route::post('/club/login', [ClubAuthController::class, 'login'])->name('club.login.post');
Route::get('/club/register', [ClubAuthController::class, 'showRegister'])->name('club.register');
Route::post('/club/register', [ClubAuthController::class, 'register'])->name('club.register.post');
Route::get('/club/dashboard', fn()=>view('club.dashboard'))->middleware('auth')->name('club.dashboard');

// Company
Route::get('/entreprise/login', [CompanyAuthController::class, 'showLogin'])->name('entreprise.login');
Route::post('/entreprise/login', [CompanyAuthController::class, 'login'])->name('entreprise.login.post');
Route::get('/entreprise/register', [CompanyAuthController::class, 'showRegister'])->name('entreprise.register');
Route::post('/entreprise/register', [CompanyAuthController::class, 'register'])->name('entreprise.register.post');
Route::get('/entreprise/dashboard', fn()=>view('entreprise.dashboard'))->middleware('auth')->name('entreprise.dashboard');

// Person
Route::get('/person/login', [PersonAuthController::class, 'showLogin'])->name('person.login');
Route::post('/person/login', [PersonAuthController::class, 'login'])->name('person.login.post');
Route::get('/person/register', [PersonAuthController::class, 'showRegister'])->name('person.register');
Route::post('/person/register', [PersonAuthController::class, 'register'])->name('person.register.post');
Route::get('/person/dashboard', fn()=>view('person.dashboard'))->middleware('auth')->name('person.dashboard');

// Logout Person
Route::post('/person/logout', function () {
    Auth::logout();
    return redirect()->route('person.login');
})->name('person.logout');

// Logout Club
Route::post('/club/logout', function () {
    Auth::logout();
    return redirect()->route('club.login');
})->name('club.logout');

// Logout Company
Route::post('/entreprise/logout', function () {
    Auth::logout();
    return redirect()->route('entreprise.login');
})->name('entreprise.logout');

// Logout Admin
Route::post('/admin/logout', function () {
    Auth::logout();
    return redirect()->route('admin.login');
})->name('admin.logout');

/*
|--------------------------------------------------------------------------
| MOT DE PASSE OUBLIE / RESET
|--------------------------------------------------------------------------
*/
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request');

Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email');

Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/password/reset', [ResetPasswordController::class, 'reset'])
    ->name('password.update');









// ⭐ Dashboard Person
Route::middleware(['auth','person'])->group(function () {
    Route::get('/person/dashboard', [DashboardController::class, 'index'])->name('person.dashboard');

Route::get('/person/profile/edit', [RegisterController::class, 'edit'])->name('person.profile.edit');

 Route::put('/person/profile/update', [RegisterController::class, 'update'])
        ->name('person.profile.update');
});

// ⭐ Dashboard Club


// ⭐ Dashboard Club
Route::middleware(['auth','club'])->group(function () {
    Route::get('/club/dashboard', [DashboardController::class, 'index'])->name('club.dashboard');
  
    Route::get('/club/persons/', [PersonController::class, 'index'])
        ->name('club.persons.index');

   Route::get('/club/persons/edit/{id}', [PersonController::class, 'edit'])
        ->name('club.persons.edit');

    Route::post('/club/persons/update/{id}', [PersonController::class, 'update'])
        ->name('club.persons.update');

    Route::delete('/club/persons/delete/{id}', [PersonController::class, 'destroy'])
        ->name('club.persons.delete');

       
   Route::post('/club/persons/store', [PersonController::class, 'store'])
            ->name('club.persons.store');
        
    Route::get('/club/persons/create', [PersonController::class, 'create'])
        ->name('club.persons.create');


 Route::get('/club/profile/edit', [RegisterController::class, 'edit'])->name('club.profile.edit');

});

// ⭐ Dashboard Entreprise
Route::middleware(['auth','entreprise'])->group(function () {
    Route::get('/entreprise/dashboard', [DashboardController::class, 'index'])->name('entreprise.dashboard');
    
    
    Route::get('/entreprise/persons/{type}', [PersonController::class, 'index'])
        ->name('entreprise.persons.index');

    Route::get('/entreprise/persons/edit/{id}', [PersonController::class, 'edit'])
        ->name('entreprise.persons.edit');

    // 📌 تحديث
    Route::post('/entreprise/persons/update/{id}', [PersonController::class, 'update'])
        ->name('entreprise.persons.update');

    // 📌 حذف
    Route::delete('/entreprise/persons/delete/{id}', [PersonController::class, 'destroy'])
        ->name('entreprise.persons.delete');     

        Route::get('/entreprise/profile/edit', [RegisterController::class, 'edit'])->name('entreprise.profile.edit');
            Route::put('/entreprise/profile/update', [RegisterController::class, 'update'])
        ->name('entreprise.profile.update');
});


// ⭐ Dashboard Admin

Route::middleware(['auth','admin'])->group(function () {


Route::get('/admin/profile/edit', [RegisterController::class, 'edit'] )->name('admin.profile.edit');

  Route::put('/admin/profile/update', [RegisterController::class, 'update'])->name('admin.profile.update');


  





    Route::get('admins', [AdminController::class, 'adminsIndex'])->name('admins.index');
    Route::get('admins/create', [AdminController::class, 'adminsCreate'])->name('admins.create');
    Route::post('admins/store', [AdminController::class, 'adminsStore'])->name('admins.store');
    Route::get('admins/edit/{id}', [AdminController::class, 'adminsEdit'])->name('admins.edit');
    Route::post('admins/update/{id}', [AdminController::class, 'adminsUpdate'])->name('admins.update');
    Route::delete('admins/delete/{id}', [AdminController::class, 'adminsDelete'])->name('admins.delete');
   
    Route::get('/admin/dashboard', fn()=>view('admin.dashboard'))->name('admin.dashboard');

    //dossier et validation
    Route::get('/admin/dossiers', [DossierController::class, 'index'])->name('admin.dossiers.index');
    Route::get('/admin/dossiers/{id}/approve', [DossierController::class, 'approve'])->name('admin.dossiers.approve');
    Route::get('/admin/dossiers/{id}/reject', [DossierController::class, 'reject'])->name('admin.dossiers.reject');

// club et validation 
 
    
    Route::get('/admin/clubs', [ClubController::class, 'index'])
        ->name('admin.clubs.index');

    Route::get('/admin/clubs/{id}/approve', [ClubController::class, 'approve'])
        ->name('admin.clubs.approve');

    Route::get('/admin/clubs/{id}/reject', [ClubController::class, 'reject'])
        ->name('admin.clubs.reject');

//activite et complex et pricing pla 

// gestion des activités
    Route::get('/admin/activities', [ActivitysController::class, 'index'])->name('admin.activities.index');
    Route::get('/admin/activities/create', [ActivitysController::class, 'create'])->name('admin.activities.create');
    Route::post('/admin/activities', [ActivitysController::class, 'store'])->name('admin.activities.store');
    Route::get('/admin/activities/{id}/edit', [ActivitysController::class, 'edit'])->name('admin.activities.edit');
    Route::put('/admin/activities/{id}', [ActivitysController::class, 'update'])->name('admin.activities.update');
    Route::delete('/admin/activities/{id}', [ActivitysController::class, 'destroy'])->name('admin.activities.destroy');


// gestion des horaires (schedules)
Route::get('/admin/schedules', [ScheduleController::class, 'index'])->name('admin.schedules.index');
Route::get('/admin/schedules/create', [ScheduleController::class, 'create'])->name('admin.schedules.create');
Route::post('/admin/schedules', [ScheduleController::class, 'store'])->name('admin.schedules.store');
Route::get('/admin/schedules/{id}/edit', [ScheduleController::class, 'edit'])->name('admin.schedules.edit');
Route::put('/admin/schedules/{id}', [ScheduleController::class, 'update'])->name('admin.schedules.update');
Route::delete('/admin/schedules/{id}', [ScheduleController::class, 'destroy'])->name('admin.schedules.destroy');

Route::get('/admin/get-complex-activity', function (Request $request) {
    $ca = \App\Models\ComplexActivity::where('complex_id', $request->complex_id)
        ->where('activity_id', $request->activity_id)
        ->first();

    return response()->json([
        'id' => $ca ? $ca->id : null
    ]);
})->name('admin.getComplexActivity');

// gestion des capacités
// Capacities Management
Route::get('/admin/capacities', [CapacityController::class, 'index'])
    ->name('admin.capacities.index');

Route::get('/admin/capacities/create', [CapacityController::class, 'create'])
    ->name('admin.capacities.create');

Route::post('/admin/capacities', [CapacityController::class, 'store'])
    ->name('admin.capacities.store');

Route::get('/admin/capacities/{id}/edit', [CapacityController::class, 'edit'])
    ->name('admin.capacities.edit');

Route::put('/admin/capacities/{id}', [CapacityController::class, 'update'])
    ->name('admin.capacities.update');

Route::delete('/admin/capacities/{id}', [CapacityController::class, 'destroy'])
    ->name('admin.capacities.destroy');




// gestion des complexes
   Route::get('/admin/complexes', [ComplexeController::class, 'index'])
        ->name('admin.complexes.index');
    Route::get('/admin/complexes/create', [ComplexController::class, 'create'])
        ->name('admin.complexes.create');


    Route::post('/admin/complexes', [ComplexController::class, 'store'])
        ->name('admin.complexes.store');

    // تعديل مركب
    Route::get('/admin/complexes/{id}/edit', [ComplexController::class, 'edit'])
        ->name('admin.complexes.edit');

    Route::put('/admin/complexes/{id}', [ComplexController::class, 'update'])
        ->name('admin.complexes.update');

    // حذف مركب
    Route::delete('/admin/complexes/{id}', [ComplexController::class, 'destroy'])
        ->name('admin.complexes.destroy');

// tableau de bord admin

    Route::get('/admin/pricing', [PricingsPlanController::class, 'index'])->name('admin.pricing_plans.index');
    Route::get('/admin/pricing/create', [PricingsPlanController::class, 'create'])->name('admin.pricing_plans.create');
    Route::post('/admin/pricing', [PricingsPlanController::class, 'store'])->name('admin.pricing_plans.store');
    Route::get('/admin/pricing/{id}/edit', [PricingsPlanController::class, 'edit'])->name('admin.pricing_plans.edit');
    Route::put('/admin/pricing/{id}', [PricingsPlanController::class, 'update'])->name('admin.pricing_plans.update');
    Route::delete('/admin/pricing/{id}', [PricingsPlanController::class, 'destroy'])->name('admin.pricing_plans.destroy');
// mise à jour ملاحظة dossier

Route::post(
    'admin/dossiers/{dossier}/note',
    [DossierController::class, 'updateNote']
)->name('admin.dossiers.note');


Route::resource('/admin/age-categories', AgeCategoryController::class);
 Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
    ->middleware('auth')
    ->name('admin.dashboard');

});




/*
|--------------------------------------------------------------------------
| ESPACE SECURISE (NECESSITE LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::get('/profile/new', [ProfileController::class, 'newPerson'])
    ->name('profile.new');

Route::get('/profile/step/{step}', [ProfileController::class, 'showStep'])
        ->name('profile.step');
 Route::post('/profile/step/{step}', [ProfileController::class, 'saveStep'])
        ->name('profile.step.save');


 Route::get('/person/{person}/edit/step/{step}', [ProfileController::class, 'editStep'])
    ->name('profile.editStep');

Route::post('/person/{person}/edit/step/{step}', [ProfileController::class, 'saveEditStep'])
    ->name('profile.editStep.save');       



Route::get('/activities', function () {
    return view('activities.index');
})->name('activities');

  
    // Tableau de bord
 Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware('auth');

    /*
    |--------------------------------------------------------------------------
    | DOSSIERS
    |--------------------------------------------------------------------------
    */
   




Route::get('/activities/create', [ActivityController::class, 'create'])->name('activities.create');
Route::post('/activities/store', [ActivityController::class, 'store'])->name('activities.store');

// يجب أن تكون تحت المسارات فوق 👇

Route::get('/activities/register/{id}', [ActivityController::class, 'register'])->name('activities.register');
Route::get('/my-activities', [ActivityController::class, 'myActivities'])->name('my.activities');

// ⚠️ تعديل/حذف تكون دائمًا آخر شيء
Route::get('/activities/{id}/edit', [ActivityController::class, 'edit'])->name('activities.edit');
Route::put('/activities/{id}', [ActivityController::class, 'update'])->name('activities.update');
Route::delete('/activities/{id}', [ActivityController::class, 'destroy'])->name('activities.destroy');





    // تسجيل في نشاط معين

    Route::get('/activities', [App\Http\Controllers\ActivityController::class, 'index'])
        ->name('activities.index');

        Route::get('/activities/{id}/complexes',
    [App\Http\Controllers\ActivityController::class, 'complexes'])
    ->name('activities.complexes');






    // صفحة أنشطتي
  
   Route::get('/complexes', [ComplexeController::class, 'index'])
        ->name('complexes.index');

    Route::post('/complexes', [ComplexeController::class, 'store'])
        ->name('complexes.store');

    Route::put('/complexes/{id}', [ComplexeController::class, 'update'])
        ->name('complexes.update');

    Route::delete('/complexes/{id}', [ComplexeController::class, 'destroy'])
        ->name('complexes.destroy');
    /*
    |--------------------------------------------------------------------------
    | RESERVATIONS
    |--------------------------------------------------------------------------
    */


    Route::get('/my-reservations', [ReservationController::class, 'index'])
        ->name('reservation.my-reservations');

    Route::get('/reservations/create', [ReservationController::class, 'create'])
        ->name('reservation.create');

    Route::get('/reservations/{id}/renew', [ReservationController::class, 'renew'])
        ->name('reservation.renew');

        Route::delete('/reservations/{reservation}', 
    [ReservationController::class, 'destroy']
)->name('reservations.destroy');


        Route::post(
    '/reservations/{reservation}/renew',
    [ReservationController::class, 'renewStore']
)->name('reservations.renew.store');







Route::get('/my-activities', function () {
        return view('activities.my');
    })->name('my.activities');
    // Étape 1 - Choisir type
    Route::get('/reservations/select-type', [ReservationController::class, 'selectType'])
        ->name('reservation.select_type');

    // Étape 2 - Liste des complexes selon type
    Route::get('/reservations/list/{type}', [ReservationController::class, 'listByType'])
        ->name('reservation.list_by_type');

    // Étape 3 - Formulaire
    Route::get('/reservations/form/{id}', [ReservationController::class, 'form'])
        ->name('reservation.form');

    // Étape 4 - Enregistrer
    Route::post('/reservations/store', [ReservationController::class, 'store'])
        ->name('reservation.store');

    // Paiements
    Route::get('/reservations/payment/{id}', [PaymentController::class, 'paymentPage'])
        ->name('reservation.payment');

    Route::get('/reservations/pay-cash/{id}', [PaymentController::class, 'payCash'])
        ->name('reservation.pay_cash');

    Route::get('/reservations/pay-online/{id}', [PaymentController::class, 'payOnline'])
        ->name('reservation.pay_online');


});
