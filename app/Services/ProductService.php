<?php

namespace App\Services;

use App\Actions\GetUpperRoleUserIdsAction;
use App\Enums\NotificationActions;
use App\Enums\Roles;
use App\Events\SendMulticastNotification;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;


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
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $imagePath = app(FileService::class)->upload($request, 'image');
            $data['image'] = $imagePath;
        } else {
            $existingProduct = Product::findOrFail($product);
            $data['image'] = $existingProduct->image;
        }

        $result = Product::findOrFail($product)->update($data);
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

        event(new SendMulticastNotification(
            auth()->id(),
            User::query()->whereIn('role', [Roles::CUSTOMER->value, Roles::SALESMAN->value])->pluck('id')->toArray(),
            NotificationActions::CHANGE_PRICE->value,
        ));

        return $updatedProduct;
    }

    public function indexProduct()
    {
        $branch_id = request()->input('branch_id');
        $result = Product::query()->where('branch_id', $branch_id)->paginate(10);
        return $result;
    }

    public function showProduct($product)
    {
        $result = Product::findOrFail($product);
        return $result;
    }

    public function deleteProduct($product): ?bool
    {
        $product = Product::findOrFail($product);
        $data = [
            'action_type' => NotificationActions::DELETE->value,
            'actionable_id' => $product->id,
            'actionable_type' => Product::class,
            'user_id' => auth()->id(),
        ];
        $ownerIds = GetUpperRoleUserIdsAction::handle(auth()->user());

        NotificationService::make($data, 0, $ownerIds);
        return $product->delete();
    }

    public function getSalesmanProducts($request)
    {
        return Branch::findOrFail($request->branch_id)->products()->paginate(10)->toArray();
    }

    public function importProduct($product_id, $branch_id)
    {
        $product = Product::FindOrFail($product_id);
        Product::query()->create([
            'name' => $product->product_name,
            'branch_id' => $branch_id,
            'description' => $product->product_description,
            'amount' => $product->product_amount,
            'unit_price' => $product->product_unit_price,
            'wholesale_price' => $product->product_wholesale_price,
            'retail_price' => $product->retail_price,
            'image' => $product->product_image,
        ]);
        return true;
    }

    public function getPrice($products): array
    {
        $priceType = auth()->user()->customer_type == 'shop' ? "retail_price" : 'wholesale_price';
        return Product::query()
            ->whereIn('id', $products)
            ->select('id', DB::raw("$priceType AS price"))
            ->get()
            ->toArray();
    }

    public function listPrices($branchId)
    {
        return Product::query()
            ->where('branch_id', $branchId)
            ->get()
            ->toArray();
    }
}
