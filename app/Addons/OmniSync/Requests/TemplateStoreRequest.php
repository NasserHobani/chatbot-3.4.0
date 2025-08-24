<?php

namespace App\Addons\OmniSync\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TemplateStoreRequest extends FormRequest
{
/**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'template_name'     => 'required|string|max:512',
            'template_type'     => 'required|string|in:IMAGE,VIDEO',
            'template_video'    => 'required_if:template_type,VIDEO|url',
            'template_image'    => 'required_if:template_type,IMAGE|url',   
            'title'             => 'nullable|string|max:60',
            'subtitle'          => 'nullable|string|max:60',
            'button_type'       => 'required|string|in:NONE,QUICK_REPLY,CTA',
            'type_of_action'    => 'required_if:,CTA|array',
            'button_value'      => 'required_if:button_type,CTA|array',
            'button_text.*'     => [
                'required_if:button_type,QUICK_REPLY',
                'string',
                'max:255'
            ]
        ];
    }
    public function messages()
    {
        return [
            'template_name.required' => 'Template name is required.',
            'template_name.max' => 'Template name can have a maximum of 512 characters.',
            'template_type.required' => 'Template type is required.',
            'template_type.in' => 'Template type must be one of image, video.',
            'locale.required' => 'Locale is required.',
            'template_image.required_if' => 'Template image url is required when the template type is "image".',
            'template_video.required_if' => 'Template video url is required when the template type is "video".',     
            'title.max' => 'Title can have a maximum of 60 characters.',
            'subtitle.max' => 'Subtitle can have a maximum of 60 characters.',
            'type_of_action' => 'Type of action is required when the button type is "CTA".',
            'button_value' => 'Button value is required when the button type is "CTA".'
        ];
    }
}

?>