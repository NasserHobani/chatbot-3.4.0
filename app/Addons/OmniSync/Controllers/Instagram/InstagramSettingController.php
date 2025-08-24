<?php

namespace App\Addons\OmniSync\Controllers\Instagram;

use App\Addons\OmniSync\Repository\Instagram\InstagramSettingRepository;
use App\Http\Controllers\Controller;
use App\Repositories\ClientRepository;
use App\Repositories\CountryRepository;
use Illuminate\Http\Request;

class InstagramSettingController extends Controller
{
    protected $repo;
    protected $client;
    protected $country;

    public function __construct(InstagramSettingRepository $repo, ClientRepository $client, CountryRepository $country)
    {
        $this->repo = $repo;
        $this->client = $client;
        $this->country = $country;   
    }

    public function instagramSettings(Request $request)
    {
        $instagramSettingsView = 'addon:OmniSync::instagram.setting.index';
        return view($instagramSettingsView);
    }

    public function instagramSettingUpdate(Request $request)
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

        return $this->repo->instagramSettingUpdate($request);
    }

    public function instagramSync(Request $request)
    {
        if (isDemoMode()) {
            $data = [
                'status' => false,
                'message'  => __('this_function_is_disabled_in_demo_server'),
            ];
            return response()->json($data);
        }
        
        return $this->repo->instagramSync($request);
    }

    public function removeInstagramToken(Request $request, $id)
    {
        if (isDemoMode()) {
            $data = [
                'status' => false,
                'message'  => __('this_function_is_disabled_in_demo_server'),
            ];
            return response()->json($data);
        }
        return $this->repo->removeInstagramToken($request, $id);
    }
}

?>