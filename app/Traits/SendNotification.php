<?php

namespace App\Traits;

use App\Events\PusherNotification;
use App\Models\Contact;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

trait SendNotification
{
    public function sendNotification($users = [], $message = null, $message_type = 'success', $url = null, $details = null): bool
    {
        foreach ($users as $user) {
            $notification              = new Notification();
            $notification->user_id     = $user;
            $notification->title       = $message;
            $notification->description = $details;
            $notification->url         = $url;
            $notification->created_by  = auth()->id();
            $notification->save();
        }

        try {
            if (setting('is_pusher_notification_active')) {
                foreach ($users as $user) {
                    event(new PusherNotification($user, $message, $message_type, $url, $details));
                }
            }
        } catch (\Exception $e) {
            logError('Error: ', $e);
        }

        return true;
    }

    public function pushNotification($data)
    {
        $contact_information = Contact::find($data['contact_id']);

        $headers = [
            'Authorization' => 'Basic ' . setting('onesignal_rest_api_key'),
            'accept'        => 'application/json',
            'content-type'  => 'application/json',
        ];

        $body = [
            'include_player_ids' => $data['ids'],
            'contact_id'         => $data['contact_id'],
            'contents'           => [
                'en' => $data['message'],
            ],
            'headings'           => [
                'en' => $data['heading'],
            ],
            'app_id'             => setting('onesignal_app_id'),
            'url'                => $data['url'],
            'data'               => [
                'contact_id' => $data['contact_id'],
            ],
            'contact_information' => $contact_information,
	];
	Log::info('Sending to One Signal');
	Log::info($body);

        return httpRequest('https://onesignal.com/api/v1/notifications', $body, $headers);
    }


    public function sendAdminNotifications($data)
    {
        $admin   = User::find(1);
        $message = $data['message'];
        try {
            $this->sendNotification([$admin->id], $message);
        } catch (\Exception $e) {
        }

        try {
            $this->pushNotification([
                'ids'     => $admin->onesignal_player_id,
                'message' => $message,
                'heading' => $data['heading'],
                'url'     => $data['url'],
            ]);
        } catch (\Exception $e) {
        }
    }
}
