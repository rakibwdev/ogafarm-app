<?php

// Subscription routes — Milestone 3 implements checkout + webhooks.
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('subscribe')->group(function () {
    Route::get('/plans', fn () => view('subscriptions.plans'))->name('subscription.plans');
});
