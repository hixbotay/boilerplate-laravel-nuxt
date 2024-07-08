<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;

class SettingController extends AbsController
{
    protected $model;
    protected $rules;
    public function __construct()
    {
        $this->model = new Config();
        $this->rules = [
            'name' => 'required|string',
            'value' => 'required|string',
        ];
    }
}
