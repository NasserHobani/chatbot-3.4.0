<?php 

namespace App\Addons\OmniSync\Controllers\Webhook;

use App\Addons\OmniSync\Repository\Webhook\MessengerRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessengerWebhookController extends Controller
{
    protected $messengerRepo;

    public function __construct(MessengerRepository $messengerRepo)
    {
        $this->messengerRepo = $messengerRepo;
    }

    public function verifyToken(Request $request,$token)
    {
        return $this->messengerRepo->verifyToken($request,$token);
    }

    public function receiveResponse(Request $request,$token)
    {
        return  $this->messengerRepo->receiveResponse($request,$token);
    }
}
