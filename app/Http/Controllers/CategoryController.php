<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\CreateCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    private $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }
    public function store(CreateCategoryRequest $request)

    {
        $result = $this->categoryService->store($request);
        return ResponseHelper::success($result, null, 'category created successfully', 200);
    }

    public function update(CreateCategoryRequest $request,$product)
    {
        $result = $this->categoryService->update($request, $product);
        return ResponseHelper::success($result, null, 'category update successfully', 200);
    }

    public function index()
    {
        $result = $this->categoryService->index();
        return ResponseHelper::success($result, null, 'category returned successfully', 200);
    }

    public function show($product)
    {
        $result = $this->categoryService->show($product);
        return ResponseHelper::success($result, null, 'category returned successfully', 200);
    }

    public function destroy($product)
    {
        $result = $this->categoryService->delete($product);
        return ResponseHelper::success($result, null, 'category deleted successfully', 200);
    }
}
