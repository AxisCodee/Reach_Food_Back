<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;

/**
 * Class CategoryService.
 */
class CategoryService
{
    public function store($request)
    {
        $result = Category::query()->create([
            'name' => $request->name,
            'branch_id' => $request->branch_id
        ]);
        return $result;
    }

    public function index()
    {
        $branch_id = request()->input('branch_id');
        $result = Category::query()->where('branch_id', $branch_id)
            ->get()->toArray();
        return $result;

    }


    public function update($request, $category)
    {
        ;
        $result = Category::findOrFail($category)->update($request->validated());
        return $result;
    }

    public function show($category)
    {
        $result = Category::findOrFail($category);
        return $result;
    }

    public function delete($category)
    {
        $result = Category::findOrFail($category)->delete();
        return $result;
    }

    public function getSalesmanCategories()
    {
        $salesman = User::findOrFail(19);//auth
        return $salesman->categories->toArray();
    }
}
