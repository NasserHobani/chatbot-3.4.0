<?php

namespace App\Addons\OmniSync\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Repositories\Client\ContactRepository;
use App\Repositories\Client\MessageRepository;
use App\Traits\CommonTrait;
use App\Traits\RepoResponse;
use App\Traits\SendMailTrait;
use App\Traits\SendNotification;
use Aws\Api\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Json;

class MessageController extends Controller
{
    use CommonTrait, RepoResponse, SendMailTrait, SendNotification;

    protected $repo;
    protected $contactRepository;
    protected $contact;
    protected $message;
    protected $messageModel;

    public function __construct(
        MessageRepository $repo,
        ContactRepository $contactRepository,
        ContactRepository $contact,
        Message $messageModel,
    ) {
        $this->repo              = $repo;
        $this->contactRepository = $contactRepository;
        $this->contact           = $contact;
        $this->messageModel      = $messageModel;
    }

   public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message'     => 'required_without_all:image,document',
            'receiver_id' => 'required',
            'image'       => 'required_without_all:message,document',
            'document'    => 'required_without_all:message,image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()], 422);
        }

        DB::beginTransaction();

        $client = auth()->user()->client;

        try {

            $conversation_id = $this->conversationUpdate(auth()->user()->client_id, $request->receiver_id);
            $contact = $this->contactRepository->find($request->receiver_id);

            if ($request->file('image') !== null) {
                $this->repo->sendImageMessage($request, $request->receiver_id, $contact->type, $conversation_id);
            } elseif (! empty($request->message)) {
                $this->repo->sendTextMessage($request, $request->receiver_id, $contact->type, $conversation_id);
            } else {
                return response()->json([
                    'error' => __('oops...Something Went Wrong'),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => __('message_sent_successfully'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            logError('Error: ', $e);

            return response()->json(['status' => false, 'error' => $e->getMessage()]);
        }
    }

}
?>