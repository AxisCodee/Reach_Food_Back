<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
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

    public function updatePrice(Request $request)
    {
        $this->productService->updatePrice($request);
        return ResponseHelper::success(true, null, 'Prices updated successfully', 200);
    }

    public function salesmanProducts(Request $request)
    {
        $products = $this->productService->getSalesmanProducts($request);
        return ResponseHelper::success($products);
    }

    public function importProducts(Request $request)
    {
        $products = $request['products'];
        foreach ($products as $product) {
            $this->productService->importProduct($product, $request->category_id);
        }
        return ResponseHelper::success('Products imported successfully');
    }


}
