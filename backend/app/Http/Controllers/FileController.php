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
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($res);
    }


    public function store(Request $request)
    {
        $body = $request->validate([
            'path' => 'required|string',
            'file' => 'required|file',
        ]);
        try {
            // if (!$request->hasFile('file')) {
            //     $result = Storage::disk('public')->put('/assets/images/product', $request->file('image'));
            //     $body['image'] = $result;
            // }
            $result = Storage::disk('public')->put($body['path'], $request->file('file'));
            return response()->json(['path' => Storage::disk('public')->url($result)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, $body)
    {
        $body = $request->validate([
            'path' => 'required|string',
            'file' => 'required|string',
        ]);
        try {
            if(Storage::disk('public')->exists($body['path'])){
                $result = Storage::disk('public')->delete($body['path']);
            }
            $result = Storage::disk('public')->put($body['path'], $request->file('file'));
            return response()->json(['path' => Storage::disk('public')->url($result)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(Request $request)
    {
        $body = $request->validate([
            'path' => 'required|string',
            'file' => 'required|string',
        ]);
        try {
            if(Storage::disk('public')->exists($body['path'])){
                $result = Storage::disk('public')->delete($body['path']);
            }
            return response()->json(['result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }


}
