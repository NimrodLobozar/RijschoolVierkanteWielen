<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\AutoController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LessonsController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\InstructorLessonController;
use App\Http\Controllers\StudentLessonController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    $isMaintenanceMode = DB::table('settings')->where('key', 'maintenance_mode')->value('value') ?? false;
    return view('welcome', compact('isMaintenanceMode'));
})->name('/');

Route::get('/dashboard', function () {
    $isMaintenanceMode = DB::table('settings')->where('key', 'maintenance_mode')->value('value') ?? false;
    return view('dashboard', compact('isMaintenanceMode'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

use App\Http\Middleware\AdminMiddleware;

Route::middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::patch('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

    Route::get('/instructors', [InstructorController::class, 'index'])->name('instructors.index');
    Route::get('/instructors/create', [InstructorController::class, 'create'])->name('instructors.create');
    Route::post('/instructors', [InstructorController::class, 'store'])->name('instructors.store');
    Route::get('/instructors/{instructor}', [InstructorController::class, 'show'])->name('instructors.show');
    Route::get('/instructors/{instructor}/edit', [InstructorController::class, 'edit'])->name('instructors.edit');
    Route::patch('/instructors/{instructor}', [InstructorController::class, 'update'])->name('instructors.update');
    Route::delete('/instructors/{instructor}', [InstructorController::class, 'destroy'])->name('instructors.destroy');

    Route::get('/autos', [AutoController::class, 'index'])->name('autos.index');
    Route::get('/autos/create', [AutoController::class, 'create'])->name('autos.create');
    Route::post('/autos', [AutoController::class, 'store'])->name('autos.store');

    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/create', [PackageController::class, 'create'])->name('packages.create');
    Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');


    Route::get('/betalingen', [PaymentController::class, 'index'])->name('betalingen.index');
    Route::get('/betalingen/create', [PaymentController::class, 'create'])->name('betalingen.create');
    Route::post('/betalingen', [PaymentController::class, 'store'])->name('betalingen.store');


    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{accounts}', [AccountController::class, 'show'])->name('accounts.show');
    Route::get('/accounts/{accounts}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
    Route::patch('/accounts/{accounts}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{accounts}', [AccountController::class, 'destroy'])->name('accounts.destroy');

    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::patch('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::get('/invoices/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.markAsPaid');
    Route::get('/invoices/{invoice}/mark-as-unpaid', [InvoiceController::class, 'markAsUnpaid'])->name('invoices.markAsUnpaid');

    Route::resource('lessons', LessonsController::class)->except(['show']);
    Route::resource('registrations', RegistrationController::class);
});

// Instructeur: eigen lesrooster en lesaanvragen
Route::middleware(['auth', 'role:Instructeur'])->prefix('instructeur')->name('instructor.')->group(function () {
    Route::get('/lessen', [InstructorLessonController::class, 'index'])->name('lessons.index');
    Route::post('/lessen/{lesson}/accepteren', [InstructorLessonController::class, 'accept'])->name('lessons.accept');
    Route::get('/lessen/{lesson}/bewerken', [InstructorLessonController::class, 'edit'])->name('lessons.edit');
    Route::patch('/lessen/{lesson}', [InstructorLessonController::class, 'update'])->name('lessons.update');
});

// Leerling: eigen lessen bekijken, aanvragen en annuleren
Route::middleware(['auth', 'role:Leerling'])->prefix('leerling')->name('student.')->group(function () {
    Route::get('/lessen', [StudentLessonController::class, 'index'])->name('lessons.index');
    Route::get('/lessen/aanvragen', [StudentLessonController::class, 'create'])->name('lessons.create');
    Route::post('/lessen', [StudentLessonController::class, 'store'])->name('lessons.store');
    Route::patch('/lessen/{lesson}/annuleren', [StudentLessonController::class, 'cancel'])->name('lessons.cancel');
    Route::patch('/lessen/{lesson}/opmerking', [StudentLessonController::class, 'comment'])->name('lessons.comment');
});

Route::post('/toggle-maintenance', [MaintenanceController::class, 'toggle'])->name('toggle.maintenance');

require __DIR__ . '/auth.php';
