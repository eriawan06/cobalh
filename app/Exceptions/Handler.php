<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if (env('APP_DEBUG')) {
            return parent::render($request, $exception);
        }

        $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        if ($exception instanceof HttpResponseException) {
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            $status = Response::HTTP_METHOD_NOT_ALLOWED;
            $exception = new MethodNotAllowedHttpException([], 'HTTP_METHOD_NOT_ALLOWED', $exception);
        } elseif ($exception instanceof NotFoundHttpException) {
            $status = Response::HTTP_NOT_FOUND;
            $message = $exception->getMessage() == null ? 'HTTP_NOT_FOUND' : $exception->getMessage();
            $exception = new NotFoundHttpException($message, $exception);
        } elseif ($exception instanceof AuthorizationException) {
            $status = $exception->getCode() == null ? Response::HTTP_FORBIDDEN : $exception->getCode();
            $message = $exception->getMessage() == null ? 'HTTP_FORBIDDEN' : $exception->getMessage();
            $exception = new AuthorizationException($message, $exception);
        } elseif ($exception instanceof BadRequestException) {
            $status = $exception->getCode() == null ? Response::HTTP_BAD_REQUEST : $exception->getCode();
            $message = $exception->getMessage() == null ? 'HTTP_BAD_REQUEST' : $exception->getMessage();
            $exception = new BadRequestException($message, $status, $exception);
        } elseif ($exception instanceof ValidationException) {
            $status = $exception->status;
        } elseif ($exception) {
            $exception = new HttpException($status, 'HTTP_INTERNAL_SERVER_ERROR');
        }

        $error = $exception->getMessage();
        if ($exception instanceof ValidationException) {
            $error = $exception->validator;
        }
        return response()->json([
            'is_success' => false,
            'error' => $error,
        ], $status);
    }
}
