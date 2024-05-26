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
    public function store($request)
    {
        $result = Feedback::query()->create([
            'user_id' => Auth::user()->id,
            'content' => $request->content
        ]);
        return $result;
    }

    public function index()
    {
        $result = Feedback::query()->with('user', 'user.userDetails')->get()->toArray();
        return $result;
    }

    public function destroy($feedBack)
    {
        $result = Feedback::findOrFail($feedBack)->delete();
        return $result;
    }
}
