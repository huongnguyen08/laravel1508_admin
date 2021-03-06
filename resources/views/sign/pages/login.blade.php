@extends('sign.layout') @section('title','Đăng nhập admin') @section('content')
<div class="card card-container">
	@if($errors->any())
	<div class="alert alert-danger">
		@foreach($errors->all() as $err) {{$err}}
		<br> @endforeach
	</div>
	@endif
	<img id="profile-img" class="profile-img-card" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" /> @if(Session::has('success'))
	<div class="alert alert-success">{{Session::get('success')}}</div>
	@endif @if(Session::has('error'))
	<div class="alert alert-danger">{{Session::get('error')}}</div>
	@endif
	<form class="form-signin" method="post" action="{{route('adminLogin')}}">
		{{csrf_field()}}
		<span id="reauth-email" class="reauth-email"></span>
		<input type="email" name="inputEmail" class="form-control" placeholder="Email address" required autofocus>
		<input type="password" name="inputPassword" class="form-control" placeholder="Password" required>

		<button class="btn btn-lg btn-primary btn-block btn-signin" type="submit" name="login">Sign in</button>
	</form>
	<!-- /form -->
	<a href="login/google" class="forgot-password">
		Đăng nhập bằng GOOGLE
	</a>
	<a href="login/facebook" class="forgot-password">
		Đăng nhập bằng FACEBOOK
	</a>
	<a href="#" class="forgot-password">
		Forgot the password?
	</a>
</div>
<!-- /card-container -->
@endsection