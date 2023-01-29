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
</div>
<div class="row">
    <div class="container col-4 my-4 py-4 rounded shadow-lg" style="background:{{$country['color']}}">
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
    <div class="container col-2 my-4 py-4 rounded shadow-lg" style="background: #FFFFFF">
        <h5 class="text-center" style="display:inline">国库现金储备</h5><br>
        <span class="badge bg-light text-dark"><img src="storage/img/resource/energy.png" width="20px">{{$country['energy']}}</span>
    </div>
    <div class="container col-4 my-4 py-4 rounded shadow-lg" style="background: #FFFFFF">
        <h5 class="text-center" style="display:inline">国库资源储备</h5><br>
        @foreach($country['storage'] as $key => $value)
            @if($value >=0)
                <span class="badge bg-success text-dark"><img src="storage/img/resource/{{$key}}.png" width="20px">{{$value}}</span>
            @else
                <span class="badge bg-danger text-dark"><img src="storage/img/resource/{{$key}}.png" width="20px">{{$value}}</span>
            @endif
        @endforeach
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
    <div class="container col-3 my-4 py-4 rounded shadow-lg" style="background: #FFF">
        <h5 class="text-center" style="display:inline">我国各种族权利</h5><br>
        @foreach($country['species'] as $key => $value)
            <span class="badge bg-light text-dark "><h6>{{$value['name']}}：{{$value['right']}}</h6></span><br>
        @endforeach
    </div>
    <div class="container col-4 my-4 py-4 rounded shadow-lg" style="background: #FFF">
        <h5 class="text-center" style="display:inline">税率</h5><br>
        <span class="badge bg-light text-dark ">
            <div style="display:inline">企业税率</div>&nbsp<div id="dTax" style="display:inline">{{$country['districtTax']}}</div>
            <input style="display:inline" type="range" class="form-range" min="0" max="1" step="0.01" id="districtTax" value="{{$country['districtTax']}}" onchange="changeTax('{{$country['tag']}}','districtTax')"/>&nbsp
        </span><br>
        <span class="badge bg-light text-dark ">
            <div style="display:inline">上层税率</div>&nbsp<div id="upTax" style="display:inline">{{$country['upPopTax']}}</div>
            <input style="display:inline" type="range" class="form-range" min="0" max="1" step="0.01" id="upPopTax" value="{{$country['upPopTax']}}" onchange="changeTax('{{$country['tag']}}','upPopTax')"/>&nbsp
        </span><br>
        <span class="badge bg-light text-dark ">
            <div style="display:inline">中层税率</div>&nbsp<div id="midTax" style="display:inline">{{$country['midPopTax']}}</div>
            <input style="display:inline" type="range" class="form-range" min="0" max="1" step="0.01" id="midPopTax" value="{{$country['midPopTax']}}" onchange="changeTax('{{$country['tag']}}','midPopTax')"/>&nbsp
        </span><br>
        <span class="badge bg-light text-dark ">
            <div style="display:inline">下层税率</div>&nbsp<div id="lowTax" style="display:inline">{{$country['lowPopTax']}}</div>
            <input style="display:inline" type="range" class="form-range" min="0" max="1" step="0.01" id="lowPopTax" value="{{$country['lowPopTax']}}" onchange="changeTax('{{$country['tag']}}','lowPopTax')"/>&nbsp
        </span><br>
    </div>
</div>
<div class="row">
    <div class="container col-10 my-4 py-4 rounded shadow-lg" style="background: #FFF">
        <h5 class="text-center" style="display:inline">已生效的国家修正</h5><br>
        @foreach($country['ModifierList'] as $key => $value)
            <span class="badge bg-light text-dark ">
                <h5>{{$value['name']}}</h5>
                @foreach($value['modifier'] as $key2=>$value2)
                    {{$key2}} : {{$value2*100}}%
                @endforeach
            </span>
        @endforeach
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-3">
            <p>123</p>
        </div>
        <div class="col-md-9">
            <p>我草！你是：@if($privilege==0)超级管理员@elseif($privilege==1)管理员@elseif($privilege==2)代表@elseif($privilege==3)媒体@endif</p>
        </div>
        @if($privilege == 0)
            <button type="button" class="btn btn-danger" data-bs-dismiss="model" onclick="mainFunction()">Game Start</button>
        @endif
    </div>
</div>
@include('components.footer')
</body>
</html>
