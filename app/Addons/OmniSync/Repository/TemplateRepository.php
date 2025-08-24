<?php
namespace App\Addons\OmniSync\Repository;

use App\Enums\TypeEnum;
use App\Models\Template;
use App\Traits\ImageTrait;
use App\Traits\CommonTrait;
use Illuminate\Support\Str;
use App\Traits\RepoResponse;
use App\Traits\TelegramTrait;
use App\Traits\TemplateTrait;
use App\Traits\WhatsAppTrait;
use App\Services\TemplateService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TemplateRepository
{
    use CommonTrait, ImageTrait, RepoResponse, TelegramTrait, WhatsAppTrait,TemplateTrait;
    const GRAPH_API_BASE_URL = 'https://graph.facebook.com/v19.0/';
    private $model;

    protected $whatsappService;

    public function __construct(Template $model, WhatsAppService $whatsappService)
    {
        $this->model           = $model;
        $this->whatsappService = $whatsappService;
    }

    public function combo()
    {
        return $this->model->withPermission()->active()->pluck('name', 'id');
    }

    public function all()
    {
        return $this->model->where('type','messenger')->withPermission()->latest()->paginate(setting('pagination'));
    }

    public function activeSegments()
    {
        return $this->model->withPermission()->where('status', 1)->get();
    }

    public function find($id)
    {
        return $this->model->withPermission()->find($id);
    }

    public function get_size($file_path)
    {
        return Storage::size($file_path);
    }

    public function store($request)
    { 
        try {
            $clientSetting = Auth::user()->client->messengerSetting;
            $media = null;
            $template_name = Str::lower($request->template_name);
            $template_type = $request->template_type;
            
            // Prepare the message template data
            $messageTemplate = [
                'name' => $template_name,
                'language' => "en",
                'category' => $request->template_type,
                'components' => []
            ];

            $buttons = [];

            if ($request->button_type !== "NONE") {

                if ($request->button_type == "CTA") {
                    
                    $button_text = $request->button_text;
                    $button_value = $request->button_value;
                    foreach ($request->type_of_action as $key => $action) {
                        if ($action == "URL") {
                            $buttons[] = [
                                "type" => "web_url",
                                "title" => $button_text[$key],
                                "url" => $button_value[$key],
                            ];
                        } 
                    }                    
                }
            }

            if($template_type == 'IMAGE'){
                $messageTemplate['components'][] = [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'generic',
                        'elements' => [
                            [
                                'title' => $request->title ?? NULL,
                                'subtitle' => $request->subtitle ?? NULL,
                                'image_url' => $request->template_image,
                                'buttons' => $buttons ?? []
                            ]
                        ]
                    ]
                ];
            }

            if($template_type == 'VIDEO'){
                $messageTemplate['components'][] = [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'media',
                        'elements' => [
                            [
                                'media_type' => "video",
                                'url' => $request->template_video,
                                'buttons' => $buttons ?? []
                            ]
                        ]
                    ]
                ];
            }

            $template = new $this->model();
            $template->name = $template_name;
            $template->client_setting_id = $clientSetting->id;
            $template->components = $messageTemplate['components'];
            $template->category = $template_type;
            $template->language = "en";
            $template->client_id = Auth::user()->client->id;
            $template->type = TypeEnum::MESSENGER;
            $template->status = "APPROVED";
            $template->template_id = time();
            $template->header_media = $media;
            $template->save();
            return $this->formatResponse(true, __('message_template_created_successfully'), route('client.messenger.templates.index'), []);

        } catch (\Throwable $e) {

            \Log::error($e);
            if (config('app.debug')) {
                dd($e);            
            }
            logError('Store error: ', $e);
            return $this->formatResponse(false, $e->getMessage(), route('client.messenger.templates.index'), []);

        }
    }

    public function update($request, $id)
    {
        $template = $this->model->withPermission()->find($id);
        $clientSetting = Auth::user()->client->messengerSetting;
        $template_type = $request->template_type;
        $template_name = Str::lower($request->template_name);

        try {

            $media = null;
            $template_type = $request->template_type;

            $messageTemplate = [
                'name' => $template_name,
                'language' => "en",
                'category' => $request->template_type,
                'components' => []
            ];
            
            $buttons = [];
            
            if ($request->button_type !== "NONE") {

                if ($request->button_type == "CTA") {
                    
                    $button_text = $request->button_text;
                    $button_value = $request->button_value;
                    foreach ($request->type_of_action as $key => $action) {
                        if ($action == "URL") {
                            $buttons[] = [
                                "type" => "web_url",
                                "title" => $button_text[$key],
                                "url" => $button_value[$key],
                            ];
                        } 
                    }                    
                }
            }

            if($template_type == 'IMAGE'){
                $messageTemplate['components'][] = [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'generic',
                        'elements' => [
                            [
                                'title' => $request->title ?? NULL,
                                'subtitle' => $request->subtitle ?? NULL,
                                'image_url' => $request->template_image,
                                'buttons' => $buttons ?? []
                            ]
                        ]
                    ]
                ];
            }

            if($template_type == 'VIDEO'){
                $messageTemplate['components'][] = [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'media',
                        'elements' => [
                            [
                                'media_type' => "video",
                                'url' => $request->template_video,
                                'buttons' => $buttons ?? []
                            ]
                        ]
                    ]
                ];
            }

            $template->name = $template_name;
            $template->components = $messageTemplate['components'];
            $template->category = $template_type;
            $template->language = "en";
            $template->type = TypeEnum::MESSENGER;
            $template->status = "APPROVED";
            $template->template_id = time();
            $template->header_media = $media;
            $template->save();

            return $this->formatResponse(true, __('updated_successfully'), route('client.messenger.templates.index'), []);
            
        } catch (\Throwable $e) {
            if (config('app.debug')) {
                dd($e->getMessage());            
            }
            logError('Template Update Error: ', $e);
            return $this->formatResponse(false, $e->getMessage(), route('client.messenger.templates.index'), []);
        }
    }

    public function destroy($id)
    {
        try {
            $template = $this->model->withPermission()->find($id);
            $accessToken = getClientWhatsAppAccessToken(Auth::user()->client);
            $whatsappBusinessAccountId = getClientWhatsAppBusinessAcID(Auth::user()->client);
            $hsmId = $template->template_id;
            $templateName = $template->name;
            $apiUrl = self::GRAPH_API_BASE_URL . "{$whatsappBusinessAccountId}/message_templates?hsm_id={$hsmId}&name={$templateName}";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json'
                ),
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'DELETE',
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $_response = json_decode($response);
            if (isset($_response->error)) {
                $error_message = !empty($_response->error->error_user_msg) ?
                    $_response->error->error_user_msg :
                    $_response->error->message;
                if ($_response->error->code == "100") {
                    $this->model->withPermission()->where('id', $id)->delete();
                }
                return $this->formatResponse(
                    false,
                    $error_message,
                    'client.templates.create',
                    []
                );
            } else {
                $this->model->withPermission()->where('id', $id)->delete();
            }
            return $this->formatResponse(true, __('deleted_successfully'), 'client.templates.index', []);
        } catch (\Throwable $e) {
            if (config('app.debug')) {
                dd($e->getMessage());            
            }
            logError('Error: ', $e);
            return $this->formatResponse(false, $e->getMessage(), 'client.templates.index', []);
        }
    }

    public function syncTemplateByID($id)
    {
        try {
            $clientSetting = Auth::user()->client->whatsappSetting;
            $template = $this->model->withPermission()->find($id);
            $accessToken = getClientWhatsAppAccessToken(Auth::user()->client);
            $apiUrl = self::GRAPH_API_BASE_URL . "/{$template->template_id}";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json',
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $templateObject = json_decode($response);
            if (isset($templateObject) && isset($templateObject->error)) {
                $error_message = isset($templateObject->error->error_user_msg) ?
                    $templateObject->error->error_user_msg :
                    $templateObject->error->message;
                return $this->formatResponse(
                    false,
                    $error_message,
                    'client.templates.index',
                    []
                );
            }
            $template = $this->model->withPermission()->firstOrNew(['template_id' => $templateObject->id]);
            $template->fill([
                'name'          => $templateObject->name,
                'client_setting_id' => $clientSetting->id,
                'components'    => $templateObject->components ?? [],
                'category'      => $templateObject->category,
                'language'      => $templateObject->language,
                'client_id'     => Auth::user()->client->id,
                'status'        => $templateObject->status,
                'type'          => TypeEnum::WHATSAPP,
            ]);
            $template->save();
            return $this->formatResponse(
                true,
                __('template_sync_successfully'),
                'client.templates.index',
                []
            );
        } catch (\Throwable $e) {
            if (config('app.debug')) {
                dd($e->getMessage());            
            }           
            logError('Error: ', $e);
             return $this->formatResponse(
                false,
                $e->getMessage(),
                'client.templates.index',
                []
            );
        }
    }

    public function getTemplateByID($id)
    {
        $row  = $this->find($id);
        $data = app(TemplateService::class)->execute($row);
        return view('backend.client.whatsapp.campaigns.partials.__template', $data)->render();
    }

    public function statusChange($request)
    {
        $id = $request['id'];

        return $this->model->find($id)->update($request);
    }

    public function loadTemplate()
    {
        $clientSetting = Auth::user()->client->whatsappSetting;
        return $this->getLoadTemplate($clientSetting);
    }

    public function whatsappTemplate()
    {
        return $this->model->withPermission()->active()->where('type', TypeEnum::WHATSAPP)->latest()->paginate();
    }
    public function activeWhatsappTemplate()
    {
        return $this->model->withPermission()->active()->where('type', TypeEnum::WHATSAPP)->latest()->get();
    }
}
