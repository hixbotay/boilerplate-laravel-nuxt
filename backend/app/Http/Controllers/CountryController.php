<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Country;

class CountryController extends Controller
{
    /**
     * Get a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $request->query->set('order_by', 'id');
        $request->query->set('order_type', 'asc');

        $query = Country::filter($request);

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
            'mobile_code' => 'required|string',
            'code' => 'required|string|unique:countries',
            'flag' => 'string'
        ];

        $validator = Validator::make($body, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 400);
        }

        $country = Country::create($body);

        return response()->json($country);
    }

    /**
     * Get the specified resource.
     *
     * @param  int  $country_id
     * @return \Illuminate\Http\Response
     */
    public function get($country_id)
    {
        $country = Country::findOrFail($country_id);

        return response()->json($country);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $country_id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $country_id)
    {
        $body = (array) json_decode($request->getContent());

        $rules = [
            'name' => 'string',
            'mobile_code' => 'string',
            'code' => 'string|unique:countries,code,' . $country_id,
            'flag' => 'string'
        ];

        $validator = Validator::make($body, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 400);
        }

        $country = Country::findOrFail($country_id);

        // unset fields cannot be updated
        unset($body['created_at']);
        unset($body['updated_at']);

        $country->update($body);

        return response()->json($country);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $country_id
     * @return \Illuminate\Http\Response
     */
    public function delete($country_id)
    {
        $country = Country::findOrFail($country_id);

        $country->delete();

        return response()->json(['message' => 'Success']);
    }

    public function getChoose(Request $request)
    {
        $query = Country::where('code', 'VN');

		return response()->json([
			'records' => $query->get(),
		]);
    }
}
