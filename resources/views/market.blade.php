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
<div class="row">
    <div class="container col-6 my-4 py-4 rounded shadow-lg" style="background:#FFF}}">
        <div class="row">
            <div class="text-center">
                <h4 class="text-center h4">{{$market->owner}}市场</h4><br>
                @foreach($market->member as $member)
                    <img src="storage/img/countries/{{$member}}.png" style="display: inline" width="40px" />
                @endforeach
            </div>
        </div>
    </div>
    <div class="container col-4 my-4 py-4 rounded shadow-lg" style="background:#FFF}}">
        <div class="row">
            <div class="text-center">
                <h4 class="text-center h4">包含行星</h4><br>
                @foreach($market->planets as $planet)
                    {{$planet}};
                @endforeach
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="container col-5 my-4 py-4 rounded shadow-lg" style="background:#FFF}}">
        <ul class="list-group list-group-flush my-4">
            <li class="list-group-item">
                <div class="row">
                    <p class="col-2 text-center">交易国</p>
                    <p class="col-2 text-center">内容</p>
                    <p class="col-2 text-center">路线长度</p>
                    <p class="col-2 text-center">持续时间</p>
                    <p class="col-2 text-center">删除</p>
                </div>
            </li>
            @foreach($market->trades as $trade)
                <li class="list-group-item">
                    <div class="row">
                        <p class="col-2 text-center">{{$trade["target"]}}</p>
                        <p class="col-2 text-center">{{$trade["content"][0]}}：{{$trade["content"][1]}}</p>
                        <p class="col-2 text-center">{{count($trade["path"])}}</p>
                        <p class="col-2 text-center">{{$trade["duration"]}}月</p>
                        <button class="btn btn-danger col-2" type="button" onclick="deleteTrade('{{$market->owner}}','{{$trade["target"]}}','{{$trade["content"][0]}}',{{$trade["content"][1]}})">取消</button>
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="row">
            <button class="btn btn-success" type="button" data-bs-target="#newTradeModal" data-bs-toggle="modal">新建贸易</button>
        </div>
    </div>
    <div class="container col-6 my-4 py-4 rounded shadow-lg" style="background:#FFF}}">
        <ul class="list-group list-group-flush my-4">
            <li class="list-group-item">
                <div class="row">
                    <h5 class="col-3 text-center">资源</h5>
                    <h5 class="col-3 text-center">市场价格</h5>
                    <h5 class="col-3 text-center">需求订单</h5>
                    <h5 class="col-3 text-center">供应订单</h5>
                </div>
            </li>
            @foreach($market->goods as $key=>$good)
                <li class="list-group-item">
                    <div class="row">
                        <p class="col-3 text-center"><img src="{{asset("storage/img/resource/".$key.".png")}}" style="width: 24px"></p>
                        <p class="col-3 text-center">{{$good['price']}}</p>
                        <p class="col-3 text-center">{{$good['demandOrder']}}</p>
                        <p class="col-3 text-center">{{$good['supplyOrder']}}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@include('components.footer')
<div class="modal fade" id="newTradeModal" tabindex="-1" aria-labelledby="newTradeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新建贸易路线</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        @foreach($markets as $m)
                            <button type="button" class="btn btn-light" id="{{$m['owner'][0]}}" onclick="readMarket('{{$m['owner'][0]}}')"><img src="storage/img/countries/{{$m['owner'][0]}}.png" width="40px"/>{{$m['owner'][1]}}</button></br>
                        @endforeach
                    </div>
                    <div class="col" id="prices"></div>
                </div>
            </div>
            <div class="modal-footer">
                <div id="country"></div><div id="resource" style="display: inline"></div>购买数量
                <div class="row">
                    <input type="number" class="form-control col-1" id="value" value="" style="display: inline"/>
                </div>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="newTrade('{{$market->owner}}')">确认</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">返回</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
