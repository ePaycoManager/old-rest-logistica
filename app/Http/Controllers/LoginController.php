<?php
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\User;
 
class LoginController extends Controller
{
    public function login(Request $request)
    {
 
      $rules = [
          'public_key' => 'required',
      ];
 
        $customMessages = [
           'required' => ':attribute tidak boleh kosong'
      ];
        $this->validate($request, $rules, $customMessages);
         $public_key    = $request->input('public_key');
        try {
            $login = User::where('public_key', $public_key)->first();
            if ($login) {
                if ($this->validarCliente($public_key)) {
                    try {
                        $api_token = sha1($login->id_user.time());

                        $create_token = User::where('id', $login->id_user)->update(['api_token' => $api_token]);
                        $res['status'] = true;
                        $res['message'] = 'Success login';
                        $res['data'] =  $login;
                        $res['api_token'] =  $api_token;

                        return response($res, 200);
 

                    } catch (\Illuminate\Database\QueryException $ex) {
                        $res['status'] = false;
                        $res['message'] = $ex->getMessage();
                        return response($res, 500);
                    }
                } else {
                        $res['success'] = false;
                        $res['message'] = 'No ha validado el comercio correctamente.';
                        return response($res, 401);
                }
                
            } else {
               $validar_comercio = $this->validarCliente($public_key);
               if($validar_comercio){
                $user = new User();
                $user->public_key= $public_key;
                $user->save(); 
                $res['success'] = true;
                $res['message'] = 'Se ha validado el comercio correctamente, por favor envie la petición nuevamente para retornar el token';
                return response($res,200);
               } else {
                    $res['success'] = false;
                $res['message'] = 'No ha validado el comercio correctamente.';
                return response($res,401);
               }
               
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            $res['success'] = false;
            $res['message'] = $ex->getMessage();
            return response($res, 500);
        }
    }


    public function validarCliente($public_key){
            // aca logica que llame al rest-pagos y valide el cliente

            return true;
    }
}