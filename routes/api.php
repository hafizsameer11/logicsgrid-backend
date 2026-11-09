<?php

use App\Http\Controllers\Api\ContentController;
use Illuminate\Support\Facades\Route;

Route::controller(ContentController::class)->group(function () {
    Route::get('site', 'site');
    Route::get('pages/{slug}', 'page');
    Route::get('services', 'services');
    Route::get('services/{slug}', 'service');
    Route::get('case-studies', 'caseStudies');
    Route::get('case-studies/{slug}', 'caseStudy');
    Route::get('posts', 'posts');
    Route::get('posts/{slug}', 'post');
    Route::get('tech-groups', 'techGroups');
    Route::get('care-plans', 'carePlans');
    Route::get('redirects', 'redirects');
    Route::get('sitemap', 'sitemap');
    Route::post('enquiries', 'storeEnquiry')->middleware('throttle:10,1');
});
