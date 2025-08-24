@extends('backend.layouts.master')
@section('title', __('templates'))
@section('content')
    @push('css_asset')
        <link rel="stylesheet" href="{{ static_asset('admin/css/devices.min.css') }}">
        <link rel="stylesheet" href="{{ static_asset('admin/css/template.css') }}">
    @endpush
    <section class="oftions">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col col-lg-12 col-md-12">
                    <div class="d-flex align-items-center justify-content-between mb-12">
                        <h3 class="section-title">{{ __('create_messenger_template') }}</h3>
                        <div class="d-flex align-items-center gap-2">
                            <div>
                                <a href="{{ route('client.messenger.templates.index') }}"
                                    class="d-flex align-items-center btn sg-btn-primary gap-2">
                                    <i class="las la-list-alt"></i>
                                    <span>{{ __('template_lists') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white redious-border p-20 p-sm-30 pt-sm-30">
                        <div class="row">
                            <div class="col-lg-8">
                                <form method="POST" action="{{ route('client.messenger.template.store') }}"
                                    id="whatsapp-template-form" enctype="multipart/form-data">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="template_name">{{ __('template_name') }}
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control" id="template_name"
                                                    name="template_name" placeholder="{{ __('enter_template_name') }}"
                                                    value="{{ old('template_name') }}" maxlength="512" required>
                                                <div class="invalid-feedback text-danger"></div>
                                                <small id="nameCharCount"
                                                    class="text-muted text-end">{{ __('characters') }}: 0 /
                                                    512</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <label for="template_type">{{ __('template_type') }} <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control" id="template_type" name="template_type" required>
                                                    <option value="IMAGE"
                                                        {{ old('template_type') == 'IMAGE' ? 'selected' : '' }}>
                                                        {{ __('image') }}
                                                    </option>
                                                    <option value="VIDEO"
                                                        {{ old('template_type') == 'VIDEO' ? 'selected' : '' }}>
                                                        {{ __('video') }}
                                                    </option>
                                                </select>
                                                <div class="invalid-feedback text-danger"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div id="templateImageSection" class="mb-4" style="display: {{ old('template_type') == 'IMAGE' ? 'block' : 'none' }};">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <label for="template_image">{{ __('template_image') }}</label>
                                                        <input type="text" class="form-control" id="template_image"
                                                            name="template_image" placeholder="{{ __('enter_template_image') }}"
                                                            accept="image/*" value="{{ old('template_image') }}">
                                                        <small class="text-muted">{{ __('only_image_url') }}</small>
                                                        <!-- Localized help text -->
                                                        <div class="invalid-feedback text-danger">
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="mb-4">
                                                            <label for="title">{{ __('title') }}</label>
                                                            <input type="text" class="form-control" id="title" name="title" placeholder="{{ __('enter_title') }}" value="{{ old('title') }}" maxlength="60">    
                                                            <div class="invalid-feedback text-danger"></div>
                                                            <small id="charCount" class="text-muted">{{ __('characters') }}: 0 / 1024</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="mb-4">
                                                            <label for="subtitle">{{ __('subtitle') }}</label>
                                                            <input type="text" class="form-control" id="subtitle" name="subtitle" placeholder="{{ __('enter_subtitle') }}" value="{{ old('subtitle') }}" maxlength="60">    
                                                            <div class="invalid-feedback text-danger"></div>
                                                            <small id="charCount" class="text-muted">{{ __('characters') }}: 0 / 1024</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="templateVideoSection" class="mb-4" style="display: {{ old('template_type') == 'VIDEO' ? 'block' : 'none' }};">
                                                <label for="template_video">{{ __('template_video') }}</label>
                                                <input type="text" class="form-control" id="template_video"
                                                    name="template_video" placeholder="{{ __('enter_template_video') }}"
                                                    accept="video/*" value="{{ old('template_video') }}">
                                                <div id="validationMessage" class="invalid-feedback text-danger"></div>

                                                <small class="text-muted">{{ __('only_video_url') }}</small>
                                                <!-- Help text -->
                                                <div class="invalid-feedback text-danger"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-4 position-relative">
                                                <label for="button_type" class="d-block">{{ __('button') }} <span class="text-danger">*</span></label>
                                                <div class="radio_button">
                                                    <input type="radio" name="button_type" id="none"
                                                        value="NONE"
                                                        {{ old('button_type') == 'NONE' ? 'checked' : 'checked' }} />
                                                    <label class="btn btn-default" for="none">
                                                        {{ __('none') }}
                                                    </label>
                                                    <div class="invalid-feedback text-danger"></div>
                                                </div>
                                                <div class="radio_button">
                                                    <input type="radio" id="cta" name="button_type"
                                                        id="cta" value="CTA"
                                                        {{ old('button') == 'CTA' ? 'checked' : '' }} />
                                                    <label class="btn btn-default" for="cta">
                                                        {{ __('cta') }}</label>
                                                    <div class="invalid-feedback text-danger"></div>
                                                </div>
                                                <div class="radio_button">
                                                    <input type="radio" id="quick_reply" name="button_type"
                                                        id="quick_reply" value="QUICK_REPLY"
                                                        {{ old('button_type') == 'quick_reply' ? 'checked' : '' }} />
                                                    <label class="btn btn-default" for="quick_reply">
                                                        {{ __('quick_reply') }}</label>
                                                    <div class="invalid-feedback text-danger"></div>

                                                </div>
                                            </div>
                                            <br>
                                            <div id="call-to-action-section" class="position-relative"
                                                style="display:none">
                                                <div class="call-to-action-btn">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="dropdown">
                                                                <button class="btn sg-btn-primary dropdown-toggle btn-sm"
                                                                    type="button" data-bs-toggle="dropdown"
                                                                    aria-expanded="false">
                                                                    <i class="las la-plus"></i>
                                                                    {{ __('add_call_to_action') }}
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li>
                                                                        <a class="dropdown-item add_call_to_action"
                                                                            data-action="visit_website"
                                                                            data-max="2"
                                                                            href="javascript:void(0);">
                                                                            <i class="las la-globe-africa"></i>
                                                                            {{ __('visit_website') }}
                                                                            <span class="d-block add-btn-notice">2
                                                                                {{ __('buttons_maximum') }}</span>
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item add_call_to_action"
                                                                            data-action="call_phone_number"
                                                                            data-max="1"
                                                                            href="javascript:void(0);"> 
                                                                            <i class="las la-phone-volume"></i>
                                                                            {{ __('call_phone_number') }}
                                                                            <span class="d-block add-btn-notice">1
                                                                                {{ __('buttons_maximum') }}</span>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="append-call-to-action" id="append-call-to-action">
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="quick_reply-section" class="position-relative" style="display:none">
                                                <div class="quick_reply-btn">
                                                    <button class="btn sg-btn-primary add-quick-reply-btn btn-sm"
                                                        type="button" data-action="quick_reply" data-max="8">
                                                        <i class="las la-plus"></i> 
                                                        {{ __('add_quick_reply') }}
                                                        <span class="d-block add-btn-notice">
                                                            {{ __('8') }}
                                                            {{ __('buttons_maximum') }}
                                                        </span>
                                                    </button>
                                                </div>
                                                <div class="append-quick-reply" id="append-quick-reply">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-4 mt-2">
                                                <div class="d-flex justify-content-end align-items-center mt-30">
                                                    <button id="preloader" class="btn btn-primary d-none" type="button"
                                                        disabled>
                                                        <span class="spinner-border spinner-border-sm" role="status"
                                                            aria-hidden="true"></span>
                                                        Loading...
                                                    </button>
                                                    <button type="submit"
                                                        class="btn btn-primary save">{{ __('submit') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </form>
                            </div>
                            <div class="col-lg-4">
                                <div class="whatsapp-container">
                                    @include('addon:OmniSync::template.partials._preview')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.7/axios.min.js"></script>
    <script>
        window.translations = {!! json_encode(json_decode(file_get_contents(base_path('lang/en.json')), true)) !!};
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>
    <script src="{{ static_asset('admin/js/custom/template.js') }}?v=1.9.0"></script>
@endpush
