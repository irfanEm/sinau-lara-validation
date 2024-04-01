<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class FormController extends Controller
{

    public function form(): Response
    {
        return response()->view('form');
    }

    public function submitForm(Request $request): Response
    {
        $data = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        return response("OK", Response::HTTP_OK);
    }
    public function login(Request $request): Response
    {
        try{
            $data = $request->validate([
                'username' => 'required',
                'password' => 'required',
            ]);
            return response("OKE", Response::HTTP_OK);
        }catch(ValidationException $except){
            return response($except->errors(), Response::HTTP_BAD_REQUEST);
        }
    }
}
