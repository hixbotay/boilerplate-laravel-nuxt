<?php

namespace App\Http\Controllers;

use App\Exceptions\Handler;
use App\Helpers\UploadsHelper;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::filter($request)->with('user');
        $res = [
            'records' => [],
            'page' => $query->getPageNumber(),
            'per_page' => $query->getPerPage(),
            'total_records' => 0
        ];
        if($request->is_paginate){
            $res['total_records'] = $query->getTotal();
        }else{
            $res['records'] = $query->get();
        }
        return response()->json($res);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|unique:articles,title',
            'description' => 'required|string',
            'excerpt' => 'string',
            // 'image_feature' => 'nullable|image',
        ]);
        if ($request->has('image_feature')) {
            $result = Storage::disk('public')->put('/assets/images/article', $request->file('image_feature'));
            $data['image_feature'] = $result;
        }
        try {
            $user = Auth::user();
            $data['user_id'] = $user->id;
            $article = Article::create($data);
        } catch (\Exception $e) {
            return response()->json(array('message' => $e->getMessage()), 400);
        }
        return response()->json(Article::with('user')->find($article->id));
    }

    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'title' => 'required|unique:articles,title,'.$article->id,
            'description' => 'required|string',
            'excerpt' => 'required|string',
            // 'image_feature' => '',
        ]);
        try {
            if (!empty($request->image_feature)) {
                if($article->image_feature){
                    Storage::disk('public')->delete($article->image_feature);
                }
                $result = Storage::disk('public')->put('/assets/images/article', $request->file('image_feature'));
                $data['image_feature'] = $result;
            }
            $article->update($data);
        } catch (\Exception $e) {
            return response()->json(array('message' => $e->getMessage()), 400);
        }
        return response()->json(Article::with('user')->find($article->id));
    }

    public function show(Request $request, $article)
    {
        return response()->json(Article::with('user')->find($article));
    }

    public function destroy(Request $request, Article $article)
    {
        $result = $article->delete();
        if ($result) {
            return response()->json(['message' => 'Delete success']);
        }
        return response()->json(['message' => 'Delete error'], 202);
    }
}
