<?php
namespace App\Http\Controllers\Client;
use App\Http\Controllers\Controller;
use App\Models\OneSignalToken;
use App\Models\TeamGroup;
use App\Repositories\UserRepository;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $user;

    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    public function statusChange(Request $request): JsonResponse
    {
        if (isDemoMode()) {
            $data = [
                'status'  => 'danger',
                'message' => __('this_function_is_disabled_in_demo_server'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }
        try {
            $this->user->statusChange($request->all());
            $data = [
                'status'  => 'success',
                'message' => __('update_successful'),
                'title'   => 'success',
            ];

            return response()->json($data);
        }catch (\Exception $e) {
            $data = [
                'status'  => 'danger',
                'message' => __('something_went_wrong_please_try_again'),
                'title'   => __('error'),
            ];

            return response()->json($data);
        }
    }

    public function instructorVerified($id): RedirectResponse
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));

            return back();
        }
        try {
            $response = $this->user->userVerified($id);
            Toastr::success(__($response['message']));

            return redirect()->back();
        }catch (\Exception $e) {
            Toastr::error('something_went_wrong_please_try_again');

            return redirect()->back();
        }
    }

    public function instructorBan($id): RedirectResponse
    {
        if (isDemoMode()) {
            Toastr::error(__('this_function_is_disabled_in_demo_server'));

            return back();
        }
        try {
            $response = $this->user->userBan($id);
            Toastr::success(__($response['message']));

            return redirect()->back();
        }catch (\Exception $e) {
            Toastr::error('something_went_wrong_please_try_again');

            return redirect()->back();
        }
    }

    public function instructorDelete($id): JsonResponse|RedirectResponse
    {
        if (isDemoMode()) {
            $data = [
                'status'  => 'danger',
                'message' => __('this_function_is_disabled_in_demo_server'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }
        try {
            $response = $this->user->userDelete($id);

            $data     = [
                'status'  => 'success',
                'message' => __($response['message']),
                'title'   => 'success',
            ];

            return response()->json($data);
        }catch (Exception $e) {
            $data = [
                'status'  => 'danger',
                'message' => __('something_went_wrong_please_try_again'),
                'title'   => 'error',
            ];

            return response()->json($data);
        }
    }

    public function oneSignalSubscription(Request $request)
    {
        try {
            $this->user->oneSignalSubscription($request->onesignal_token);

            return response()->json(['success' => true]);
        }catch (\Exception $e) {
            return response()->json(['status' => false,'error' => __('something_went_wrong_please_try_again')]);
        }
    }

    public function oneSignalNotification()
    {
        $headers    = [
            'Authorization' => 'Basic '.setting('onesignal_rest_api_key'),
            'accept'        => 'application/json',
            'content-type'  => 'application/json',
        ];

        $contact_id = 3;

        $ids        = OneSignalToken::where('client_id', auth()->user()->client_id)->pluck('subscription_id')->toArray();
        $body       = [
            'include_player_ids' => $ids,
            'contents'           => [
                'en' => 'How are you?',
            ],
            'headings'           => [
                'en' => 'Hello',
            ],
            'app_id'             => setting('onesignal_app_id'),
            'url'                => route('client.chat.index', ['contact' => $contact_id]),
        ];

        $response   = httpRequest('https://onesignal.com/api/v1/notifications', $body, $headers);

    }


    public function testTeam() {
    
	    $team = TeamGroup::where(['id' => 1])->first();
	    $staffs= $team->staff;
	    $min_contacts = null;
	    $staff_id = null;
	    foreach ($staffs as $staff) {
		    print_r("Staff Id " . $staff->user_id); echo "<br />";
		    $contact_count = $staff->contacts()->count();
		    print_r($contact_count); echo "<br />";
    		if ($min_contacts === null || $contact_count < $min_contacts) {
    		    $min_contacts = $contact_count;
		    $staff_user = $staff;
        	    $staff_id = $staff->user_id;
		}
		    print_r("Staff Id is:");
		    print_r( $staff->user_id); echo "<br />";
	    }   

	   print_r($min_contacts); echo "<br />"; 
	    print_r($staff_id);
    }
}
