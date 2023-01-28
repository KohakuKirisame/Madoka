<!DOCTYPE html>
<html lang="zh">
<head>
    @include('components.header')
    <title>市场 - Madoka</title>
    <script type="application/javascript" src="{{asset('js/market.js')}}"></script>
</head>
<body>
@include('components.nav')
<div class="container my-4">
    <h1 class="text-center">市场</h1>
</div>
<div class="container">
    <div class="card my-4">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    {{$market->owner}}
                </div>
                <div class="col-6">
                    @foreach($market->member as $member)
                        {{$member}}
                    @endforeach
                </div>
                <div class="col-6">
                    @foreach($market->planets as $planet)
                        {{$planet}}
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <ul class="list-group list-group-flush my-4">
        <li class="list-group-item">
            <div class="row">
                <h5 class="col-4 text-center">资源</h5>
                <h5 class="col-2 text-center">价格</h5>
                <h5 class="col-2 text-center">仓储</h5>
                <h5 class="col-2 text-center">需</h5>
                <h5 class="col-2 text-center">供</h5>
            </div>
        </li>
        @foreach($market->goods as $key=>$good)
            <li class="list-group-item">
                <div class="row">
                    <p class="col-4 text-center"><img src="{{asset("storage/img/resource/".$key.".png")}}" style="width: 24px"></p>
                    <p class="col-2 text-center">{{$good['price']}}</p>
                    <p class="col-2 text-center">{{$good['storage']}}</p>
                    <p class="col-2 text-center">{{$good['demandOrder']}}</p>
                    <p class="col-2 text-center">{{$good['supplyOrder']}}</p>
                </div>
            </li>
        @endforeach
    </ul>
    <ul class="list-group list-group-flush my-4">
        <li class="list-group-item">
            <div class="row">
                <p class="col-2 text-center">交易国</p>
                <p class="col-3 text-center">内容</p>
                <p class="col-5 text-center">路线</p>
                <p class="col-2 text-center">持续时间</p>
            </div>

        </li>
        @foreach($market->trades as $trade)
            <li class="list-group-item">
                <div class="row">
                    <p class="col-2 text-center">{{$trade["target"]}}</p>
                    <p class="col-3 text-center">{{$trade["content"][0]}}：{{$trade["content"][1]}}</p>
                    <p class="col-5 text-center">{{implode("->",$trade["path"])}}</p>
                    <p class="col-2 text-center">{{$trade["duration"]}}月</p>
                </div>
            </li>
        @endforeach
    </ul>
</div>
@include('components.footer')
</body>
</html>
