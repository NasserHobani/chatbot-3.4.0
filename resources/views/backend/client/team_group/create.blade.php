@extends('backend.layouts.master')
@section('title', __('team'))
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="header-top d-flex justify-content-between align-items-center">
                    <h3 class="section-title">{{ __('add_team_member') }}</h3>
                </div>
                <div
                        class="default-tab-list table-responsive default-tab-list-v2 activeItem-bd-md bg-white redious-border p-20 p-sm-30">
                    <div class="default-list-table yajra-dataTable">
                        <form class="form" action="{{ route('client.team-group.store') }}" method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="basicInfo" role="tabpanel"
                                     aria-labelledby="basicInformation" tabindex="0">
                                    <input type="hidden" name="type" value="tab_form">
                                    <div class="row gx-20">
                                        <div class="col-lg-3 col-md-4">
                                            <div class="mb-4">
                                                <label for="name" class="form-label">{{ __('name') }}<span
                                                            class="text-danger">*</span></label>
                                                <input type="text" class="form-control rounded-2" id="name"
                                                       name="name" value="{{ old('name') }}"
                                                       placeholder="{{ __('enter_name') }}">
                                                <div class="nk-block-des text-danger">
                                                    <p class="name_error error">{{ $errors->first('name') }}</p>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="d-flex justify-content-end align-items-center mt-30">
                                        <button type="submit" class="btn sg-btn-primary">{{ __('save') }}</button>
                                        @include('backend.common.loading-btn', [
                                            'class' => 'btn sg-btn-primary',
                                        ])
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('backend.common.delete-script')
    @include('backend.common.change-status-script')
    @push('js')
        <script src="{{ static_asset('admin/js/countries.js') }}"></script>
    @endpush
@endsection
