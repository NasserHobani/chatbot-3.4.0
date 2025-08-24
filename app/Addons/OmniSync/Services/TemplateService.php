<?php

namespace App\Addons\OmniSync\Services;
use App\Models\Language;

class TemplateService
{
    protected $messengerService;

    public function __construct(MessengerService $messengerService)
    {
        $this->messengerService = $messengerService;
    }

    public function execute($row)
    {
        $buttons = null;
        $locales =  Language::pluck('name','locale');
        $components = $row->components;

        foreach ($components as $item) {
            if($item['payload']['elements'][0]['buttons']){
                $buttons = $item['payload']['elements'][0]['buttons'];
            }
        }
       
        $data      = [
            'row'   => $row,
            "buttons" => $buttons,
            "locales" => $locales,
        ];

        return $data;
    }
}
?>