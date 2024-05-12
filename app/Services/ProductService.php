<?php

namespace App\Services;

use App\Helpers\ResponseHelper;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;

/**
 * Class ProductService.
 */
class ProductService
{

    public function storeProduct($request)
    {
        $result=Product::query()->create([$request->validated()]);
        return $result;
    }
    public function updateProduct($request,Product $product)
    {
        $result= $product->where('id', $product->id)
        ->query()->update([$request->validated()]);
        return $result;
    }
    public function showProduct()
    {
        $result=Product::query()->get()->toArray();
        return $result;
    }

    public function deleteProduct(Product $product)
    {
        $result=$product->where('id', $product->id)->delete();
        return $result;
    }


}
