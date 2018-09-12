<?php
 
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\User;
 
class Authenticate
{
    protected $auth;
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    public function handle($request, Closure $next, $guard = null)
    {
        if ($this->auth->guard($guard)->guest()) {
        	$token = $public_key = $request->header( 'php-auth-user' );
            if ($token != null) {
                try {
                    
                    $check_token = User::where('api_token', $token)->first();
                    if ($check_token) {
	                    return $next($request);
                     
                    } else {
	                    $res['status'] = false;
	                    $res['message'] = 'Por favor haga login.';
	                    return response($res, 401);
                    }
                } catch (\Illuminate\Database\QueryException $ex) {
                    $res['status'] = false;
                    $res['message'] = $ex->getMessage();
                    return response($res, 500);
                }
            } else {
                $res['status'] = false;
                $res['message'] = 'Por favor envie el token.';
                return response($res, 401);
            }
        }
        return $next($request);
    }
}