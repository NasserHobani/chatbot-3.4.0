@extends('backend.layouts.master')
@section('title', __('campaigns'))
@push('css_asset')
    <link rel="stylesheet" href="{{ static_asset('admin/css/devices.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('admin/css/template.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        .flatpickr-wrapper {
            width: 100%;
        }

        #schedule_time_div {
            display: none;
        }

        .boot-file-input {
            height: 38px !important;
            padding-left: 12px !important;
        }

        .message.received {
            width: 90%;
        }

        .temp-pre {
            width: 320px;
            border-radius: 20px;
            min-height: 350px;
        }

        .conversation .conversation-container {
            min-height: 350px;
        }
    </style>
@endpush
@section('content')
    <div class="main-content-wrapper">
        <section class="oftions">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-12">
                        <h3 class="section-title">{{ __('send_new_campaigns') }}</h3>
                        <form action="{{ route('client.messenger.contact.template.store') }}" id="campaign_store" method="post">
                            @csrf
                            <div class="bg-white redious-border p-20 p-sm-30">
                                <div class="row" style="display: flex;">
                                    <div class="col-6" style="display: flex; flex-direction: column;">
                                        <div class="row gx-20 add-coupon">
                                            <input type="hidden" class="is_modal" value="0" />
                                            <input type="hidden" name="contact_id" class="contact_id" value="{{ $contact_id }}" />
                                            <div class="col-lg-12">
                                                <div class="mb-4">
                                                    <label for="template_id" class="form-label">{{ __('templates') }}<span
                                                            class="text-danger">*</span></label>
                                                    <select id="template_id" name="template_id"
                                                        class="multiple-select-1 form-select-lg rounded-0 mb-3"
                                                        aria-label=".form-select-lg example" required>
                                                        <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                    </select>
                                                    @if ($errors->has('template_id'))
                                                        <div class="nk-block-des text-danger">
                                                            <p>{{ $errors->first('template_id') }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-lg-12 schedule_time_div" id="schedule_time_div">
                                                <div class="mb-4">
                                                    <label for="schedule_time"
                                                        class="form-label">{{ __('schedule_time') }}<span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" value="{{ old('schedule_time') }}"
                                                        class="form-control rounded-2" id="schedule_time"
                                                        name="schedule_time" placeholder="{{ __('schedule_time') }}">
                                                    @if ($errors->has('schedule_time'))
                                                        <div class="nk-block-des text-danger">
                                                            <p>{{ $errors->first('schedule_time') }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6" style="display: flex; flex-direction: column;">
                                        <div class="card h-100">
                                            <div class="card-body" id="load-template">
                                                @include('addon:OmniSync::template.partials.__template',[
                                                    'template' => $row,
                                                ])
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-start align-items-center mt-30">
                                            <button type="submit" class="btn sg-btn-primary"
                                                onclick="submitForm()">{{ __('send') }}</button>
                                            @include('backend.common.loading-btn', [
                                                'class' => 'btn sg-btn-primary',
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@push('js_asset')
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.7/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>
<script src="{{ static_asset('admin/js/custom/template.js') }}"></script>   
@endpush
