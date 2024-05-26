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
    public function updatePrice($request)
{
    $results = [];

    foreach ($request->product_id as $index => $productId) {
        $result = Product::where('id', $productId)->update([
            'retail_price' => $request->retail_price[$index],
            'wholesale_price' => $request->wholesale_price[$index]
        ]);

    }

    return $result;
}
    public function indexProduct()

    {
        $category_id = request()->input('category_id');
        $result=Product::query()->where('category_id',$category_id)->paginate(10);
        return $result;
    }

    public function showProduct($product)
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
