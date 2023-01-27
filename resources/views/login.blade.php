<!DOCTYPE html>
<html lang="zh">
<head>
    @include('components.header')
    <title>登录 - Madoka</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
<iframe style="display: none" frameborder="no" border="0" marginwidth="0" marginheight="0" width=330 height=86 src="//music.163.com/outchain/player?type=2&id=22731512&auto=1&height=66"></iframe>
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
</body>
</html>
