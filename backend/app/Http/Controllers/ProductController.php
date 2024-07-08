<?php

namespace App\Http\Controllers;

use App\Helpers\CsvfileHelper;
use App\Http\Enums\ServiceType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductItem;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Enums\ProductItemStatus;
use App\Models\ProductCategoryRelation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        
        $query = Product::joinCategory()->filter($request);
        if ($request->cat_id) {
            $query->getCategory($request->cat_id);
        }
        $res = [
            'records' => [],
            'page' => $query->getPageNumber(),
            'per_page' => $query->getPerPage(),
            'total_records' => 0
        ];
        if($request->is_paginate){
            $res['total_records'] = $query->getTotal();
        }else{
            $res['records'] = $query->with('categories')->get();
        }
        return response()->json($res);
    }


    public function store(Request $request)
    {
        $body = $request->validate([
            'name' => 'required|string',
            'sku' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'description' => 'nullable|string',
            'quantity' => 'nullable|integer',
            'price' => 'numeric',
            'price_sale' => 'nullable|numeric',
            'categories' => 'nullable|array',
            'categories.*.id' => 'exists:categories,id'
        ]);
        try {
            unset($body['categories']);
            if($request->id){
                return $this->update($request,$body);
            }
            
            if ($request->hasFile('image')) {
                $result = Storage::disk('public')->put('/assets/images/product', $request->file('image'));
                $body['image'] = $result;
            }
            $product = Product::create($body);
            foreach ($request->categories as $cid) {
                DB::table('product_categories_relation')->insert([
                    "product_id" => $product->id,
                    "category_id" => $cid
                ]);
            }
            return response()->json(Product::with('categories')->find($product->id));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, $body)
    {
        $product = Product::find($request->id);
        if(!$product){
            return response()->json(['message' => "Require product"], 400);
        }
        try {
            if ($request->hasFile('image')) {
                if($product->image){
                    Storage::disk('public')->delete($product->image);
                }
                $result = Storage::disk('public')->put('/assets/images/product', $request->file('image'));
                $body['image'] = $result;
            }
            $product->update($body);
            if ($request->categories) {
                $product->deleteCategory();
                $product->insertCategory($request->categories);
            }
            
            return response()->json(Product::with('categories')->find($product->id));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show($product)
    {
        $record = Product::with('categories')->find($product);
        return response()->json($record);
    }

    public function destroy(Request $request, $product_id)
    {
        $product = Product::find($product_id);
        if ($product) {
            DB::beginTransaction();
            Storage::disk('public')->delete($product->image);
            $rowRelation = ProductCategoryRelation::getByProduct($product->id)->delete();
            $rowProductItem = ProductItem::getByProduct($product->id)->delete();
            $rowProduct = $product->delete();
            DB::commit();
            return response(['message' => 'Delete success'], 202);
        }
        return response()->json(['message' => 'Delete error'], 405);
    }

    public function clientProductDetail($product)
    {
        $record = Product::with('categories')->find($product);
        return response()->json($record);
    }

    public function getCategories(Request $request)
    {
        $query = ProductCategory::filter($request);
        
        $res = [
            'records' => [],
            'page' => $query->getPageNumber(),
            'per_page' => $query->getPerPage(),
            'total_records' => 0
        ];
        if($request->get_paging){
            $res['total_records'] = $query->getTotal();
        }else{
            $res['records'] = $query->get();
        }
        return response()->json($res);
    }

    public function getTreeCategory(Request $request)
    {
        $query = ProductCategory::filter($request);
        
        $res = [
            'records' => [],
            'page' => $query->getPageNumber(),
            'per_page' => $query->getPerPage(),
            'total_records' => 0
        ];
        if($request->get_paging){
            $res['total_records'] = $query->getTotal();
        }else{
            $records = $query->get()->toArray();
            $res['records'] = array_values(array_filter($records, function($e){
                return $e['parent_id'] == 0;
            }));
            foreach($res['records'] as $key=>$parent){
                $res['records'][$key]['child'] = array_values(array_filter($records, function($e) use ($parent){
                    return $e['parent_id'] == $parent['id'];
                }));
            }
        }
        return response()->json($res);
    }


    public function storeCategory(Request $request)
    {
        $body = $request->validate([
            'parent_id' => 'nullable|integer',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'nullable|integer',
            'image' => 'nullable|image'
        ]);
        try {
            $body['user_id'] = $request->user()->id;
            if ($request->hasFile('image')) {
                $result = Storage::disk('public')->put('/assets/images/category', $request->file('image'));
                $body['image'] = $result;
            }
            if($body['status']===null){
                $body['status'] = 1;
            }
            $cat = ProductCategory::create($body);

            return response()->json($cat);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function updateCategory(Request $request, ProductCategory $category)
    {
        $body = $request->validate([
            'parent_id' => 'nullable|integer',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'nullable|integer',
            'image' => 'nullable|image'
        ]);
        
        try {
            if ($request->hasFile('image')) {
                if($category->image){
                    Storage::disk('public')->delete($category->image);
                }
                $result = Storage::disk('public')->put('/assets/images/category', $request->file('image'));
                $body['image'] = $result;
            }

            $category->update($body);
            return response()->json($category);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function deleteCategory(Request $request, $category_id)
    {
        $category = ProductCategory::find($category_id);
        if ($category) {
            Storage::disk('public')->delete($category->image);
            $listCategoriesChild = ProductCategory::getChildren($category->id)->get();
            $listIdCat[] = $category->id;
            if (count($listCategoriesChild) > 0) {
                foreach ($listCategoriesChild as $cat) {
                    $listIdCat[] = $cat->id;
                    Storage::disk('public')->delete($cat->image);
                }
            }
            $rowCat = ProductCategory::getListId($listIdCat)->delete();
            $rowRelation = ProductCategoryRelation::getListCategoryId($listIdCat)->delete();
            return response(['message' => 'Delete table product_categories '.$rowCat.' row, table product_categories_relation '.$rowRelation.' row'], 202);
        }
        return response()->json(['message' => 'Delete error'], 405);
    }

    public function getItems(Request $request)
    {
        $query = ProductItem::filter($request);
        
        $res = [
            'records' => [],
            'page' => $query->getPageNumber(),
            'per_page' => $query->getPerPage(),
            'total_records' => 0
        ];
        if($request->get_paging){
            $res['total_records'] = $query->getTotal();
        }else{
            $res['records'] = $query->get();
        }

        return response()->json($res);
    }

    public function storeItem(Request $request)
    {
        $body = $request->validate([
            'product_id' => 'required|integer',
            'name' => 'required|string',
            'email' => 'nullable|string',
            'description' => 'string',
            'status' => 'integer',
        ]);
            
        try {
            if (empty($request->status)) {
                $body['status'] = ProductItemStatus::ACTIVE['value'];
            } else {
                $body['status'] = $request->status;
            }
            $product_id = $body['product_id'];
            
            $productItem = ProductItem::create($body);
            Product::updateQuantity($product_id);
            return response()->json(ProductItem::with('product')->find($productItem->id));

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function updateItem(Request $request, ProductItem $productitem)
    {
        $body = $request->validate([
            'name' => 'required|string',
            'description' => 'string',
            'status' => 'integer',
        ]);
        try {
            $oldStatus = $productitem->status;
            unset($body['product_id']);
            $productitem->update($body);
            if (isset($body['status']) && $body['status'] != $oldStatus) {
                Product::updateQuantity($productitem->product_id);
            }
            return response()->json(ProductItem::with('product')->find($productitem->id));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function deleteItem(Request $request, ProductItem $productitem)
    {
        if ($productitem->delete()) {
            $quantity = Product::updateQuantity($productitem->product_id);
            return response(['message' => 'Record is deleted', 'quantity' => $quantity], 202);
        }
        return response()->json(['message' => 'Delete error'], 405);
    }

    public function importItem(Request $request)
    {
        $body = $request->validate([
            'product_id' => 'integer',
            'fileItem' => 'file',
        ]);
        try {
            //code...
            $data = [];
            if ($request->hasFile('fileItem')) {
                $filePath = $request->file('fileItem');
                $data = CsvfileHelper::read($filePath);
            } 
            // Create data import
            $dataImport = [];
            if (count($data) > 0) {
                foreach ($data as $key => $value) {
                    if ($key > 0) {
                        $dataImport[] = [
                            'product_id' => $body['product_id'],
                            'name' => $value['col-1'],
                            'description' => $value['col-2'],
                            'status' => ProductItemStatus::ACTIVE['value'],
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                    }
                }
            }
            $import = ProductItem::insert($dataImport);
            $quantity = Product::updateQuantity($body['product_id']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
        return response()->json([ 'data_import' => $import, 'quantity' => $quantity ]);
    }

}
