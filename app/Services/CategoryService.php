<?php

namespace App\Services;

use App\Models\Category;

/**
 * Class CategoryService.
 */
class CategoryService
{
    public function store($request)
    {
        $result = Category::query()->create([
            'name' => $request->name,
            'branch_id'=> $request->branch_id

        ]);
        return $result;
    }

    public function index()
    {
        $result = Category::query()->get()->toArray();
        return $result;
    }

    public function destroy($category)
    {
        $result = Category::findOrFail($category)->delete();
        return $result;
    }
    public function update($category)
    {
        $result = Category::findOrFail($category)->update();
        return $result;
    }
}
