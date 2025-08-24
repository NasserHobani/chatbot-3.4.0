<?php

namespace App\Addons\OmniSync\Repository\Webhook;

use App\Addons\OmniSync\Traits\BotReplyTrait;
use App\Addons\OmniSync\Traits\MessengerTrait;
use App\Enums\MessageStatusEnum;
use App\Enums\StatusEnum;
use App\Enums\TypeEnum;
use App\Models\Client;
use App\Models\Contact;
use App\Models\ContactRelationList;
use App\Models\ContactRelationSegments;
use App\Models\ContactsList;
use App\Models\Flow;
use App\Models\Message;
use App\Models\Segment;
use App\Traits\ImageTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MessengerRepository
{
    use MessengerTrait, BotReplyTrait, ImageTrait;

    private $clientModel;
    private $contact;
    private $flow;
    private $message;

    public function __construct(
        Client $clientModel,
        Contact $contact,
        Flow $flow,
        Message $message,
    )
    {
        $this->clientModel = $clientModel;
        $this->contact = $contact;
        $this->flow = $flow;
        $this->message = $message;
    }

    public function verifyToken($request,$token)
    {
        $hubMode = $request->hub_mode;
        $hubVerifyToken = $request->hub_verify_token;
        $hubChallenge = $request->hub_challenge;

        $client = $this->clientModel->where('webhook_verify_token', $hubVerifyToken)->with('messengerSetting')->first();

        if (!empty($client) && !empty($client->webhook_verify_token)) {
            if ($hubMode && $hubMode === 'subscribe') {
                if (!empty($client->messengerSetting)) {
                    $messengerSetting = $client->messengerSetting;
                    $messengerSetting->webhook_verified = 1;
                    $messengerSetting->update();
                }else{
                    $client->load('messengerSetting');
                }
                return response($hubChallenge, 200)->header('Content-Type', 'text/plain');
            }else {
                return response()->json([], 403);
            }
        } else {
            return response()->json([], 403);
        }
    }

    // public function receiveResponse($request, $token)
    // {
    //     $value = $request->entry[0]['messaging'][0];
    //     $pageID = $request->entry[0]['id'] ?? null;

    //     if (!$pageID) {
    //         return response()->json(['send' => false, 'error' => 'Page ID not provided in the request']);
    //     }

    //     $client = $this->clientModel->active()
    //      ->where('webhook_verify_token', $token)
    //      ->whereHas('messengerSetting', function ($query) use ($pageID) {
    //          $query->where('business_account_id', $pageID);
    //      })
    //      ->with('messengerSetting')
    //      ->first();

    //      $activeSubscription    = $client->activeSubscription;

    //      if (!empty($client) && !empty($client->webhook_verify_token) && $activeSubscription->messenger_access) {
    //         try {
    //             if (isset($value['message'])) {
    //                 $this->handleIncomingMessage($value, $client);
    //             }
    //             return response()->json(['send' => true]);
    //         } catch (\Throwable $e) {
    //             logError('Receive Response : ', $e);
    //             return response()->json(['send' => false, 'error' => __('an_unexpected_error_occurred_please_try_again_later.'), 'data' => $request]);
    //         }

    //     } else {
    //         return response()->json(['send' => false]);
    //     }
    // }

    public function receiveResponse($request, $token)
    {
        $entry = $request->entry[0] ?? null;
        $value = $entry['messaging'][0] ?? null;
        $pageID = $entry['id'] ?? null;
        $instagramObj = $request->object ?? null;

        if (!$pageID) {
            return response()->json(['send' => false, 'error' => 'Page ID not provided in the request']);
        }

        // Skip echo messages (these are sent by your own Instagram/Facebook page)
        // if (!empty($value['message']['is_echo'])) {
        //    Log::info('Skipped echo message', ['sender' => $value['sender']['id'] ?? 'unknown']);
        //   return response()->json(['send' => true, 'echo_skipped' => false]);
        // }

        // Find client by token and matching page ID
        if($instagramObj!=="instagram"){
        $client = $this->clientModel->active()
            ->where('webhook_verify_token', $token)
            ->whereHas('messengerSetting', function ($query) use ($pageID) {
                $query->where('business_account_id', $pageID);
            })
            ->with('messengerSetting')
            ->first();
        }else{
        $client = $this->clientModel->active()
            ->where('webhook_verify_token', $token)
            ->whereHas('instagramSetting', function ($query) use ($pageID) {
                $query->where('business_account_id', $pageID);
            })
            ->with('instagramSetting')
            ->first();
        }


        if (!$client) {
            Log::error('Client not found for page ID', ['page_id' => $pageID, 'token' => $token]);
            return response()->json(['send' => false, 'error' => 'Client not found']);
        }

        $activeSubscription = $client->activeSubscription ?? null;

        if (!empty($client->webhook_verify_token) && $activeSubscription && $activeSubscription->messenger_access) {
            try {
                if (isset($value['message'])) {
                    $this->handleIncomingMessage($value, $client, $instagramObj);
                }

                if (isset($value['postback'])) {
                    $this->handlePostback($value, $client);
                }

                return response()->json(['send' => true]);
            } catch (\Throwable $e) {
                logError('Receive Response : ', $e);
                return response()->json([
                    'send' => false,
                    'error' => __('an_unexpected_error_occurred_please_try_again_later.'),
                    'data' => $request
                ]);
            }
        }

        return response()->json(['send' => false, 'error' => 'No active subscription or webhook token mismatch']);
    }




    private function handlePostback($value, $client)
    {
        $senderId = $value['sender']['id'];
        $payload = $value['postback']['payload'];
        $title = $value['postback']['title'] ?? '';
        
        $nodeData = Flow::where('data', 'LIKE', '%'.$payload.'%')->first();

        if (!$nodeData) {
            Log::error('No node data found for payload: ' . $payload);
            return;
        }

        //for postback messenger flow
        $flowData = Flow::where('data', 'LIKE', '%'.$payload.'%')->first();

        if (!$flowData) {
            return response()->json(['error' => 'Flow data not found'], 404);
        }

        // Extract the relevant buttons
        $buttons = collect($flowData->messages)
            ->where('type', 'box-with-button')
            ->flatMap(function ($message) {
                return $message['items'] ?? [];
            })
            ->toArray();

        // Save the data
        // $flowData->update([
        //     'buttons' => json_encode($buttons),
        //     'component_buttons' => json_encode($buttons),
        // ]);

        //for postback messenger flow end

        if (is_string($nodeData->data)) {
            $nodeDataArray = json_decode($nodeData->data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error:', [json_last_error_msg()]);
                return;
            }
        } else {
            // If the data is already an array, use it directly
            $nodeDataArray = $nodeData->data;
        }

        // Find the corresponding message based on the payload
        $responseMessage = $this->findMessageByPayload($nodeDataArray, $payload);

        // If no message is found, set a default response
        if (!$responseMessage) {
            Log::error('No response message found for payload:', [$payload]);
            $responseMessage = "Unknown button clicked.";
        }

        // Create and save the message in the database
        $message = new \App\Models\Message([
            'client_id' => $client->id,
            'contact_id' => 8, 
            'value' => $responseMessage,
            'message_type' => 'interactive',
            'status' => MessageStatusEnum::SENT->value, 
        ]);

        // Save the message to the database
        $message->save();

        // Send a response back to the user
        $this->sendMessengerMessage($message, 'text');
    }

    private function findMessageByPayload($nodeData, $payload)
    {
        if (isset($nodeData['messages'])) {
            foreach ($nodeData['messages'] as $message) {
                if ($message['type'] === 'box-with-button' && isset($message['items'])) {
                    foreach ($message['items'] as $item) {
                        if ($item['id'] === $payload) {
                            if (isset($nodeData['elements']['edges'])) {
                                foreach ($nodeData['elements']['edges'] as $edge) {
                                    if ($edge['sourceHandle'] === $payload . 'right') {
                                        $targetNodeId = $edge['target'];

                                        foreach ($nodeData['messages'] as $targetMessage) {
                                            if ($targetMessage['id'] === $targetNodeId) {
                                                // Ensure 'text' exists before accessing it
                                                return $targetMessage['text'] ?? null;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                // Handle 'text' message type
                elseif ($message['type'] === 'text' && $message['id'] === $payload) {
                    return $message['text'] ?? null; // Avoid undefined array key error
                }
            }
        }

        return null;
    }


    private function handleStatusUpdate($value, $client)
    {
        try {
            $campaign = null;
            if (isset($value['statuses'][0])) {
                $statusInfo = $value['statuses'][0];
                $message_id = $statusInfo['id'] ?? null;
                $conversation = $statusInfo['conversation'] ?? null;
                if (isset($conversation)) {
                    return ;
                }
                $message = $this->message->where('message_id', $message_id)->first();
                if ($message) {
                    $incomming_status = $statusInfo['status'] ?? null;
                    $message->status = $incomming_status;
                    $message->error = $statusInfo['errors'][0]['message'] ?? '';
                    $message->update();
                    if (!empty($message->campaign)) {
                        $campaign = $message->campaign;
                        if ($incomming_status === 'failed' && isset($statusInfo['errors'][0]['code'])) {
                            $error_code = $statusInfo['errors'][0]['code'];
                            if ($this->isErrorStoppingCampaign($error_code)) {
                                $campaign->status = StatusEnum::STOPPED;
                                $campaign->errors = $this->getErrorMessage($error_code);
                                $campaign->update();
                            }
                        }
                        // Update campaign metrics
                        switch ($incomming_status) {
                            case 'delivered':
                                if ($message->status !== 'read') {
                                    $campaign->total_delivered += 1;
                                }
                                break;
                            case 'sent':
                                if ($message->status !== 'delivered') {
                                    $campaign->total_sent += 1;
                                }
                                break;
                            case 'read':
                                $campaign->total_read += 1;
                                break;
                            case 'failed':
                                $campaign->total_failed += 1;
                                break;
                        }
                        $campaign->save();
                    }
                }
            } else {
                Log::info('handleStatusUpdate', ['No status info found']);
            }
        } catch (\Exception $e) {
            logError('handleStatusUpdate Exception: ', $e);
            return false;
        }
    }

    private function isErrorStoppingCampaign($error_code)
    {
        $stop_campaign_errors = config('static_array.stop_campaign_errors');
        return in_array($error_code, $stop_campaign_errors);
    }

    private function getErrorMessage($error_code)
    {
        $whatsapp_error = config('static_array.whatsapp_error');
        $index = array_search($error_code, array_column($whatsapp_error, 'code'));
        $description = $index !== false ? $whatsapp_error[$index]['description'] : 'Unknown Error';
        return $description;
    }

    private function handleIncomingMessage($value, $client, $instagramObj){

        try {
            // Check if required keys exist in the array
            if (!isset($value['message']) || !isset($value['sender']['id'])) {
                Log::info('Required keys are missing in the incoming message array.');
                throw new \Exception('Required keys are missing in the incoming message array.');
            }

            if(isset($value['message']['is_echo'])){
                $sender_contact = $value['recipient']['id'];
                $is_contact_msg = true;
            }else {
                $sender_contact = $value['sender']['id'];
                $is_contact_msg = true;
            }
            
            $type = $value['message']['attachments'][0]['type'] ?? 'text';
            
            if($instagramObj!=="instagram"){
              $sender_info = $this->getSenderName($sender_contact,$client);
            }else{

              $data = json_decode(json_encode($client), true);
              
              $instagramBusinessAcccountId = $data['instagram_setting']['business_account_id'] ?? null;
              $accessToken = $data['instagram_setting']['access_token'] ?? null;
              
            //   $url = 'https://graph.facebook.com/v22.0/' . $instagramBusinessAcccountId . '?fields=name,profile_picture_url&access_token=' . $accessToken;

            $url = 'https://graph.facebook.com/v22.0/' . $sender_contact . '?fields=name,profile_pic&access_token=' . $accessToken;
                            
              $ch  = curl_init($url);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
              curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
              $response     = curl_exec($ch);
              $instagramName = json_decode($response, true);             
            }
            
            $message_id = $value['message']['mid'];

            $contact = $this->contact
                ->where('client_id', $client->id)
                ->where(function ($query) use ($sender_contact) {
                    $query->where('contact_id', $sender_contact);
                })
                ->first();
                if (!empty($contact) && $contact->is_blacklist) {
                    Log::info('Incoming message from a blacklisted contact, not saving the message.', [$sender_contact]);
                    return;
                }
                                               

                if (!$contact) {
                    DB::beginTransaction();
                    
                    try {
                        
                        $contact = new Contact();
                        
                        if($instagramObj !== "instagram"){
                                                
                          $contact->name = $sender_info['first_name'].' '.$sender_info['last_name'];
                          
                          if($sender_info){

                              $imageContent = file_get_contents($sender_info['profile_pic']);
                              $fileName = 'fb_' . time() . '.jpg';
                              file_put_contents(public_path('images/' . $fileName), $imageContent);
                              $filePath = public_path('images/' . $fileName);
                              // Check if the file exists
                              if (file_exists($filePath)) {
                                  $uploadedFile = new UploadedFile(
                                      $filePath,
                                      basename($filePath), // Original name
                                      mime_content_type($filePath), // MIME type
                                      null, // Size (optional, null means it will be determined automatically)
                                      true // Set test mode to true since the file is already on the server
                                  );
                              }
  
                              $response     = $this->saveImage($uploadedFile, '_contact_');
                          }
                          
                          $contact->images     = $response['images'] ?? $contact->images;
                        
                        }else{
                            // $contact->name = $instagramName['name'];

                            if (isset($instagramName['name']) && !empty($instagramName['name'])) {
                                $contact->name = $instagramName['name'];
                            }else{
                                $contact->name = "Unknown";
                            }
                            
                            if (isset($instagramName['profile_pic']) && !empty($instagramName['profile_pic'])) {
                            
                                $imageContent = file_get_contents($instagramName['profile_pic']);
                                $fileName = 'fb_' . time() . '.jpg';
                                file_put_contents(public_path('images/' . $fileName), $imageContent);
                                $filePath = public_path('images/' . $fileName);
                                
                                if (file_exists($filePath)) {
                                    $uploadedFile = new UploadedFile(
                                        $filePath,
                                        basename($filePath), 
                                        mime_content_type($filePath), 
                                        null,
                                        true
                                    );
                                }
    
                                $instagramImage = $this->saveImage($uploadedFile, '_contact_');
                                
                                
                                $contact->images     = $instagramImage['images'] ?? $contact->images;
                            
                            }
                                
                          }
                        
                        $contact->phone = $sender_contact;
                        $contact->contact_id = $sender_contact;
                        $contact->client_id = $client->id;
                        //$contact->country_id = $this->whatsappService->extractCountryCode($phone);
                        $contact->has_conversation = 1;
                        $contact->is_verified = 1;
                        $contact->bot_reply = 1;
                        
                        if($instagramObj!=="instagram"){
                          $contact->type = TypeEnum::MESSENGER;
                        }else{
                          $contact->type = TypeEnum::INSTAGRAM;
                        }
                        
                        $contact->has_unread_conversation = 1;
                        $contact->last_conversation_at = now();
                        $contact->status = 1;
                        // $contact->images     = $response['images'] ?? $contact->images;
                        $contact->save();

                        $contactList = ContactsList::where('client_id', $client->id)->where('name', 'Uncategorized')->first();

                        if (empty($contactList)) {
                            $contactList = new ContactsList();
                            $contactList->name = 'Uncategorized';
                            $contactList->client_id = $client->id;
                            $contactList->save();
                        }

                        ContactRelationList::firstOrCreate([
                            'contact_id' => $contact->id,
                            'contact_list_id' => $contactList->id,
                        ]);

                        $defaultSegment = Segment::firstOrCreate([
                            'client_id' => $client->id,
                            'title' => 'Default',
                        ], [
                            'client_id' => $client->id,
                            'title' => 'Default',
                        ]);

                        ContactRelationSegments::firstOrCreate([
                            'contact_id' => $contact->id,
                            'segment_id' => $defaultSegment->id,
                        ]);

                        DB::commit();
                        
                    } catch (\Throwable $th) {
                        logError('Duplicate contact : ', $th);
                        DB::rollBack();
                    }
                    
                } else {
                    $contact->update([
                        'contact_id' => $sender_contact,
                        'is_verified' => 1,
                        'has_conversation' => 1,
                        'has_unread_conversation' => 1,
                        'last_conversation_at' => now(),
                    ]);
                }

                $content = $value;
                
                $is_campaign_msg = false;
                $this->saveIncommingMessage($contact, $content, $client, $is_contact_msg, $is_campaign_msg, $type, $message_id, $instagramObj);

        } catch (\Throwable $th) {
            
        }

    }

    private function saveIncommingMessage($contact, $content, $client, $is_contact_msg, $is_campaign_msg, $type, $message_id, $instagramObj){

        try {
            $existingMessage = Message::where('message_id', $message_id)->first();

            if ($existingMessage) {
                
                if (($instagramObj == "instagram") && isset($content['message']['is_deleted'])) {
                    Log::info('INSTAGRAM MESSAGE DELETED', [$content]);
                
                    $message_id = $content['message']['mid'] ?? null;
                
                    if ($message_id) {
                        
                      $existingMessage->delete();
                      Log::info("Deleted Instagram message from DB", ['message_id' => $message_id]);
                      
                      if (setting('is_pusher_notification_active')) {
                        event(new \App\Events\ReceiveUpcomingMessage($client));
                      }
                      
                      return false;
                         
                    }
                }
                
                Log::info('Message with the same message_id already exists', ['message_id' => $message_id]);
                
                return false;
            }

            $message = new Message();
            $message->contact_id = $contact->id;
            $message->message_id = $message_id;
            $message->client_id = $client->id;
            $notified_message = '';
            
            if ($type == 'image') {
                $response = $this->handleReceivedMedia($content['message']['attachments'][0]['payload']['url']);
                $message->header_image = $response;
                $notified_message = __('sent_an_image');
            }  elseif ($type == 'audio') {
                $response = $this->handleReceivedMedia($content['message']['attachments'][0]['payload']['url']);
                $message->header_audio = $response;
                $notified_message = __('sent_an_audio_file');
            } elseif ($type == 'video') {
                $response = $this->handleReceivedMedia($content['message']['attachments'][0]['payload']['url']);
                $message->header_video = $response;
                $notified_message = __('sent_a_video');
            } elseif ($type == 'file') {
                $response = $this->handleReceivedMedia($content['message']['attachments'][0]['payload']['url']);
                $message->header_document = $response;
                $notified_message = __('sent_a_video');
            } elseif ($type == 'text') {
                $response = $content['message']['text'];
                $message->value = $response;
                $notified_message = $response;
            }  elseif ($type == 'button') {

                $buttonsData = $content['messages'][0]['button'];
                $formattedButtons = [];
                if (isset($buttonsData)) {
                    $formattedButtons[] = [
                        'type' => $content['messages'][0]['type'],
                        'payload' => $buttonsData['payload'] ?? '',
                        'text' => $buttonsData['text'] ?? ''
                    ];
                }
                $message->buttons = json_encode($formattedButtons);
                $notified_message = $content['messages'][0]['button']['text'];
            } elseif ($type == 'interactive') {
                
                $buttonsData = $content['messages'][0]['interactive'];
                $formattedButtons = [];
                if (isset($buttonsData['button_reply'])) {
                    $formattedButtons[] = [
                        'type' => $buttonsData['type'],
                        'id' => $buttonsData['button_reply']['id'] ?? '',
                        'text' => $buttonsData['button_reply']['title'] ?? ''
                    ];
                }
                $message->buttons = json_encode($formattedButtons);
                $notified_message = $buttonsData['button_reply']['title'];
            } 
            else if ($type == 'unsupported') {
                $response = __('message_type_is_currently_not_supported');
                $message->value = $response;
                $notified_message = $response;
                $message->error = $response;
            }
            if (isset($content['messages'][0]['context'])) {
                $message->context_id = $content['messages'][0]['context']['id'];
            }

            $message->message_type = $type;
            $message->components = null;
            $message->campaign_id = null;
            $message->is_contact_msg = $is_contact_msg;
            $message->is_campaign_msg = $is_campaign_msg;
            $message->source = TypeEnum::MESSENGER;
            $message->status = MessageStatusEnum::DELIVERED;
            $message->save();
            // Update status if needed
            $message->status = MessageStatusEnum::DELIVERED;
            $message->update();
            if (setting('is_pusher_notification_active')) {
                event(new \App\Events\ReceiveUpcomingMessage($client));
            }

            $contact->update([
                'last_conversation_at' => now(),
                'has_conversation' => 1,
                'has_unread_conversation' => 1
            ]);

            // if ($message && $contact->bot_reply) {
            if (!empty($message) && $contact->bot_reply==1) {
                $this->QuickReply($message);
            }

            return true;

        } catch (\Exception $e) {
            logError('Save Incoming Message Exception: ', $e);
            return false;
        }
    }
}


?>