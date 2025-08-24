<?php
namespace App\Addons\OmniSync\Repository;

use App\Enums\MessageEnum;
use App\Enums\MessageStatusEnum;
use App\Enums\TypeEnum;
use App\Jobs\SendMessengerCampaignMessageJob;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\ContactAttribute;
use App\Models\ContactsList;
use App\Models\Country;
use App\Models\Message;
use App\Models\Segment;
use App\Models\Subscription;
use App\Models\Template;
use App\Traits\CommonTrait;
use App\Traits\RepoResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CampaignRepository
{
    use CommonTrait, RepoResponse;

    private $model;

    private $contact;

    private $template;

    private $segment;

    private $contact_list;

    private $country;

    private $message;

    private $attribute;

    public function __construct(
        Campaign $model,
        Contact $contact,
        Template $template,
        Segment $segment,
        ContactsList $contact_list,
        Country $country,
        Message $message,
        ContactAttribute $attribute,
    ) {
        $this->model        = $model;
        $this->contact      = $contact;
        $this->template     = $template;
        $this->segment      = $segment;
        $this->contact_list = $contact_list;
        $this->country      = $country;
        $this->message      = $message;
        $this->attribute    = $attribute;
    }

    public function all()
    {
        return Campaign::where('campaign_type','messenger')->latest()->withPermission()->paginate(setting('pagination'));
    }

    public function activeSegments()
    {
        return Campaign::where('status', 1)->where('campaign_type','messenger')->withPermission()->get();
    } 

    public function ContactTemplateStore($request)
    {
        DB::beginTransaction();

        try {

            $client = auth()->user()->client;
            $activeSubscription = $client->activeSubscription;
          
            if (!$activeSubscription) {
                return $this->formatResponse(false, __('no_active_subscription'), 'client.campaigns.index', []);
            }

            $campaignRemaining = $activeSubscription->campaign_remaining;
            $conversationRemaining = $activeSubscription->conversation_remaining;

            if($activeSubscription->campaign_limit != -1 && $campaignRemaining <= 0){
                return $this->formatResponse(false, __('insufficient_campaigns_limit'), 'client.campaigns.index', []);
            }

            if($activeSubscription->conversation_limit != -1 && $conversationRemaining <= 0){
                return $this->formatResponse(false, __('insufficient_conversation_limit'), 'client.campaigns.index', []);
            }
            
            $contact                    = $this->contact->findOrFail($request->contact_id);
            $template                   = $this->template->active()->findOrFail($request->template_id);

            $content                    = null;
            $header_text                = null;
            $header_image               = null;
            $header_document            = null;
            $header_video               = null;
            $header_audio               = null;
            $footer_text                = null;
            $title                      = null;
            $subtitle                   = null;

            $buttons                    = [];
            $template_components        = $template->components;
            $component_body             = [];
            $component_header           = [];
            $component_buttons          = [];
            
            if($template['components'][0]['payload']['template_type'] == 'media'){
                $header_video = $template['components'][0]['payload']['elements'][0]['url'] ?? NULL;
            }

            if($template['components'][0]['payload']['template_type'] == 'generic'){
                $header_image = $template['components'][0]['payload']['elements'][0]['image_url'] ?? NULL;
            }

            if(isset($template['components'][0]['payload']['elements'][0]['title'])){
                $title = $template['components'][0]['payload']['elements'][0]['title'] ?? NULL;
            }

            if(isset($template['components'][0]['payload']['elements'][0]['subtitle'])){
                $subtitle = $template['components'][0]['payload']['elements'][0]['subtitle'] ?? NULL;
            }

            if(isset($template['components'][0]['payload']['elements'][0]['buttons'])){

                $buttons_for_chat  = [];
                
                foreach($template['components'][0]['payload']['elements'][0]['buttons'] as $button){
                    $buttons_for_chat[]       = [
                        'type' => $button['type'],
                        'text' => $button['title'],
                        'url'  => $button['url'],
                    ];
                }

            }

            $message                    = new $this->message();
            $message->contact_id        = $contact->id;
            $message->template_id       = $template->id;
            $message->client_id         = Auth::user()->client->id;
            $message->header_text       = $header_text;
            $message->footer_text       = $footer_text;
            $message->header_image      = $header_image;
            $message->header_audio      = $header_audio;
            $message->header_video      = $header_video;
            $message->header_location   = $request->header_location;
            $message->header_document   = $header_document;
            $message->buttons           = json_encode($buttons);
            $message->value             = $content;
            $message->error             = null;
            $message->message_type      = MessageEnum::TEMPLATE;
            $message->status            = MessageStatusEnum::SCHEDULED;
            $message->source            = TypeEnum::MESSENGER;
            $scheduleTime               = Carbon::now();
            $message->schedule_at       = $scheduleTime;
            $message->component_header  = json_encode($component_header);
            $message->component_body    = json_encode($component_body);
            $message->component_buttons = json_encode($component_buttons);
            $message->campaign_id       = null;
            $message->is_campaign_msg   = 1;
            $message->save();

            $conversation_remaining     = $conversationRemaining - 1;

            Subscription::where('client_id', auth()->user()->client_id)->where('status', 1)->latest()->update(['conversation_remaining' => $conversation_remaining]);
            $this->conversationUpdate(Auth::user()->client_id, $contact->id);
            SendMessengerCampaignMessageJob::dispatch($message);
            DB::commit();

            return $this->formatResponse(true, __('created_successfully'), 'client.chat.index', []);

        } catch (\Throwable $e) {

            DB::rollBack();

            if (config('app.debug')) {
                dd($e->getMessage());            
            }

            logError('Send Template: ', $e);

            return $this->formatResponse(false, __('an_unexpected_error_occurred_please_try_again_later.'), 'client.chat.index', []);

        }
    }
}
?>