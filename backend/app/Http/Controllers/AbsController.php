<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AbsController extends Controller
{
    protected $model;
    protected $rules;
    protected $rulesUpdate;
    /**
     * Get a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $this->model->filter($request);

        $res = [
            'page' => $query->getPageNumber(),
            'per_page' =>  $query->getPerPage(),
        ];
        if($request->is_paginate){
            $res['total_records'] = $query->getTotal();
        }else{
            $res['records'] = $query->get();
        }

		return response()->json($res);
    }


    /**
     * Creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $body =  $request->validate($this->rules);

        $res = $this->model->create($body);

        return response()->json($res);
    }

    /**
     * Get the specified resource.
     *
     * @param  int  $template_id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json($this->model->find($id));
    }

    /**
     * Update a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if($this->rulesUpdate){
            $data = $request->validate($this->rulesUpdate);
        }else{
            $data = $request->validate($this->rules);
        }

        $model = $this->model->findOrFail($id);
        $model->update($data);

        return response()->json($model);
    }

    public function destroy(Request $request, $id)
    {
        $model = $this->model->findOrFail($id);
        if ($model && $model->delete()) {
            return response(['message' => 'Record is deleted'],202);
        }
        return response()->json(['message' => 'Delete error'], 405);
    }
}
