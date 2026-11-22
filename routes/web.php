<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// Uploaded images normally come straight from the public/storage symlink. If that link is
// missing (for example after a container rebuild), Laravel serves the file from the public disk
// instead, so images never break.
Route::get('storage/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path, null, ['Cache-Control' => 'public, max-age=604800']);
})->where('path', '.*');
