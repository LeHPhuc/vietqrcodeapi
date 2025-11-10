<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Middleware\Authenticate as AuthenticateMiddleware;
use Illuminate\Auth\AuthenticationException;   
use Illuminate\Auth\Access\AuthorizationException; 
use Laravel\Sanctum\Exceptions\MissingAbilityException;         
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => AuthenticateMiddleware::class, 
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (AuthenticationException $e, $request) {
          if ($request->expectsJson()) {
              return response()->json([
                  'error'   => 'unauthenticated',   
                  'message' => 'Thiếu hoặc token không hợp lệ.',
              ], 401);
          }
      });
      // 403 - ĐANG GẶP: AccessDeniedHttpException (đặt trước handler HttpException chung)
      $exceptions->renderable(function (AccessDeniedHttpException $e, $request) {
          return response()->json(['ok'=>false,'error'=>'forbidden','message'=>'Bạn không có quyền thực hiện hành động này.'], 403);
      });
    })->create();
