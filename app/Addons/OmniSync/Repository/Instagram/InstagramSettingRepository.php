<?php

namespace App\Addons\OmniSync\Repository\Instagram;

use App\Enums\TypeEnum;
use App\Models\Client;
use App\Models\ClientSetting;
use App\Models\ClientSettingDetail;
use App\Models\Contact;
use App\Traits\RepoResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstagramSettingRepository
{
    use RepoResponse;

    private $model;

    private $contact;

    private $client;

    private $clientSettingDetail;

    public function __construct(
        ClientSetting $model,
        Contact $contact,
        Client $client,
        ClientSettingDetail $clientSettingDetail
    )
    {
        $this->model            = $model;
        $this->contact          = $contact;
        $this->client           = $client;
        $this->clientSettingDetail = $clientSettingDetail;
    }


    public function instagramSettingUpdate($request)
    {
      DB::beginTransaction();
          
          try {
              $client = Auth::user()->client;
              $is_connected = 0;
              $token_verified = 0;
              $scopes         = null;
              $accessToken  = $request->access_token;
              $url          = 'https://graph.facebook.com/debug_token?input_token=' . $accessToken . '&access_token=' . $accessToken;
              $ch           = curl_init($url);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
              $response     = curl_exec($ch);
              $responseData = json_decode($response, true);
              if (isset($responseData['error'])) {
                  return $this->formatResponse(false, $responseData['error']['message'], 'client.instagram.settings', []);
              } else {
                  if (isset($responseData['data']['is_valid']) && $responseData['data']['is_valid'] === true) {
                      $is_connected = 1;
                      $token_verified = 1;
                      $scopes = $responseData['data']['scopes'];
                  } else {
                      return $this->formatResponse(false, __('access_token_is_not_valid'), 'client.instagram.settings', []);
                  }
              }
    
              $scopes = $responseData['data']['scopes'];
              $dataAccessExpiresAt = isset($responseData['data']['data_access_expires_at']) ?
                  (new \DateTime())->setTimestamp($responseData['data']['data_access_expires_at']) : null;
              $dataExpiresAt = isset($responseData['data']['expires_at']) ?
                  (new \DateTime())->setTimestamp($responseData['data']['expires_at']) : null;  
              curl_close($ch);  
    
              $clientSetting       = $this->model
                  ->where('type', TypeEnum::INSTAGRAM->value)
                  ->where('client_id', Auth::user()->client->id)
                  ->first();
    
              if ($clientSetting) {
                  $clientSetting = $this->model->where('type', TypeEnum::INSTAGRAM)->where('client_id', Auth::user()->client->id)->first();
                  $clientSetting->access_token        = $accessToken;
                  $clientSetting->app_id              = $responseData['data']['app_id'] ?? $request->app_id;
                  $clientSetting->is_connected        = $is_connected;
                  $clientSetting->token_verified      = $token_verified;
                  $clientSetting->scopes              = $scopes;
                  $clientSetting->granular_scopes     = $responseData['data']['granular_scopes'] ?? null;
                  $clientSetting->name                = $responseData['data']['application'] ?? null;
                  $clientSetting->data_access_expires_at = $dataAccessExpiresAt;
                  $clientSetting->expires_at          = $dataExpiresAt;
                  $clientSetting->fb_user_id          = $responseData['data']['user_id'] ?? null;
                  $clientSetting->update();
              }else{
                  $clientSetting = $this->model->create([
                      'type'                => TypeEnum::INSTAGRAM,
                      'client_id'           => Auth::user()->client->id,
                      'access_token'        => $accessToken,
                      'app_id'              => $responseData['data']['app_id'] ?? $request->app_id,
                      'is_connected'        => $is_connected,
                      'token_verified'      => $token_verified,
                      'scopes'              => $scopes,
                      'granular_scopes'     => $responseData['data']['granular_scopes'] ?? null,
                      'name'                => $responseData['data']['application'] ?? null,
                      'data_access_expires_at' => $dataAccessExpiresAt,
                      'expires_at'          => $dataExpiresAt,
                      'fb_user_id'          => $responseData['data']['user_id'] ?? null,
                  ]);
              }
    
              $this->getPageInfo($accessToken,$clientSetting);
    
              DB::commit();
              return $this->formatResponse(true, __('updated_successfully'), route('client.instagram.settings'), []);
              
          } catch (\Throwable $th) {
              DB::rollback();
              if (config('app.debug')) {
                  dd($th->getMessage());
              }
              logError('Throwable: ', $th);
              return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), '', []);
          }
    }



    public function instagramSync($request)
    {
        DB::beginTransaction();
        try {
            $clientSetting = @Auth::user()->client->instagramSetting;

            if (!$clientSetting) {
                return $this->formatResponse(false, __('instagram_setting_not_found'), 'client.instagram.settings', []);
            }

            $accessToken = $clientSetting->access_token;
            $url = 'https://graph.facebook.com/debug_token?input_token=' . $accessToken . '&access_token=' . $accessToken;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $response = curl_exec($ch);
            curl_close($ch);
            $responseData = json_decode($response, true);
            if (isset($responseData['error'])) {
                return $this->formatResponse(false, $responseData['error']['message'], 'client.instagram.settings', []);
            }
            if (isset($responseData['data']['is_valid']) && $responseData['data']['is_valid'] === true) {

                $clientSetting->is_connected = 1;
                $clientSetting->token_verified = 1;
                $clientSetting->scopes = $responseData['data']['scopes'];
                $clientSetting->granular_scopes = $responseData['data']['granular_scopes'] ?? null;
                $clientSetting->data_access_expires_at = isset($responseData['data']['data_access_expires_at']) ?
                    (new \DateTime())->setTimestamp($responseData['data']['data_access_expires_at']) : null;
                $clientSetting->expires_at = isset($responseData['data']['expires_at']) ?
                    (new \DateTime())->setTimestamp($responseData['data']['expires_at']) : null;
                $clientSetting->fb_user_id = $responseData['data']['user_id'] ?? null;
                $clientSetting->name    = $responseData['data']['application'] ?? null;
                $clientSetting->save();
                 
                $this->clientSettingDetail->updateOrCreate(
                    [
                        'client_setting_id' => $clientSetting->id
                    ],
                    [
                        'phone_number_id' => $clientSetting->phone_number_id ?? null,
                        'account_review_status' => $accountReviewStatus['data'] ?? null,
                        'certificate' => $phoneNumbers['data']->data[0]->certificate ?? null,
                        'new_certificate' => $phoneNumbers['data']->data[0]->new_certificate ?? null,
                        'messaging_limit_tier' => $phoneNumbers['data']->data[0]->messaging_limit_tier ?? null,
                        'profile_info' => [
                            'webhook_configuration' => $phoneNumbers['data']->data[0]->webhook_configuration->application ?? null,
                            'message_template_namespace' => $accountReviewStatus['data']->message_template_namespace ?? null,
                            'address' => $businessProfile['data']->data[0]->address ?? null,
                            'email' => $businessProfile['data']->data[0]->email ?? null,
                            'description' => $businessProfile['data']->data[0]->description ?? null,
                            'vertical' => $businessProfile['data']->data[0]->vertical ?? null,
                            'about' => $businessProfile['data']->data[0]->about ?? null,
                            'websites' => json_encode($businessProfile['data']->data[0]->websites ?? []),
                            'profile_picture_url' => $businessProfile['data']->data[0]->profile_picture_url ?? null,
                        ]
                    ]
                );
               
                DB::commit();
                return $this->formatResponse(true, __('instagram_settings_synced_successfully'), 'client.instagram.settings', []);

            } else {
                return $this->formatResponse(false, __('access_token_is_not_valid'), 'client.instagram.settings', []);
            }
        } catch (\Throwable $e) {
            DB::rollback();
            logError('Throwable: ', $e);
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            return $this->formatResponse(false, $e->getMessage(), 'client.instagram.settings', []);
        }
    }


    public function removeInstagramToken($request, $id)
    {
        if (isDemoMode()) {
            return $this->formatResponse(false, __('this_function_is_disabled_in_demo_server'), 'client.instagram.settings', []);
        }
        DB::beginTransaction();
        try {
            $clientSetting = $this->model->where('type', TypeEnum::INSTAGRAM)
                ->where('client_id', Auth::user()->client->id)
                ->where('id', $id)
                ->firstOrFail();
            $clientSetting->delete();
            DB::commit();
            return $this->formatResponse(true, __('deleted_successfully'), 'client.instagram.settings', []);
        } catch (\Throwable $e) {
            DB::rollback();
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            logError('Throwable: ', $e);
            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.instagram.settings', []);
        }
    }
    

    private function getPageInfo($accessToken,$clientSetting)
    {
        try {

            // $url = 'https://graph.facebook.com/v22.0/me?access_token=' . $accessToken;
            
            $url = 'https://graph.facebook.com/v22.0/me?fields=instagram_business_account,name&access_token=' . $accessToken;
            
            $ch  = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $response     = curl_exec($ch);
            $responseData = json_decode($response, true);
            
            $businessName = $responseData['name'] ?? null;
            $instagramId = $responseData['instagram_business_account']['id'] ?? null;
            
            // log::info('Page info check id', [$clientSetting]);
            
            if (isset($responseData['error'])) {
                return $this->formatResponse(false, $responseData['error']['message'], 'client.instagram.settings', []);
            } 
            curl_close($ch);
            
            $clientSetting->business_account_name = $businessName;
            $clientSetting->business_account_id = $instagramId;
            $clientSetting->update();
        } catch (\Throwable $th) {
            return false;
        }
        
        return true;
    }
}

?>