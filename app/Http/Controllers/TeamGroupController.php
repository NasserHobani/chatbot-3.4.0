<?php

namespace App\Http\Controllers;

use App\DataTables\Client\TeamGroupDataTable;
use App\Models\TeamGroup;
use App\Repositories\ClientRepository;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Repositories\UserRepository;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TeamGroupController extends Controller
{
    protected $client;

    protected $user;


    public function __construct(UserRepository $userRepository,ClientRepository $client)
    {
        $this->user = $userRepository;


    }

    public function index(TeamGroupDataTable $dataTable)
    {
        Log::info("get index team group");

        try {
            return $dataTable->render('backend.client.team_group.index');
        } catch (\Exception $e) {
            Toastr::error('something_went_wrong_please_try_again');
            return back();
        }
    }

    public function get_all()
    {
        Log::info("get all team group");
        return TeamGroup::all();
    }

    public function create(Request $request)
    {
        try {
            return view('backend.client.team_group.create');
        } catch (Exception $e) {
            Toastr::error('something_went_wrong_please_try_again');
            return back();
        }
    }

    public function store(Request $request, UserRepository $userRepository): JsonResponse
    {
        if (isDemoMode()) {
            $data = [
                'status' => false,
                'error' => __('this_function_is_disabled_in_demo_server'),
                'title' => 'error',
            ];
            return response()->json($data);
        }

        DB::beginTransaction();
        try {
            // Get the client and its active subscription
            $client = auth()->user()->client;

            $activeSubscription = $client->activeSubscription;
            if (!$activeSubscription) {
                return response()->json([
                    'status' => false,
                    'message' => __('do_not_have_active_subscription_team_members'),
                    'title' => 'error',
                ]);
            }
            $request['client_id'] = $client->id;
            TeamGroup::create(array('name'=>$request['name'],'client_id'=>$client->id));


            DB::commit();

            Toastr::success(__('create_successful'));
            return response()->json([
                'status' => true,
                'success' => __('create_successful'),
                'route' => route('client.team-group.index'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'error' => __('something_went_wrong_please_try_again')]);
        }
    }

    public function edit($id): View|Factory|RedirectResponse|Application
    {
        try {
            $team_group = TeamGroup::where('id', $id)->first();
            $data = [
                'team' => $team_group,
                'staff' => auth()->user()->client->staff,
            ];
            Log::info("get edit team group");
            Log::info($team_group->staff);
            return view('backend.client.team_group.edit', $data);
        } catch (Exception $e) {
            Toastr::error('something_went_wrong_please_try_again');
            return back();
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();
        try {

            $team = TeamGroup::find($id);
            $team->staff()->sync($request->get("staff_select"));
            $team->update($request->all("name"));
            DB::commit();
            Toastr::success(__('update_successful'));
            return response()->json(['success' => __('update_successful'), 'route' => route('client.team-group.index')]);
        } catch (Exception $e) {
            DB::rollBack();
            if (config('app.debug')) {
                dd($e->getMessage());
            }
            return response()->json(['status' => false, 'error' => __('something_went_wrong_please_try_again')]);
        }
    }

}
