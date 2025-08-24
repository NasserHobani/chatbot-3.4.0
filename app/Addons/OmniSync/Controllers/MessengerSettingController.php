<?php

namespace App\Addons\OmniSync\Controllers;

use App\Addons\OmniSync\Repository\MessengerSettingRepository;
use App\Http\Controllers\Controller;
use App\Repositories\ClientRepository;
use App\Repositories\CountryRepository;
use Illuminate\Http\Request;

class MessengerSettingController extends Controller
{
    protected $repo;
    protected $client;
    protected $country;

    public function __construct(MessengerSettingRepository $repo, ClientRepository $client, CountryRepository $country)
    {
        $this->repo = $repo;
        $this->client = $client;
        $this->country = $country;   
    }

    public function messengerSettings(Request $request)
    {
        $messengerSettingsView = 'addon:OmniSync::setting.index';
        return view($messengerSettingsView);
    }

    public function messengerSettingUpdate(Request $request)
    {
        if (isDemoMode()) {
            $data = [
                'status' => false, 
                'message'  => __('this_function_is_disabled_in_demo_server'),
            ];
            return response()->json($data);
        }
        
        $clientId = auth()->user()->client->id;
        
        $request->validate([
            'access_token' => ['required','string'],
        ],[
            'access_token.required' => __('access_token_is_required'),
            'access_token.string' => __('access_token_must_be_string'),
        ]);

        return $this->repo->messengerSettingUpdate($request);
    }

    public function messengerSync(Request $request)
    {
        if (isDemoMode()) {
            $data = [
                'status' => false,
                'message'  => __('this_function_is_disabled_in_demo_server'),
            ];
            return response()->json($data);
        }
        
        return $this->repo->messengerSync($request);
    }

    public function removeMessengerToken(Request $request, $id)
    {
        if (isDemoMode()) {
            $data = [
                'status' => false,
                'message'  => __('this_function_is_disabled_in_demo_server'),
            ];
            return response()->json($data);
        }
        return $this->repo->removeMessengerToken($request, $id);
    }
}

?>