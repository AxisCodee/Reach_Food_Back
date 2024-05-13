<?php

namespace App\Services;

use App\Helpers\ResponseHelper;
use App\Http\Requests\FeedbackRequest;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;

/**
 * Class FeedbackService.
 */
class FeedbackService
{
    public function store($request){
    $data = $request->validated();
    $data['user_id'] = Auth::user()->id;
    $result = Feedback::query()->create($data);
    return $result;
    }
}
