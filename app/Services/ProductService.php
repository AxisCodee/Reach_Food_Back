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
        $result= $product->query()->where('id', $product->id)
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
        $result=Product::query()->where('id',$product->id)->get()->toArray();
        return $result;
    }

    public function deleteProduct($product)
    {
        $result=$product->where('id', $product->id)->delete();
        return $result;
    }


}
