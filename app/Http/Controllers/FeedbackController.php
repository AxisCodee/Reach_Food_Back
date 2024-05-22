<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\FeedbackRequest;
use App\Services\FeedbackService;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    private $feedbackService;

    public function __construct(FeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }

    public function store(FeedbackRequest $request)
    {
        $result = $this->feedbackService->store($request);
        return  ResponseHelper::success($result, null, 'feedback stored successfully', 200);
    }
    public function index()
    {
        $result = $this->feedbackService->index();
        return  ResponseHelper::success($result, null, 'feedback returned successfully', 200);
    }
    public function destroy($feedback)
    {
        $result = $this->feedbackService->destroy($feedback);
        return  ResponseHelper::success($result, null, 'feedback returned successfully', 200);
    }
}
