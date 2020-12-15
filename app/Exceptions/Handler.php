<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;


class Handler extends ExceptionHandler
{
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
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function render($request, Throwable $exception){
        try {
            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException)
                return response()->json ([ 
                    'userMessage' => "Not Found",
                    'debugMessage' => "Not Found",
                    'data' 	  => null
                ], 404);
            elseif ($exception instanceof \Illuminate\Auth\AuthenticationException )
                return response()->json ([ 
                    'userMessage' => "Unauthorized",
                    'debugMessage' => "Unauthorized",
                    'data' 	  => null
                ], 401 );
            elseif ($this->isHttpException($exception))
                return response()->json ([ 
                    'userMessage' => "Unknown error",
                    'debugMessage' => "Unknown error",
                    'data' 	  => null
                ], 422 );
            else
                return response()->json ([ 
                    'userMessage' => "Internal server error",
                    'debugMessage' => $exception->getMessage(),
                    'data' 	  => null
                ], 500 );
                
        } catch (\Throwable $th) {
            return response()->json ([ 
                'userMessage' => "Internal server error",
                'debugMessage' => $th->getMessage(),
                'data' 	  => null
            ], 500 );
        }

    }
}
