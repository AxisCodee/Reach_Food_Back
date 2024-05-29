<?php

namespace App\Services;

use App\Helpers\ResponseHelper;
use App\Http\Requests\StoreProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

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

    public function updateProduct($request, $product)
    {
        $imagePath = app(FileService::class)->upload($request, 'image');
        $data = $request->validated();
        $data['image'] = $imagePath;
        $result = Product::findOrFail($product)
            ->update($data);
        return $result;
    }

    public function updatePrice($request)
    {
        $results = [];

        foreach ($request->product as $product) {
            $updatedProduct = Product::where('id', $product['id'])
                ->update([
                    'retail_price' => $product['retail_price'],
                    'wholesale_price' => $product['wholesale_price']
                ]);

            $results[] = [
                'id' => $product['id'],
                'retail_price' => $product['retail_price'],
                'wholesale_price' => $product['wholesale_price']
            ];
        }

        return  $updatedProduct;
    }

    public function indexProduct()
    {
        $category_id = request()->input('category_id');
        $result = Product::query()->where('category_id', $category_id)->paginate(10);
        return $result;
    }

    public function showProduct($product)
    {
        $result = Product::findOrFail($product);
        return $result;
    }

    public function deleteProduct($product)
    {
        $result = Product::findOrFail($product)->delete();
        return $result;
    }

    public function getSalesmanProducts($request)
    {
        $products = Category::findOrFail($request->category_id)->products()->get()->toArray();
        return $products;
    }

}
