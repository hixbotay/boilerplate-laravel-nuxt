<?php

namespace App\Exceptions;

use App\Helpers\LogHelper;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler {
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register() {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e) {
        // Http exceptions
        if ($e instanceof HttpException) {
            if ($e->getStatusCode() == 404) {
                return response()->json(['message' => 'Request path not found','error'=>$e->getMessage()], 404);
            }

            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
        // Guzzle exceptions
        if ($e instanceof ClientException) {
            return response()->json(json_decode($e->getResponse()->getBody()->getContents()), $e->getCode());
        }
        // Validation exceptions
        if ($e instanceof ValidationException) {
            return response()->json(['message'=>'Invalid request','errors'=>$e->errors()], 400);
        }
        LogHelper::info('error.txt','Exception '.$e->getMessage());
        return response()->json([
            'message' => config('app.debug')?$e->getMessage():"Something wrong",
            'type' => get_class($e),
            // 'trace' => $e->getTrace()
        ], 500);
    }

}
