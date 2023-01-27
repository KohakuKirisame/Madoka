<!DOCTYPE html>
<html lang="zh">
<head>
    @include('components.header')
    <meta charset="UTF-8">
    <title>军事</title>
    <script type="application/javascript" src="{{asset('js/military.js')}}"></script>
</head>
<body>
@include('components.nav')
<div class="container my-4">
    <h1 class="text-center">舰队</h1>
</div>
<div class="container my-4 py-4 rounded shadow-lg" style="background: #FFFFFF">
    <ul class="list-group list-group-flush">
        <li class="list-group-item">
            <div class="row">
                <h5 class="col-2 text-center">舰队</h5>
                <h5 class="col-2 text-center">位置</h5>
                <h5 class="col-2 text-center">超光速类型</h5>
                <h5 class="col-2 text-center">舰船数量</h5>
                <h5 class="col-2 text-center">作战电脑</h5>
                <h5 class="col-2 text-center">详情</h5>
            </div>
        </li>
        @foreach($fleets as $fleet)
            <li class="list-group-item">
                <div class="row">
                    @if($privilege==3)
                        <p class="col-2 text-center">{{$fleet['name']}}</p>
                    @else
                        <div class="col-2">
                            <input type="text" class="form-control" id="fleetName-{{$fleet['id']}}" value="{{$fleet['name']}}" onchange="changeFleetName({{$fleet['id']}})"/>
                        </div>
                    @endif
                    <p class="col-2 text-center">{{$fleet['position']}}</p>
                    <div class="col-2">
                        <select class="form-select" aria-label="ftlSelect" id="fleetFTL-{{$fleet['id']}}" onchange="changeFleetFTL({{$fleet['id']}})">
                            @foreach($ftls as $ftl)
                                @if($ftl == $fleet['ftl'])
                                    <option selected>{{$fleet['ftl']}}</option>
                                @else
                                    <option value="{{$ftl}}">{{$ftl}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <p class="col-2 text-center">{{$fleet['ships']}}</p>
                    <div class="col-2">
                        <select class="form-select" aria-label="computerSelect" id="fleetComputer-{{$fleet['id']}}"  onchange="changeFleetComputer({{$fleet['id']}})">
                            @foreach($computers as $computer)
                                @if($fleet['computer'] == $computer['localization'])
                                    <option selected>{{$computer['localization']}}</option>
                                @else
                                    <option value="{{$computer['id']}}">{{$computer['localization']}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <p class="col-2 text-center">
                        <button class="btn btn-primary" type="button" onclick="readFleet({{$fleet['id']}},{{$privilege}})">详情</button>
                    </p>
                </div>
            </li>
        @endforeach
    </ul>
</div>
<div class="container my-4">
    <h1 class="text-center">陆军</h1>
</div>
<div class="container my-4 py-4 rounded shadow-lg" style="background: #FFFFFF">
    <ul class="list-group list-group-flush">
        <li class="list-group-item">
            <div class="row">
                <h5 class="col-2 text-center">陆军</h5>
                <h5 class="col-2 text-center">位置</h5>
                <h5 class="col-2 text-center">数量</h5>
                <h5 class="col-2 text-center">伤害</h5>
                <h5 class="col-2 text-center">生命</h5>
                <h5 class="col-2 text-center">操作</h5>
            </div>
        </li>
        @foreach($armys as $army)
            <li class="list-group-item">
                <div class="row">
                    @if($privilege==3)
                        <p class="col-2 text-center">{{$army['name']}}</p>
                    @else
                        <div class="col-2">
                            <input type="text" class="form-control" id="armyName-{{$army['id']}}" value="{{$army['name']}}" onchange="changeArmyName({{$army['id']}})"/>
                        </div>
                    @endif
                    <p class="col-2 text-center">{{$army['position']}}</p>
                    <p class="col-2 text-center">{{$army['quantity']}}</p>
                    <p class="col-2 text-center">{{$army['damage']}}</p>
                    <p class="col-2 text-center">{{$army['HP']}}</p>
                    <p class="col-2 text-center">
                        <button class="btn btn-primary" type="button" onclick="moveArmy({{$army['id']}},{{$privilege}})">移动</button>
                        <button class="btn btn-danger" type="button" onclick="deleteArmy({{$army['id']}})">解散</button>
                    </p>
                </div>
            </li>
        @endforeach
    </ul>
</div>
@include('components.footer')
<div class="modal fade" id="fleetModal" tabindex="-1" aria-labelledby="fleetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fleetName"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-8 container my-4 py-4 rounded shadow-lg">
                        <h6 class="text-center">舰队数据</h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="row">
                                    <h7 class="col text-center">船体</h7>
                                    <h7 class="col text-center">能量伤害</h7>
                                    <h7 class="col text-center">动能伤害</h7>
                                    <h7 class="col text-center">装甲</h7>
                                    <h7 class="col text-center">护盾</h7>
                                    <h7 class="col text-center">闪避</h7>
                                    <h7 class="col text-center">速度</h7>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="row">
                                    <p class="col text-center" id="hull"></p>
                                    <p class="col text-center" id="EDamage"></p>
                                    <p class="col text-center" id="PDamage"></p>
                                    <p class="col text-center" id="armor"></p>
                                    <p class="col text-center" id="shield"></p>
                                    <p class="col text-center" id="evasion"></p>
                                    <p class="col text-center" id="speed"></p>
                                </div>
                            </li>
                        </ul>
                        <div class="row">
                            <div class="col-6">
                                <h6 class="text-center">装填模块A</h6>
                                <div id="weaponA"></div>
                            </div>
                            <div class="col-6">
                                <h6 class="text-center">装填模块B</h6>
                                <div id="weaponB"></div>
                            </div>
                        </div>
                        <div class="row my-4 py-4">
                            <div class="col-4">
                                <button type="button" class="btn btn-light" data-bs-target="#fleetMergeModal" data-bs-toggle="modal" data-bs-dismiss="modal" onclick="getFleets('merge')">合并舰队</button>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-light" data-bs-target="#shipTransModal" data-bs-toggle="modal" data-bs-dismiss="modal">转移舰船</button>
                            </div>
                            <div class="col-4">
                                <button type="button" class="btn btn-danger" data-bs-target="#fleetDeleteModal" data-bs-toggle="modal" data-bs-dismiss="modal">删除舰队</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 container my-4 py-4 rounded shadow-lg">
                        <ul class="list-group list-group-flush" id="shipList"></ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div id="adminButton"></div>
                @if($privilege <= 1)
                    <button type="button" class="btn btn-primary" data-bs-target="#newShipModal" data-bs-toggle="modal" data-bs-dismiss="modal">新建船只</button>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="fleetMergeModal" tabindex="-1" aria-labelledby="fleetMergeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">选择舰队</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="fleets"></div>
            </div>
            <div class="modal-footer">
                <div id="adminButton"></div>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" href="#fleetModel">返回</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="shipTransModal" tabindex="-1" aria-labelledby="shipTransModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">选择船只</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="shipList2"></div>
            </div>
            <div class="modal-footer">
                <div id="adminButton"></div>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" href="#fleetModel">返回</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="fleetDeleteModal" tabindex="-1" aria-labelledby="fleetDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">删除舰队</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="color: red">您确定删除舰队吗？会摧毁所有舰队内舰船并不会返回任何资源！</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" onclick="fleetDelete()">确认</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@if($privilege <= 1)
    <div class="modal fade" id="newShipModal" tabindex="-1" aria-labelledby="newShipModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">新建船只</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @foreach($shipTypes as $type)
                        <button type="button" class="btn btn-light" onclick="adminNewShip('{{$type['type']}}')">{{$type['name']}}</button>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <div id="adminButton"></div>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" href="#fleetModel">返回</button>
                </div>
            </div>
        </div>
    </div>
@endif
</body>
</html>
