<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Enums\CurrencySymbolPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Currency;

class CurrencyController extends Controller
{
    /**
     * Get a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $query = Currency::filter($request);

		return response()->json([
			'records' => $query->get(),
			'page' => $query->getPageNumber(),
			'per_page' => $query->getPerPage(),
			'total_records' => $query->getTotal(),
		]);
    }

    /**
     * Creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $body = (array) json_decode($request->getContent());

        $rules = [
            'name' => 'required|string',
            'display_type' => 'string|in:' . implode(',', CurrencySymbolPosition::getAllValue()),
            'thousand' => 'integer|max:3',
            'symbol' => 'required|string',
            'exchange_rate' => 'required|numeric',
            'code' => 'required|string'
        ];

        $validator = Validator::make($body, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 400);
        }

        $currency = Currency::create($body);

        return response()->json($currency);
    }

    /**
     * Get the specified resource.
     *
     * @param  int  $currency_id
     * @return \Illuminate\Http\Response
     */
    public function get($currency_id)
    {
        $currency = Currency::findOrFail($currency_id);

        return response()->json($currency);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $currency_id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $currency_id)
    {
        $body = (array) json_decode($request->getContent());

        $rules = [
            'name' => 'string',
            'display_type' => 'string|in:' . implode(',', CurrencySymbolPosition::getAllValue()),
            'thousand' => 'integer|max:3',
            'symbol' => 'string',
            'exchange_rate' => 'numeric',
            'code' => 'string'
        ];

        $validator = Validator::make($body, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 400);
        }

        $currency = Currency::findOrFail($currency_id);

        // unset fields cannot be updated
        unset($body['created_at']);
        unset($body['updated_at']);

        $currency->update($body);

        return response()->json($currency);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $currency_id
     * @return \Illuminate\Http\Response
     */
    public function delete($currency_id)
    {
        $currency = Currency::findOrFail($currency_id);

        $currency->delete();

        return response()->json(['message' => 'Success']);
    }
}
