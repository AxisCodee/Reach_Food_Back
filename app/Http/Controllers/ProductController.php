<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\Product\GetListPriceRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\SupplyProductsRequest;
use App\Http\Requests\UpdatePricesRequest;
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

    public function restore($id)
    {
        $p = Product::onlyTrashed()->where('id', $id)->first();
        if ($p) {
            $p->restore();
            return ResponseHelper::success($p, null, 'products restore successfully', 200);
        }
        return ResponseHelper::error(null, 'المنتج غير موجود', 404);
    }

    public function updatePrice(UpdatePricesRequest $request)
    {
        $request->validated();
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

    public function getPrices(GetListPriceRequest $request)
    {
        return ResponseHelper::success(
            $this->productService->getPrice($request->products)
        );
    }

    public function listPrices()
    {
        return ResponseHelper::success(
            $this->productService->listPrices(request('branch_id'))
        );
    }

    public function supply(SupplyProductsRequest $request)
    {
        $data = $request->validated();
        $products = Product::query()
            ->whereIn('id', $data['products'])
            ->get();
        foreach ($products as $product) {
            $newProduct = $product->replicate();
            $newProduct['branch_id'] = $data['branch_id'];
            $newProduct->save();
        }

        return ResponseHelper::success('success');
    }
}
