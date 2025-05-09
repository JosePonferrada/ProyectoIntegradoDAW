<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        
        // Add custom rendering for 404 errors
        $this->renderable(function (NotFoundHttpException $e) {
            return Inertia::render('Errors/404');
        });

        // Add custom rendering for 403 errors
        $this->renderable(function ($e) {
          if ($e instanceof AuthorizationException || 
              $e instanceof AccessDeniedHttpException ||
              $this->isHttpException($e) && $e->getStatusCode() === 403) {
              return Inertia::render('Errors/Forbidden');
          }
          return null;
      });
    }
}