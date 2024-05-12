<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use App\Services\FileService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }


    public function store(StoreProductRequest $request)

    {         $result=$this->productService->storeProduct($request);

         // $imagePath = app(FileService::class)->upload($request, 'image');
        return ResponseHelper::success($result,null,'products created successfully',200);
    }

    public function update(StoreProductRequest $request,Product $product)
    {
        $result=$this->productService->updateProduct($request,$product);
        return ResponseHelper::success($result,null,'products update successfully',200);
    }

    public function show()
    {
        $result=$this->productService->showProduct();
        return ResponseHelper::success($result,null,'products returned successfully',200);
    }
    public function index(Product $product)
    {
        $result=$this->productService->indexProduct($product);
        return ResponseHelper::success($result,null,'products returned successfully',200);
    }

    public function destroy(Product $product)
    {
        $result=$this->productService->deleteProduct($product);
        return ResponseHelper::success($result,null,'products deleted successfully',200);
    }

}
