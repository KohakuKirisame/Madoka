<!DOCTYPE html>
<html lang="zh">
<head>
	@include('components.header')
	<title>主面板 - Madoka</title>
    <script type="application/javascript" src="{{asset('js/dashboard.js')}}"></script>
</head>
<body>
@include('components.nav')
<div class="container my-4">
    <h2 class="text-center">主面板</h2>
    @if($privilege == 0)
        <div class="row">
            <button type="button" class="btn btn-danger" data-bs-dismiss="model" onclick="mainFunction()">Game Start</button>
        </div>
    @endif
</div>
<div class="row">
    <div class="container col-3 my-4 py-4 rounded shadow-lg" style="background:{{$country['color']}}">
        <div class="row">
            <div class="col">
                <img src="storage/img/countries/{{$country['tag']}}.png" width="100px" style="display:inline">&nbsp
            </div>
            <div class="col text-center">
                <h4 class="text-center h4">{{$country['name']}}</h4>
                <p class="text-center" style="display:inline">掌握恒星系{{count(json_decode($country['stars'],true))}}个；掌握行星{{count($country['planets'])}}个</p>
            </div>
        </div>
    </div>
    <div class="container col-4 my-4 py-4 rounded shadow-lg" style="background:#FFF">
        <div class="row">
            <h5 class="text-center">国家实力</h5>
            <span class="badge bg-light text-dark h6">经济实力：{{$country['economyPower']}}</span>
            <span class="badge bg-light text-dark h6">军事实力：{{$country['militaryPower']}}</span>
        </div>
    </div>
    <div class="container col-4 my-4 py-4 rounded shadow-lg" style="background: #FFFFFF">
        <h5 class="text-center" style="display:inline">国家资源状况</h5><br>
        <h6>
        @foreach($country['resource'] as $key => $value)
            @if($value >=0)
                <span class="badge bg-success text-dark"><img src="storage/img/resource/{{$key}}.png" width="20px">{{round($value)}}</span>
            @else
                <span class="badge bg-danger text-dark"><img src="storage/img/resource/{{$key}}.png" width="20px">{{round($value)}}</span>
            @endif
        @endforeach
        </h6>
    </div>
</div>
<div class="row">
    @if(count($country['atWarWith']) == 0)
        <div class="container col-2 my-4 py-4 rounded shadow-lg" style="background: lightgreen">
            <h5 class="text-center" style="display:inline">我们的国家处于和平</h5><br>
        </div>
    @else
        <div class="container col-2 my-4 py-4 rounded shadow-lg" style="background: red">
            <h5 class="text-center text-white" style="display:inline">我们正在进行战争！</h5><br>
            @foreach($country['atWarWith'] as $key => $value)
                <span class="badge bg-danger text-light"><img src="storage/img/countries/{{$value[0]}}.png" width="20px">{{$value[1]}}</span>
            @endforeach
        </div>
    @endif
    @if(count($country['alliedWith']) == 0)
        <div class="container col-2 my-4 py-4 rounded shadow-lg" style="background: yellow">
            <h5 class="text-center" style="display:inline">我们的国家没有盟友</h5><br>
        </div>
    @else
        <div class="container col-2 my-4 py-4 rounded shadow-lg" style="background: lightgreen">
            <h5 class="text-center text-white" style="display:inline">我们的盟友</h5><br>
            @foreach($country['alliedWith'] as $key => $value)
                <span class="badge bg-success text-light"><img src="storage/img/countries/{{$value[0]}}.png" width="20px">{{$value[1]}}</span>
            @endforeach
        </div>
    @endif
</div>
<div class="container">
    <div class="row">
        <div class="col-md-9">
            <p>我草！你是：@if($privilege==0)超级管理员@elseif($privilege==1)管理员@elseif($privilege==2)代表@elseif($privilege==3)媒体@endif</p>
        </div>
    </div>
</div>
@include('components.footer')
</body>
</html>
