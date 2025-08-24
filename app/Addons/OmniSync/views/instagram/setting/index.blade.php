@extends('backend.layouts.master')
@section('title', __('instagram_settings'))
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="section-title">{{ __('instagram_settings') }}</h3>
                <div class="default-tab-list default-tab-list-v2  bg-white redious-border p-20 p-sm-30 mb-4">
                    @if (instagramConnected())
                        <div class="alert alert-success" role="alert">
                            {{ __('success_!_you_are_now_connected_to_instagram_cloud_api') }}
                        </div>
                    @else
                        <div class="alert alert-danger" role="alert">
                            {{ __('not_connected!_please_complete_all_the_steps_in_order_to_connect_to_instagram_api') }}
                        </div>
                    @endif
                    
                    @if (!isinstagramAppWebhookConnected())
                        <div class="alert alert-danger" role="alert">
                            <strong>{{ __('oops') }}</strong> {{ __('instagram_webhook_not_connected') }}<br>
                            <small>{{ __('real_time_updates_will_not_be_available_until_the_webhook_is_connected') }}</small>
                            @if (
                                !empty(@Auth::user()->client->instagramSetting->access_token) &&
                                    !empty(@Auth::user()->client->instagramSetting->scopes))
                                <a class="alert-link" href="https://developers.facebook.com/apps/{{ Auth::user()->client->instagramSetting->app_id }}/whatsapp-business/wa-settings/?business_id={{ Auth::user()->client->instagramSetting->business_account_id }}"
                                    target="_blank">
                                    <i class="las la-link"></i> {{ __('add_instagram_webhook') }}
                                </a>
                            @endif
                        </div>
                    @endif
                    <div class="row row-eq-height mb-3">
                        <div class="col-lg-6">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>{{ __('webhook_config') }}</h5>
                                    @if (setting('whatsApp_settings_video_url'))
                                        <a href="#" class="d-flex align-items-center gap-2" data-bs-toggle="modal"
                                            data-bs-target="#config_tutorial">
                                            <i class="lab la-youtube" style="font-size:24px;"></i> {{ __('tutorial') }}
                                        </a>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <label for="webhook_callback_url" class="form-label"><i class="las la-link"></i>
                                            {{ __('callback_url') }}</label>
                                        <div class="input-group">
                                            <input type="url"
                                                value="{{ isDemoMode() ? '******************' : route('messenger.webhook', @Auth::user()->client->webhook_verify_token) }}"
                                                readonly name="webhook_callback_url" class="form-control"
                                                placeholder="{{ __('enter_webhook_callback_url') }}"
                                                aria-label="{{ __('enter_webhook_callback_url') }}"
                                                aria-describedby="webhook_callback_url">
                                            <span class="input-group-text copy-text" id="webhook_callback_url">
                                                <i class="las la-copy"></i>
                                            </span>
                                        </div>
                                        <div class="nk-block-des text-danger">
                                            <p class="first_name_error error">
                                                {{ $errors->first('webhook_callback_url') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="webhook_verify_token" class="form-label"><i class="las la-key"></i>
                                            {{ __('verify_token') }}</label>
                                        <div class="input-group">
                                            <input type="text"
                                                value="{{ isDemoMode() ? '******************' : old('webhook_verify_token', @Auth::user()->client->webhook_verify_token) }}"
                                                readonly name="webhook_verify_token" class="form-control"
                                                placeholder="{{ __('enter_webhook_verify_token') }}"
                                                aria-label="{{ __('enter_webhook_verify_token') }}"
                                                aria-describedby="webhook_verify_token">
                                            <span class="input-group-text copy-text" id="webhook_verify_token">
                                                <i class="las la-copy"></i>
                                            </span>
                                        </div>
                                        <div class="nk-block-des text-danger">
                                            <p class="first_name_error error">
                                                {{ $errors->first('webhook_verify_token') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5><i class="las la-cloud"></i> {{ __('get_started_with_instagram_api') }}</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li><i class="las la-check-circle"></i> {{ __('1') }}.
                                            {{ __('sign_up_for_a_developer_account_and_create_a_new_facebook_app_by_following_the_instructions_provided_on_the_facebook_developer_website_facebook_developer') }}
                                            <a href="https://developers.facebook.com/" class="lern-more"
                                                target="_blank">Facebook Developer</a>.
                                        </li>
                                        <li><i class="las la-check-circle"></i> {{ __('2') }}.
                                            {{ __('once_your_facebook_app_is_created_navigate_to_the_app_dashboard_and_find_the_messenger_product_section_click_on_setup') }}
                                        </li>
                                        <li><i class="las la-check-circle"></i>{{ __('3') }}.
                                            {{ __('in_the_instagram_settings_enter_the_required_information_including_your_webhook_callback_url_and_verify_token') }} {{ __('callback_url') }} and
                                            {{ __('verify_token') }}.</li>
                                        <li><i class="las la-check-circle"></i> {{ __('4') }}.
                                            {{ __('go_to_webhook_fields_and_select_messages_under_manage_to_enable_message_handling') }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form action="{{ url('client/instagram/settings/update') }}" method="POST" enctype="multipart/form-data" class="form">
                        @csrf
                        <input type="hidden" class="is_modal" value="0" />
                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5><i class="las la-key"></i> {{ __('instagram_access_token') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-4">
                                            <label for="access_token" class="form-label">
                                                {{ __('access_token') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control rounded-2" id="access_token"
                                                name="access_token"
                                                value="{{ isDemoMode() ? '******************' : old('access_token', @Auth::user()->client->instagramSetting->access_token) }}"
                                                placeholder="{{ __('enter_access_token') }}">
                                            <div class="invalid-feedback nk-block-des text-danger">
                                                <p class="access_token_error error">{{ $errors->first('access_token') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label for="app_id" class="form-label"> {{ __('app_id') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control rounded-2" id="app_id"
                                                name="app_id"
                                                value="{{ isDemoMode() ? '******************' : old('app_id', @Auth::user()->client->instagramSetting->app_id) }}"
                                                placeholder="{{ __('enter_app_id') }}">
                                            <div class="invalid-feedback nk-block-des text-danger">
                                                <p class="app_id_error error">{{ $errors->first('app_id') }}</p>
                                            </div>
                                        </div>
                                        @if (@Auth::user()->client->instagramSetting)
                                            <div class="p-2 bg-light">
                                                @if (!empty(@Auth::user()->client->instagramSetting->access_token))
                                                    <h5 class="font-weight-bold mb-3">{{ __('business_account_details') }}
                                                    </h5>

                                                    <div class="mb-2">
                                                        <strong><i class="las la-user-circle"></i>
                                                            {{ __('page_name') }}:</strong>
                                                        <span
                                                            class="text-muted">{{ @Auth::user()->client->instagramSetting->business_account_name }}</span>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong><i class="las la-user-edit"></i>
                                                            {{ __('page_id') }}:</strong>
                                                        <span
                                                            class="text-muted">{{ Auth::user()->client->instagramSetting->business_account_id }}</span>
                                                    </div>

                                                    {{-- <div class="mb-2">
                                                    <strong>{{ __('name') }}:</strong>
                                                    {{ @Auth::user()->client->whatsappSetting->name }}
                                                    <a class="text-success" target="_blank"
                                                        href="https://business.facebook.com/wa/manage/home/?business_id=&waba_id={{ Auth::user()->client->whatsappSetting->business_account_id }}"
                                                        class="small">
                                                        <small>
                                                            <i class="las la-pen"></i>
                                                            {{ __('manage') }}
                                                        </small>
                                                    </a>
                                                </div> --}}
                                                    @if (!empty(Auth::user()->client->instagramSetting->expires_at))
                                                        {{-- <div class="mb-2">
                                                        <strong>{{ __('token_expires_at') }}:</strong>
                                                        {{ Auth::user()->client->whatsappSetting->expires_at }}
                                                    </div> --}}
                                                    @endif
                                                    {{-- <div class="mb-2">
                                                    <strong>{{ __('app_id') }}:</strong>
                                                    {{ Auth::user()->client->whatsappSetting->app_id }}
                                                </div> --}}
                                                    {{-- <div class="mb-2">
                                                    <strong>{{ __('phone_number_id') }}:</strong>
                                                    {{ Auth::user()->client->whatsappSetting->phone_number_id }}
                                                </div> --}}

                                                    <?php
                                                    $scopes = @Auth::user()->client->instagramSetting->scopes ?? [];
                                                    $requiredScopes = config('static_array.whatsapp_required_scopes');
                                                    ?>
                                                    {{-- <ul class="list-inline">
                                                    @foreach ($scopes as $scope)
                                                        <li class="list-inline-item">
                                                            @if (in_array($scope, $requiredScopes))
                                                                <i class="las la-check-circle text-success"></i>
                                                            @else
                                                                <i class="las la-check-circle text-success"></i>
                                                            @endif
                                                            {{ $scope }}
                                                        </li>
                                                    @endforeach
                                                    @foreach ($requiredScopes as $requiredScope)
                                                        @if (!in_array($requiredScope, $scopes))
                                                            <li class="list-inline-item">
                                                                <i class="las la-times-circle text-danger"></i>
                                                                {{ $requiredScope }}
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul> --}}

                                                    @foreach (Auth::user()->client->instagramSetting->details as $details)
                                                        <div class="mb-3">
                                                            <div class="mb-2">
                                                                <strong><i class="las la-phone"></i>
                                                                    {{ __('phone_number') }}:</strong>
                                                                <span
                                                                    class="text-muted">{{ $details->display_phone_number ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="mb-2">
                                                                <strong><i class="las la-check-circle"></i>
                                                                    {{ __('verified_name') }}:</strong>
                                                                <span
                                                                    class="text-muted">{{ $details->verified_name ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="mb-2">
                                                                <strong><i class="las la-id-card"></i>
                                                                    {{ __('phone_number_id') }}:</strong>
                                                                <span
                                                                    class="text-muted">{{ $details->phone_number_id ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="mb-2">
                                                                <strong><i class="las la-star"></i>
                                                                    {{ __('quality_rating') }}:</strong>
                                                                <span
                                                                    class="text-muted">{{ $details->quality_rating ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="mb-2">
                                                                <strong><i class="las la-comments"></i>
                                                                    {{ __('message_limit') }}:</strong>
                                                                <span
                                                                    class="text-muted">{{ $details->message_limit ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="mb-2">
                                                                <strong><i class="las la-info-circle"></i>
                                                                    {{ __('number_status') }}:</strong>
                                                                <span
                                                                    class="text-muted">{{ $details->number_status ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="mb-2">
                                                                <strong><i class="las la-code"></i>
                                                                    {{ __('code_verification_status') }}:</strong>
                                                                <span
                                                                    class="text-muted">{{ $details->code_verification_status ?? 'N/A' }}</span>
                                                            </div>
                                                            <div class="mb-2">
                                                                <strong><i class="las la-check-circle"></i>
                                                                    {{ __('account_review_status') }}:</strong>
                                                                <span
                                                                    class="text-muted">{{ $details->account_review_status ?? 'N/A' }}</span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                                {{-- @if (@Auth::user()->client->whatsappSetting)
                                                <button type="button" class="btn btn-secondary btn-sm" id="sync_button"
                                                    data-id="{{ @Auth::user()->client->whatsappSetting->id }}"
                                                    data-url="{{ route('client.whatsapp.embedded-signup.sync', @Auth::user()->client->whatsappSetting->id) }}">
                                                    <i class="las la-sync-alt"></i>
                                                </button>
                                            @endif
                                            <a class="btn btn-success btn-sm __js_edit"
                                                data-url="{{ route('client.whatsapp.profile.edit', @Auth::user()->client->whatsappSetting->id) }}"
                                                href="javascript:void(0)" class="small">
                                                <small>
                                                    <i class="las la-pen"></i>
                                                </small>
                                            </a>
                                            @if (@Auth::user()->client->whatsappSetting->access_token)
                                                <button type="button" class="btn btn-danger btn-sm text-white __js_delete"
                                                    id="remove_access_token"
                                                    data-id="{{ @Auth::user()->client->whatsappSetting->id }}"
                                                    data-url="{{ route('client.whatsapp.embedded-signup.delete', @Auth::user()->client->whatsappSetting->id) }}">
                                                    <i class="las la-trash-alt"></i>
                                                </button>
                                            @endif --}}
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-end align-items-center mt-30 gap-2">
                                            @if (@Auth::user()->client->instagramSetting->access_token)
                                                <button type="button" class="btn btn-danger text-white __js_delete"
                                                    id="remove_access_token"
                                                    data-id="{{ @Auth::user()->client->instagramSetting->id }}"
                                                    data-url="{{ route('client.instagram.remove-token', @Auth::user()->client->instagramSetting->id) }}">
                                                    <i class="las la-trash-alt"></i> {{ __('remove') }}
                                                </button>
                                            @endif
                                            @if (@Auth::user()->client->instagramSetting)
                                                <button type="button" class="btn btn-secondary" id="sync_button"
                                                    data-id="{{ @Auth::user()->client->instagramSetting->id }}"
                                                    data-url="{{ route('client.instagram-settings.sync', @Auth::user()->client->instagramSetting->id) }}">
                                                    <i class="las la-sync-alt"></i> {{ __('sync') }}
                                                </button>
                                            @endif
                                            <button type="submit" class="btn sg-btn-primary"><i class="las la-save"></i>
                                                {{ __('save') }}</button>
                                            @include('backend.common.loading-btn', [
                                                'class' => 'btn sg-btn-primary',
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5><i class="las la-key"></i> {{ __('get_your_permanent_access_token') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            <li>
                                                <i class="las la-info-circle"></i>
                                                {{ __('follow_the_detailed_process_outlined_in_the_facebook_docs_to_create_a_permanent_access_token') }}
                                                <a class="lern-more"
                                                    href="https://developers.facebook.com/docs/whatsapp/business-management-api/get-started#1--acquire-an-access-token-using-a-system-user-or-facebook-login"
                                                    target="_blank">
                                                    {{ __('facebook_docs') }}
                                                </a>
                                            </li>
                                            <li>
                                                <i class="las la-key"></i>
                                                {{ __('once_you_have_obtained_the_permanent_access_token_enter_it_here') }}
                                            </li>
                                        </ul>
                                        <h5><i class="las la-id-card"></i>
                                            {{ __('get_Your_account_id_and_phone_number_id') }}</h5>
                                        <ul class="list-unstyled mb-4">
                                            <li><i class="las la-check-circle"></i> {{ __('1') }}.
                                                {{ __('setting_line_one') }}</li>
                                            <li><i class="las la-check-circle"></i> {{ __('2') }}.
                                                {{ __('setting_line_two') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade modal-lg" id="config_tutorial" tabindex="-1" aria-labelledby="config_tutorial"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="embed-responsive videowrapper">
                    <iframe class="embed-responsive-item"
                        src="https://www.youtube.com/embed/{{ getYoutubeVideoId(setting('whatsApp_settings_video_url')) }}"
                        title="YouTube video player" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.7/axios.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.copy-text').click(function() {
                var inputField = $(this).closest('.input-group').find('input');
                inputField.select();
                document.execCommand("copy");
                toastr.success("{{ __('copied') }}");
            });
        });
        $(document).on('click', '.__js_delete', function() {
            confirmationAlert(
                $(this).data('url'),
                $(this).data('id'),
                'Yes, Delete It!'
            );
        });

        const confirmationAlert = (url, data_id, button_text = 'Yes, Confirmed it!') => {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: button_text,
                confirmButtonColor: '#ff0000',
                preConfirm: () => {
                    // Show the loading spinner
                    Swal.showLoading();

                    return axios.post(url, {
                            data_id: data_id
                        })
                        .then(response => {
                            console.log(response);
                            // Close the loader and show the success/error message
                            Swal.fire({
                                title: response.data.message,
                                icon: response.data.status ? 'success' : 'error'
                            }).then(() => {
                                if (response.data.status) {
                                    location.reload();
                                }
                            });
                        })
                        .catch(error => {
                            console.log(error);
                            // Show error message
                            Swal.fire('Error', error.response.data.message || 'An error occurred.',
                                'error');
                        });
                }
            });
        };

        $('#sync_button').click(function() {
            var button = $(this);
            var url = button.data('url');
            // Change cursor to progress
            $("body").css("cursor", "progress");

            button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' +
                '{{ __('syncing') }}');

            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    id: button.data('id')
                },
                success: function(data) {
                    // Reset cursor to default
                    $("body").css("cursor", "default");
                    button.html('<i class="las la-sync-alt"></i> {{ __('sync') }}');
                    if (data.status) {
                        toastr.success(data.message);
                        location.reload();
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function(xhr, status, error) {
                    // Reset cursor to default
                    $("body").css("cursor", "default");
                    button.html('<i class="las la-sync-alt"></i> {{ __('sync') }}');
                    console.error('Error:', error);
                    toastr.error("{{ __('an_error_occurred_while_processing_the_request') }}");
                }
            });
        });
    </script>
@endpush
