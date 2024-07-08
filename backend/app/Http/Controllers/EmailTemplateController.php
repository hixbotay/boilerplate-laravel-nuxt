<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Enums\CurrencySymbolPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\EmailTemplate;

class EmailTemplateController extends Controller
{
    /**
     * Get a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = EmailTemplate::filter($request);

        $list = $query->get();

		return response()->json([
			'records' => $list,
		]);
    }


    /**
     * Creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $body = (array) json_decode($request->getContent());

       $body =  $request->validate([
            'name' => 'required|string',
            'subject' => 'required|string',
            'html_content' => 'required|string',
            'type' => 'required|string',
       ]);

        $template = EmailTemplate::create($body);

        return response()->json($template);
    }

    /**
     * Get the specified resource.
     *
     * @param  int  $template_id
     * @return \Illuminate\Http\Response
     */
    public function show(EmailTemplate $emailtemplate)
    {
        return response()->json($emailtemplate);
    }

    /**
     * Update a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $template_id)
    {
        $body = (array) json_decode($request->getContent());

        $rules = [
            'name' => 'string',
            'subject' => 'string',
            'json_content' => 'string',
            'html_content' => 'string',
            'status' => 'in:0,1'
        ];

        $validator = Validator::make($body, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 400);
        }

        $template = EmailTemplate::findOrFail($template_id);
        $template->update($body);

        return response()->json($template);
    }

    public function destroy(Request $request, EmailTemplate $emailtemplate)
    {
        if ($emailtemplate->delete()) {
            return response(['message' => 'Record is deleted']);
        }
        return response()->json(['message' => 'Delete error'], 405);
    }
}
