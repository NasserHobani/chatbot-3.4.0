@php
    use App\Helpers\TextHelper;
@endphp
<div class="accordion border-0" id="accordionPreview">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePreview"
                aria-expanded="true" aria-controls="collapsePreview">
                {{ __('preview') }}
            </button>
        </h2>
        <div id="collapsePreview" class="accordion-collapse collapse show" data-bs-parent="#accordionPreview">
            <div class="accordion-body">
                <div class="whatsapp-preview">
                    {{-- <div class="marvel-device nexus5"> --}}
                    <div class="temp-pre">
                        <div class="screen ">
                            <div class="screen-container">
                                <div class="screen-container">
                                    <div class="chat">
                                        <div class="chat-container">
                                            <div class="conversation">
                                                <div class="conversation-container">
                                                    <div class="message received card">
                                                        <div class="card-body m-0 p-0">
                                                            <div class="message-body mb-2">
                                                                <div id="_message_body_text">
                                                                    {{-- Display Image --}}
                                                                    @if ($row->category == 'IMAGE' && !empty($row->components[0]['payload']['elements'][0]['image_url']))
                                                                        <img src="{{ $row->components[0]['payload']['elements'][0]['image_url'] }}" alt="Image Preview" style="max-width: 100%; height: auto;">
                                                                    @endif
                                                                
                                                                    {{-- Display Title --}}
                                                                    @if (!empty($row->components[0]['payload']['elements'][0]['title']))
                                                                        <h4>{{ $row->components[0]['payload']['elements'][0]['title'] }}</h4>
                                                                    @endif
                                                                
                                                                    {{-- Display Subtitle --}}
                                                                    @if (!empty($row->components[0]['payload']['elements'][0]['subtitle']))
                                                                        <p>{{ $row->components[0]['payload']['elements'][0]['subtitle'] }}</p>
                                                                    @endif
                                                                
                                                                    {{-- Display Video --}}
                                                                    @if ($row->category == 'VIDEO' && !empty($row->components[0]['payload']['elements'][0]['url']))
                                                                        <video controls style="max-width: 100%; height: auto;">
                                                                            <source src="{{ $row->components[0]['payload']['elements'][0]['url'] }}" type="video/mp4">
                                                                            Your browser does not support the video tag.
                                                                        </video>
                                                                    @endif                                                                    
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <br>
                                                        <div id="_footer_btn"
                                                            class="text-center card-footer m-0 p-0 bg-white border-top-0">
                                                            @if (isset($buttons))
                                                                @foreach ($buttons as $button)
                                                                    <div class="tmp-btn-list border-top mt-2">
                                                                        @switch($button['type'])
                                                                        @case('web_url')
                                                                                <button class="btn btn-template">

                                                                                    {!! $button['title'] !!} </button>
                                                                            @break
                                                                        @endswitch
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
