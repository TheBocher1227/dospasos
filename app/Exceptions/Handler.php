<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Exceptions\NotFoundHttpException;
use Throwable;
use Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
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
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Manejo de errores de validación
        if ($exception instanceof ValidationException) {
            Log::info($exception);
            return redirect()->back()->withErrors($exception->errors())->withInput();
        }

        // Manejo de errores de conexión a la base de datos
        if ($exception instanceof QueryException) {
            return redirect()->back()->with('error', 'DATA_001-Your request cannot be made at this time. Please try again later.');
        }

        // Manejo de error 404 (Página no encontrada)
        if ($exception instanceof NotFoundHttpException) {
            $requestedUrl = $request->path(); // Obtener la URL solicitada

            // Si la URL contiene mayúsculas, redirigir al login con el mensaje de error
            if ($requestedUrl !== strtolower($requestedUrl)) {
                return redirect()->route('login')->with('error', 'No se puede realizar la petición en este momento.');
            }

            return response()->view('errors.error404', [], 404);
        }

        // Manejo de error 419 (Token CSRF Expirado)
        if ($exception instanceof TokenMismatchException) {
            return redirect()->route('login')->with('error', 'AUTH_001-Tu sesión ha expirado. Por favor, inicia sesión nuevamente.');
        }

        // Manejo de demasiadas peticiones (429 Too Many Requests)
        if ($exception instanceof ThrottleRequestsException) {
            return redirect()->route('login')->with('error', 'Demasiadas peticiones realizadas. Por favor, inténtalo más tarde.');
        }

        // Comportamiento por defecto: pasar la excepción al manejador de Laravel
        return parent::render($request, $exception);
    }
}
