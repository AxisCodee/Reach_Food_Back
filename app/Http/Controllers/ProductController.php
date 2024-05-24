<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use App\Services\ProductService;

class ProductController extends Controller
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }


    public function store(StoreProductRequest $request)

    {
        $result = $this->productService->storeProduct($request);
        return ResponseHelper::success($result, null, 'products created successfully', 200);
    }

    public function update(StoreProductRequest $request, $product)
    {
        $result = $this->productService->updateProduct($request, $product);
        return ResponseHelper::success($result, null, 'products update successfully', 200);
    }

    public function index()
    {
        $result = $this->productService->indexProduct();
        return ResponseHelper::success($result, null, 'products returned successfully', 200);
    }

    public function show($product)
    {
        $result = $this->productService->showProduct($product);
        return ResponseHelper::success($result, null, 'products returned successfully', 200);
    }

    public function destroy($product)
    {
        $result = $this->productService->deleteProduct($product);
        return ResponseHelper::success($result, null, 'products deleted successfully', 200);
    }

}
