<?php

namespace App\Addons\OmniSync\Controllers;

use App\Addons\OmniSync\DataTable\Client\TemplateDataTable;
use App\Addons\OmniSync\Repository\CampaignRepository;
use App\Addons\OmniSync\Repository\TemplateRepository;
use App\Addons\OmniSync\Requests\TemplateStoreRequest;
use App\Addons\OmniSync\Services\MessengerService;
use App\Addons\OmniSync\Services\TemplateService;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Repositories\Client\ContactListRepository;
use App\Repositories\Client\ContactRepository;
use App\Repositories\Client\SegmentRepository;
use App\Traits\RepoResponse;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class MessengerCampaignController extends Controller
{
    protected $repo;

    protected $templateRepo;

    protected $contactListsRepo;

    protected $ContactsRepo;

    protected $segmentsRepo;

    protected $campaignsRepo;
    
    protected $whatsappService;

    public function __construct(
        CampaignRepository $repo,
        TemplateRepository $templateRepo,
        ContactListRepository $contactListsRepo,
        ContactRepository $ContactsRepo,
        SegmentRepository $segmentsRepo,
        CampaignRepository $campaignsRepo,
    ) {
        $this->repo             = $repo;
        $this->templateRepo     = $templateRepo;
        $this->contactListsRepo = $contactListsRepo;
        $this->ContactsRepo     = $ContactsRepo;
        $this->segmentsRepo     = $segmentsRepo;
        $this->campaignsRepo    = $campaignsRepo;
    }

    public function sendTemplate(Request $request)
    {
        try {

            $data =[];
            $template  = $this->templateRepo->find($request->template_id);

            $data = app(TemplateService::class)->execute($template);
            $data['contact_id'] = $request->contact_id;
            
            return view('addon:OmniSync::template.contact_template', $data);

        } catch (\Exception $e) {

            Toastr::error('something_went_wrong_please_try_again');
            if (config('app.debug')) {
                dd($e->getMessage());            
            }

            return back();

        }
    }

    public function storeContactTemplate(Request $request)
    {
        
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));
            return back();
        }

        $result = $this->repo->ContactTemplateStore($request);

        if ($result->status) {
            return redirect()->route('client.chat.index', ['contact' => $request->contact_id])->with($result->redirect_class, $result->message);
        }

        return back()->with($result->redirect_class, $result->message);
    }
}

?>