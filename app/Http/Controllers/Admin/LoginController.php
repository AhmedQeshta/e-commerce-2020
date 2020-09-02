<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function  getLogin(){

        return view('admin.auth.login');
    }

    ########## to use in tinker in power Shell ###############
    public function save(){
        $admin = new  App\Models\Admin();
        $admin -> name ="A7med Qeshta";
        $admin -> email ="ahmedqeshta0592@gmail.com";
        $admin -> password = bcrypt("0592157001");
        $admin -> save();

    }

    public function login(LoginRequest $request){

        $remember_me = $request->has('remember_me') ? true : false;

        if (auth()->guard('admin')->attempt(['email' => $request->input("email"), 'password' => $request->input("password")], $remember_me)) {
           // notify()->success('تم الدخول بنجاح  ');
            return redirect() -> route('admin.dashboard');
        }
       // notify()->error('خطا في البيانات  برجاء المجاولة مجدا ');
        return redirect()->back()->with(['error' => 'هناك خطا بالبيانات']);
    }

    public function logout(Request $request){
        if ($this->guard('admin')){
            $this->guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new Response('', 204)
            : redirect('/');
    }
    protected function guard(){
        return Auth::guard();
    }
    protected function loggedOut(Request $request){
        //
    }
}
