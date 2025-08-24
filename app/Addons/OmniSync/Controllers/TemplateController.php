<?php

namespace App\Addons\OmniSync\Controllers;

use App\Addons\OmniSync\DataTable\Client\TemplateDataTable;
use App\Addons\OmniSync\Repository\TemplateRepository;
use App\Addons\OmniSync\Requests\TemplateStoreRequest;
use App\Addons\OmniSync\Services\MessengerService;
use App\Addons\OmniSync\Services\TemplateService;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Traits\RepoResponse;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TemplateController extends Controller
{
    use RepoResponse;
    protected $repo;

    protected $messengerService;

    public function __construct(TemplateRepository $repo, MessengerService $messengerService)
    {
        $this->repo            = $repo;
        $this->messengerService = $messengerService;
    }

    public function index(TemplateDataTable $templateDataTable)
    { 
        return $templateDataTable->render('addon:OmniSync::template.index');
    }


    public function create()
    {
        $data['locales'] =  Language::pluck('name','locale');
        return view('addon:OmniSync::template.create',$data);
    }

    public function store(TemplateStoreRequest $request)
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));
            return back();
        }

        $result = $this->repo->store($request);

        if ($result->status) {
            return response()->json($result, 200);
        }
        return response()->json($result, 200);
    }

    public function edit($id)
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));
            return back();
        }
        
        $row =  $this->repo->find($id);
        $data = app(TemplateService::class)->execute($row);

        return view('addon:OmniSync::template.edit', $data);
    }

    public function update(TemplateStoreRequest $request, $id)
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));
            return back();
        }
        $result = $this->repo->update($request,$id);
        if ($result->status) {
            return response()->json($result, 200);
        }
        return response()->json($result, 200);
    }

    public function delete($id)
    {
        if (isDemoMode()) {
            $data = [
                'status'  => 'danger',
                'message' => __('this_function_is_disabled_in_demo_server'),
                'title'   => 'error',
            ];
            return response()->json($data);
        }
        return $this->repo->destroy($id);
    }
}

?>