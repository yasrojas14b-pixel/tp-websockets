<?php

use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\AppointmentScheduleController;
use App\Http\Controllers\Admin\AppointmentStatusController;
use App\Http\Controllers\Admin\BusinessHourController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ClientPhotoController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ScheduleBlockController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RemoteStyleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public booking (mobile-first, no login)
|--------------------------------------------------------------------------
*/

Route::get('/', [BookingController::class, 'create'])->name('booking.create');
Route::post('/reservar', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('booking.store');
Route::get('/turno/{appointment:public_code}', [BookingController::class, 'show'])->name('booking.show');
Route::get('/disponibilidad', AvailabilityController::class)->middleware('throttle:60,1')->name('availability');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/agenda', [CalendarController::class, 'index'])->name('calendar');
    Route::get('/agenda/eventos', [CalendarController::class, 'events'])->name('calendar.events');

    Route::resource('turnos', AppointmentController::class)
        ->only(['index', 'create', 'store', 'edit', 'update'])
        ->parameters(['turnos' => 'appointment'])
        ->names('appointments');
    Route::patch('/turnos/{appointment}/estado', [AppointmentStatusController::class, 'update'])->name('appointments.status');
    Route::patch('/turnos/{appointment}/horario', [AppointmentScheduleController::class, 'update'])->name('appointments.schedule');

    Route::resource('servicios', ServiceController::class)
        ->except('show')
        ->parameters(['servicios' => 'service'])
        ->names('services');
    Route::resource('adicionales', AddonController::class)
        ->only(['create', 'store', 'edit', 'update'])
        ->parameters(['adicionales' => 'addon'])
        ->names('addons');

    Route::resource('clientas', ClientController::class)
        ->except('destroy')
        ->parameters(['clientas' => 'client'])
        ->names('clients');
    Route::post('/clientas/{client}/fotos', [ClientPhotoController::class, 'store'])->name('clients.photos.store');
    Route::delete('/clientas/{client}/fotos/{photo}', [ClientPhotoController::class, 'destroy'])->scopeBindings()->name('clients.photos.destroy');

    Route::get('/bloqueos', [ScheduleBlockController::class, 'index'])->name('blocks.index');
    Route::post('/bloqueos', [ScheduleBlockController::class, 'store'])->name('blocks.store');
    Route::delete('/bloqueos/{block}', [ScheduleBlockController::class, 'destroy'])->name('blocks.destroy');

    Route::get('/horarios', [BusinessHourController::class, 'edit'])->name('hours.edit');
    Route::put('/horarios', [BusinessHourController::class, 'update'])->name('hours.update');
});

/*
|--------------------------------------------------------------------------
| Original WebSockets demo (emisor / receptor)
|--------------------------------------------------------------------------
*/

Route::get('/emisor', [RemoteStyleController::class, 'emisor'])->name('emisor');
Route::post('/emitir', [RemoteStyleController::class, 'emitir'])->name('emitir');
Route::get('/receptor', [RemoteStyleController::class, 'receptor'])->name('receptor');
