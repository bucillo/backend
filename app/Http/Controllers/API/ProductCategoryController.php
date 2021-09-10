<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;

class ProductCategoryController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('id');
        $show_product = $request->input('show_product');
    
        if ($id)
        {
            $category = ProductCategory::with(['products'])->find($id);
       
            if($category) {
                    return ResponseFormatter::success(
                        $category,
                        'data produk sukses'
                    );
            }
            else {
                return ResponseFormatter::error(
                    null,
                    'data produk tidak ada',
                    666
                );
            }
        }


        /* filter  */
        $category = ProductCategory::query();
        if($name)
        {
                $category->where('name','like','%' . $name . '%');
        }

        if($show_product)
        {
            $category->with('products');
        }
        return ResponseFormatter::success(
            $category->paginate($limit),
            'data produk sukses diambil'
        );

    }
}
