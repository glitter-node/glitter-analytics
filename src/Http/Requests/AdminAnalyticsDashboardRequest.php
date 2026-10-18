<?php
namespace Modules\Glitter\Analytics\Http\Requests; use Illuminate\Foundation\Http\FormRequest; class AdminAnalyticsDashboardRequest extends FormRequest {public function authorize():bool{return true;}public function rules():array{return [];}}
