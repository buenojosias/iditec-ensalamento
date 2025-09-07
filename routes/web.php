<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => []], function () {
    Route::view('/', 'dashboard')->name('dashboard');
    Route::get('modulos', \App\Livewire\Modules\Index::class)->name('modules.index');

    Route::get('turmas', \App\Livewire\Teams\Index::class)->name('teams.index');
    Route::get('turmas/importar', \App\Livewire\Teams\Import::class)->name('teams.import');
    Route::get('turmas/proximas', \App\Livewire\Teams\Next::class)->name('teams.next');
    Route::get('turmas/{team}', \App\Livewire\Teams\Show::class)->name('teams.show');

    Route::get('alunos', [\App\Http\Controllers\TeamController::class, 'index'])->name('students.index');
    Route::get('alunos/importar', \App\Livewire\Students\Import::class)->name('students.import');
    Route::get('alunos/importar/{team}', \App\Livewire\Students\Import::class)->name('students.import');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
