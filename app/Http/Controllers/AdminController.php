<?php
namespace App\Http\Controllers;
//use Request;
use Illuminate\Http\Request;
use App\User;
use Hash;
use Auth;
use App\Foods;

class AdminController extends Controller{
    
    public function getAdminLogin(){
        return view('sign.pages.login');
    }
    public function getAdminRegister(){
        return view('sign.pages.register');
    }

    public function postAdminRegister(Request $req){
        $req->validate([
            'fullname'=>'required|min:10|max:100',
            'email'=>'required|unique:users',
            'username'=>'required|unique:users|min:5|max:50',
            'password'=>'required|min:6|max:50',
            'confirm_password'=>'required|same:password'

        ],[
            'email.unique'=>'Email đã có người sử dụng',
            'username.unique'=>'Username đã có người sử dụng',
            'fullname.required'=>'Họ tên không được rỗng',
            'confirm_password.same'=>'Mat khau khong giong nhau'
        ]);
        //==============================================
        $user = new User;
        $user->username = $req->username;
        $user->fullname = $req->fullname;
        $user->birthdate = date('Y-m-d',strtotime($req->birthdate));
        $user->gender = $req->gender;
        $user->address = $req->address;
        $user->email = $req->email;
        $user->phone = $req->phone;
        $user->role = 0;
        $user->password = Hash::make($req->password);
        $user->save();
        return redirect()->route('adminLogin')
                ->with('success','Dang ki thanh cong');
    }

    public function postAdminLogin(Request $req){
        $req->validate([
            'inputEmail'=>'required',
            'inputPassword'=>'required|min:6|max:50'

        ],[
            'inputEmail.required'=>'Vui long nhap email',
            'inputPassword.min'=>'Mat khau it nhat :min ki tu',
        ]);
        $data = [
            'email'=>$req->inputEmail,
            'password'=>$req->inputPassword
        ];
        $check = Auth::attempt($data);
        
        if($check && Auth::user()->role==1){
            return redirect()->route('homepage');
        }
        elseif(Auth::check() && Auth::user()->role==0){
            return redirect()->route('notAdmin');
            //echo "Ban da dang nhap nhung ko co admin";
        }
        else{
            return redirect()->route('adminLogin')
            ->with('error','Dang nhap khong thanh cong');
        }

    }
    function getAdminLogout(){
        Auth::logout();
        return redirect()->route('adminLogin');
    }



    public function getIndex(){
        return view('pages.index');
    }


    public function getListProduct(){
        $foods = Foods::all();
        return view('pages.list-product',compact('foods'));
    }

    public function getAddProduct(){
        return view('pages.add-product');
    }
}

?>