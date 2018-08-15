<?php
	
	namespace App\Http\Controllers;
	
	use Illuminate\Http\Request;
	use Illuminate\Validation\ValidationException;
	use Illuminate\Support\Facades\Hash;
	use App\Ciudad;
	
	class CiudadController extends Controller {
		public function index(){
			
			$ciudades  = Ciudad::all();
			
			return response()->json($ciudades);
			
		}
	}