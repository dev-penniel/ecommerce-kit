<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::livewire('/contacts', 'pages::contacts.index' )->name('contacts');
    Route::livewire('/notifications', 'pages::notifications.index' )->name('notifications');
    Route::livewire('/checkout', 'pages::checkout.index' )->name('checkout');

    Route::livewire('/categories', 'pages::categories.categories' )->name('categories')->middleware('permission:access-categories');

    Route::livewire('/products', 'pages::products.index' )->name('products');
    Route::livewire('/products/create', 'pages::products.create' )->name('products.create');
    Route::livewire('/products/show/{product}', 'pages::products.show' )->name('products.show');
    Route::livewire('/products/edit/{product}', 'pages::products.edit' )->name('products.edit');


    Route::livewire('/roles', 'pages::roles.index' )->name('roles');
    Route::livewire('/roles/create', 'pages::roles.create' )->name('roles.create');
    Route::livewire('/role/{id}', 'pages::roles.edit' )->name('roles.edit');

    Route::livewire('/users', 'pages::users.index' )->name('users')->middleware('permission:access-users');
    Route::livewire('/users/create', 'pages::users.create' )->name('users.create')->middleware('permission:create-users');
    Route::livewire('/user/{id}', 'pages::users.edit' )->name('user.edit')->middleware('permission:edit-users');
    
});


require __DIR__.'/settings.php';
