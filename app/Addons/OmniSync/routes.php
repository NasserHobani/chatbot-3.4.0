<?php

use App\Addons\OmniSync\Controllers\BotReplyController;
use App\Addons\OmniSync\Controllers\Client\ContactController;
use App\Addons\OmniSync\Controllers\Client\SegmentController;
use App\Addons\OmniSync\Controllers\MessengerCampaignController;
use App\Addons\OmniSync\Controllers\MessengerSettingController;
use App\Addons\OmniSync\Controllers\TemplateController;
use App\Addons\OmniSync\Controllers\Webhook\MessengerWebhookController;

//instagram
use App\Addons\OmniSync\Controllers\Instagram\InstagramContactController;
use App\Addons\OmniSync\Controllers\Instagram\InstagramSettingController;

use Illuminate\Support\Facades\Route;

// Messenger 
Route::get('messenger/webhook/{token}',[MessengerWebhookController::class,'verifyToken'])->name('messenger.verify.token');
Route::post('messenger/webhook/{token}', [MessengerWebhookController::class, 'receiveResponse'])->name('messenger.webhook'); 

Route::group(['prefix' => localeRoutePrefix().'/client', 'middleware' => ['web', 'auth', 'verified', 'subscriptionCheck'], 'as' => 'client.'], function () {
    Route::get('messenger-settings',[MessengerSettingController::class,'messengerSettings'])->name('messenger.settings');
    Route::post('messenger/settings/update', [MessengerSettingController::class, 'messengerSettingUpdate'])->name('messenger.settings.update');
    Route::post('messenger-settings/sync/{id}', [MessengerSettingController::class, 'messengerSync'])->name('messenger-settings.sync');
    Route::post('messenger/settings/remove-token/{id}', [MessengerSettingController::class, 'removeMessengerToken'])->name('messenger.remove-token');

    Route::group(['prefix' => localeRoutePrefix().'/messenger','as' => 'messenger.'], function () {
        
        Route::resource('bot-reply', BotReplyController::class);
        Route::get('templates', [TemplateController::class, 'index'])->name('templates.index');
        Route::get('template/create', [TemplateController::class, 'create'])->name('template.create');
        Route::post('template/store', [TemplateController::class, 'store'])->name('template.store');
        Route::post('template/delete/{id}', [TemplateController::class, 'delete'])->name('template.delete');
        Route::get('template/edit/{id}', [TemplateController::class, 'edit'])->name('template.edit');
        Route::post('template/update/{id}', [TemplateController::class, 'update'])->name('template.update');
        Route::get('send-template', [MessengerCampaignController::class, 'sendTemplate'])->name('send.template');
        Route::post('contact-template/store', [MessengerCampaignController::class, 'storeContactTemplate'])->name('contact.template.store');

        //contacts route
        Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
        Route::post('contact/store', [ContactController::class, 'store'])->name('contact.store');
        Route::get('contact/edit/{id}', [ContactController::class, 'edit'])->name('contact.edit');
        Route::post('contact/update/{id}', [ContactController::class, 'update'])->name('contact.update');
        Route::delete('contact/delete/{id}', [ContactController::class, 'delete'])->name('contact.delete');
        Route::post('contact/bulk-delete', [ContactController::class, 'bulkDelete'])->name('contact.bulk-delete');
        Route::get('contact/view/{id}', [ContactController::class, 'view'])->name('contact.view');
        Route::post('contact/update-details/{id}', [ContactController::class, 'updateDetails'])->name('contact.update-details');
        //segments route
        Route::get('segments', [SegmentController::class, 'index'])->name('segments.index');
        Route::post('segment/store', [SegmentController::class, 'store'])->name('segment.store');
        Route::get('segment/edit/{id}', [SegmentController::class, 'edit'])->name('segment.edit');
        Route::post('segment/update/{id}', [SegmentController::class, 'update'])->name('segment.update');
        Route::delete('segment/delete/{id}', [SegmentController::class, 'delete'])->name('segment.delete');
        Route::get('segments-list', [ContactController::class, 'segments'])->name('segment.list');
    });

    // Instagram
    Route::get('instagram-settings',[InstagramSettingController::class,'instagramSettings'])->name('instagram.settings');
    Route::post('instagram/settings/update', [InstagramSettingController::class, 'instagramSettingUpdate'])->name('instagram.settings.update');
    Route::post('instagram-settings/sync/{id}', [InstagramSettingController::class, 'instagramSync'])->name('instagram-settings.sync');
    Route::post('instagram/settings/remove-token/{id}', [InstagramSettingController::class, 'removeInstagramToken'])->name('instagram.remove-token');

    Route::group(['prefix' => localeRoutePrefix().'/instagram','as' => 'instagram.'], function () {
        
        Route::resource('bot-reply', BotReplyController::class);
        Route::get('templates', [TemplateController::class, 'index'])->name('templates.index');
        Route::get('template/create', [TemplateController::class, 'create'])->name('template.create');
        Route::post('template/store', [TemplateController::class, 'store'])->name('template.store');
        Route::post('template/delete/{id}', [TemplateController::class, 'delete'])->name('template.delete');
        Route::get('template/edit/{id}', [TemplateController::class, 'edit'])->name('template.edit');
        Route::post('template/update/{id}', [TemplateController::class, 'update'])->name('template.update');

        //contacts route
        Route::get('contacts', [InstagramContactController::class, 'index'])->name('contacts.index');
        Route::post('contact/store', [InstagramContactController::class, 'store'])->name('contact.store');
        Route::get('contact/edit/{id}', [InstagramContactController::class, 'edit'])->name('contact.edit');
        Route::post('contact/update/{id}', [InstagramContactController::class, 'update'])->name('contact.update');
        Route::delete('contact/delete/{id}', [InstagramContactController::class, 'delete'])->name('contact.delete');
        Route::post('contact/bulk-delete', [InstagramContactController::class, 'bulkDelete'])->name('contact.bulk-delete');
        Route::get('contact/view/{id}', [InstagramContactController::class, 'view'])->name('contact.view');
        Route::post('contact/update-details/{id}', [InstagramContactController::class, 'updateDetails'])->name('contact.update-details');
    });

});