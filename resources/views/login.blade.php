<!DOCTYPE html>
<html lang="zh">
<head>
    @include('components.header')
    <title>登录 - Madoka</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="bgimg" id="bgimg"></div>
    <div class="container center-content rounded" onmouseover="$('#bgimg').addClass('imgblur')" onmouseout="$('#bgimg').removeClass('imgblur')">
        <div class="row justify-content-center">
            <div class="col-6 col-md-3 col-lg-2 my-4">
                <img src="{{asset("storage/img/Madoka.svg")}}">
            </div>
            <div class="w-100"></div>
            <a class="col-6 my-4 btn btn-primary btn-lg" href="{{$_ENV["REIMU_URL"]."/login?app=madoka&redirect=https://madoka.nbmun.cn/Action/Login"}}">用灵Reimu登录</a>
        </div>
    </div>
    @include('components.footer')
</html>
