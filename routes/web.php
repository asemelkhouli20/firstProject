<?php
use App\Models\User;
use App\Notifications\OrderShipped;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\PostController;
use App\Jobs\SlowJob;

Route::get('/', function () {
    $user = User::inRandomOrder()->first();
    Notification::send($user, new OrderShipped());


    return view('welcome');
});

Route::resource('posts', PostController::class);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
