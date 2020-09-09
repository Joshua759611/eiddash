<?php

namespace App\Api\V1\Requests;

use Dingo\Api\Http\FormRequest;
// use App\Rules\BeforeOrEqual;


class CovidConsumptionPullRequest extends FormRequest
{
    public function rules()
    {
        return [
            // 'start_of_week' => 'required|date|date_format:Y-m-d',//,
            // 'end_of_week' => 'required|date|date_format:Y-m-d|after:start_of_week|before:'.$startThisWeek,
            // 'platforms' => 'required|array',

            // 'test' => 'required|integer|max:2',
            // 'start_date' => ['date_format:Y-m-d', 'required_with:end_date', new BeforeOrEqual($this->input('end_date'), 'end_date')],
            // 'start_date' => ['date_format:Y-m-d', 'required_with:end_date', 'before_or_equal:' . $this->input('end_date')],
            // 'end_date' => 'date_format:Y-m-d',
            // 'date_dispatched_start' => ['date_format:Y-m-d', 'required_with:date_dispatched_end', 'before_or_equal:' . $this->input('date_dispatched_end')],
            // 'date_dispatched_end' => 'date_format:Y-m-d',            
        ];
    }

    public function authorize()
    {
        $apikey = $this->headers->get('apikey');
        if (!$apikey)
            return false;
        $apilab = \App\Lab::select('id', 'name')->where('apikey', '=', $apikey)->get();
        
        if($apilab->isEmpty()) return false;
        else{
            if(!session()->has('lab'));
                session(['lab' => $apilab->first()]);
            return true;
        }
    }

    public function messages()
    {
        return [];
    }

}
