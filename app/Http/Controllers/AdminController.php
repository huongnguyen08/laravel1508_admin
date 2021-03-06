<?php
namespace App\Http\Controllers;
//use Request;
use Illuminate\Http\Request;
use App\User;
use Hash;
use Auth;
use App\Foods;
use App\FoodType;
use App\PageUrl;
use App\Functions;
use Mail;
use Socialite;
use App\SocialProvider;


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
        //$loaiSP = FoodType::all();

        return view('pages.list-product',compact('foods'));
    }

    public function getAddProduct(){
        $types = FoodType::all();
        return view('pages.add-product',['types'=>$types]);
    }

    public function postAddProduct(Request $req){
    
        $f = new Functions;

        $url = new PageUrl;
        $url->url = $f->changeTitle($req->name);
        $url->save();
        
        $food = new Foods;
        $food->id_type = $req->loai;
        $food->id_url = $url->id;
        $food->name = $req->name;
        $food->summary = $req->summary;
        $food->detail = $req->detail;
        $food->price = $req->price;
        $food->promotion_price = isset($req->promotion_price) ?$req->promotion_price : 0 ;
        $food->promotion = $req->promotion;
        $food->update_at = date("Y-m-d");
        $food->unit = $req->unit;
        $food->today = isset($req->today) ? $req->today : 0;
        
        if($req->hasFile('hinh')){
            $image = $req->file('hinh');
            $image->move('admin-master/img/hinh_mon_an/',time().$image->getClientOriginalName());
            $food->image = time().$image->getClientOriginalName();
        }
        else{
            $food->image = '';
        }
        $food->save();
        return redirect()->route('listProduct')->with('success',"Thêm thành công");

    }

    public function getListProductByType($id){
        $foods = Foods::where('id_type',$id)->get();
        //dd($foods);
        $type = FoodType::where('id',$id)->first();
        return view('pages.list-product',compact('foods','type'));
        
    }

    public function getEditProduct($id){
        $food = Foods::where('id',$id)->first();
        return view('pages.edit-product',compact('food'));
    }

    public function postEditProduct(Request $req,$id){
        $food = Foods::where('id',$id)->first();
        $url = PageUrl::where('id',$food->id_url)->first();

        $f = new Functions;
        $url->url = $f->changeTitle($req->name);
        $url->save();
        
        $food->id_type = $req->loai;
        $food->name = $req->name;
        $food->summary = $req->summary;
        $food->detail = $req->detail;
        $food->price = $req->price;
        $food->promotion_price = isset($req->promotion_price) ?$req->promotion_price : 0 ;
        $food->promotion = $req->promotion;
        $food->update_at = date("Y-m-d");
        $food->unit = $req->unit;
        $food->today = isset($req->today) ? $req->today : 0;
        
        if($req->hasFile('hinh')){
            $image = $req->file('hinh');
            $image->move('admin-master/img/hinh_mon_an/',time().$image->getClientOriginalName());
            $food->image = time().$image->getClientOriginalName();
        }
        $food->save();
        return redirect()->route('listProductByType',$food->id_type)->with('success',"Cập nhật thành công");
    }

    public function getDeleteProduct($id){
        $product = Foods::where('id',$id)->first();
        if($product){
            $product->delete();
            echo "success";
        }
        else{
            echo "error";
        }
        
    }

    public function sendMail(){
        $products = Foods::where('id',2)->first();//s->toArray();
       // dd($products);
        Mail::send('pages.send_email', ['products' => $products], function ($message)
        {
            $message->from('huonghuong08.php@gmail.com', 'Họ tên');
            $message->to('huongnguyen08.cv@gmail.com','ngoc huong');
            $message->subject('Mail demo');
        });
        echo 'đã gửi';
    }

    public function redirectToProvider($providers){
        return Socialite::driver($providers)->redirect();
    }

    public function  handleProviderCallback($providers){
        try{
            $socialUser = Socialite::driver($providers)->user();
            //dd($socialUser);
            //return $user->getEmail();
        }
        catch(\Exception $e){
            //dd($e->getResponse()->getBody()->getContents());
            return redirect()->route('adminLogin')->with(['flash_message'=>"Đăng nhập không thành công"]);
        }
        $socialProvider = SocialProvider::where('provider_id',$socialUser->getId())->first();
        if(!$socialProvider){
            //tạo mới
            $user = User::where('email',$socialUser->getEmail())->first();
            if($user){
            return redirect()->route('login')->with(['flash_level'=>'danger','flash_message'=>"Email đã có người sử dụng"]);
            }
            else{
                $user = new User();
                $user->email = $socialUser->getEmail();
                $user->fullname = $socialUser->getName();
                $user->username = $socialUser->getEmail();
                // if($providers == 'google'){
                //     $image = explode('?',$socialUser->getAvatar());
                //    // $user->avatar = $image[0];
                // }
                // $user->avatar = $socialUser->getAvatar();
                $user->save();
            }
            $provider = new SocialProvider();
            $provider->provider_id = $socialUser->getId();
            $provider->provider = $providers;
            $provider->email = $socialUser->getEmail();
            $provider->save();
        }
        else{
            $user = User::where('email',$socialUser->getEmail())->first();
        }
        Auth()->login($user);
        return redirect()->route('homepage')->with(['flash_level'=>'success','flash_message'=>"Đăng nhập thành công"]);

    }
}

?>