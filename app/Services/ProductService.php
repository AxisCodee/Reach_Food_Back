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
        $imagePath = app(FileService::class)->upload($request, 'image');
        $data = $request->validated();
        $data['image'] = $imagePath;
        $result = Product::query()->create($data);
        return $result;
    }
    public function updateProduct($request,$product)
    {
        $imagePath = app(FileService::class)->upload($request, 'image');
        $data = $request->validated();
        $data['image'] = $imagePath;
        $result= Product::findOrFail($product)
        ->update($data);
        return $result;
    }
    public function showProduct()
    {
        $result=Product::query()->get()->toArray();
        return $result;
    }

    public function indexProduct($product)
    {
        $result =Product::findOrFail($product);
        return $result;
    }

    public function deleteProduct($product)
    {
        $result=Product::findOrFail($product)->delete();
        return $result;
    }


}
