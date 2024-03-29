<!DOCTYPE html>
<html>
<head>
    @include('components.header')
    <title>星球 - Madoka</title>
    <script type="application/javascript" src="{{asset('js/planet.js')}}">
    </script>
</head>
<body>
@include('components.nav')
<div class="container my-4">
    <h1 class="text-center">星球</h1>
</div>
<div class="container mt-4 py-4 rounded shadow-lg mb-5" style="background: #FFFFFF">
    <ul class="list-group list-group-flush">
        <li class="list-group-item">
            <div class="row">
                <h5 class="col-3 text-center">星球</h5>
                <h5 class="col-3 text-center">位置</h5>
                <h5 class="col-4 text-center">星球结余</h5>
                <h5 class="col-2 text-center">操作</h5>
            </div>
        </li>
        @foreach($planets as $planet)
        <li class="list-group-item">
            <div class="row">
                @if($privilege==3)
                    <img src="storage/img/countries/{{$planet['owner']}}.png" width="50px" style="display: inline"><p class="col-3 text-center">{{$planet['name']}}</p>
                @else
                <div class="col-3">

                    <img src="storage/img/countries/{{$planet['owner']}}.png" width="50px" style="display: inline"><input type="text" class="form-control" id="planetName-{{$planet['id']}}" value="{{$planet['name']}}" onchange="changePlanetName({{$planet['id']}})" style="display: inline"/>
                </div>
                @endif
                <p class="col-3 text-center">{{$planet['position']}}</p>
                <p class="col-4 text-center">
                    @foreach($planet['resource'] as $key=>$value)
                        @if($value>0)
                            <span class="badge bg-success text-dark" style="width: 15%"><img src="storage/img/resource/{{$key}}.png" width="20px">
                                @if($privilege <= 1)
                                    <input class="form-control form-control-sm" type="text" id="planet-{{$planet['id']}}-{{$key}}" value="{{round($value,1)}}" onchange="updateRes({{$planet['id']}},'{{$key}}')">
                                @else
                                    {{$value}}
                                @endif
                            </span>
                        @elseif($value<0)
                            <span class="badge bg-danger text-dark" style="width: 15%"><img src="storage/img/resource/{{$key}}.png" width="20px">
                                @if($privilege <= 1)
                                    <input class="form-control form-control-sm" type="text" id="planet-{{$planet['id']}}-{{$key}}" value="{{round($value,1)}}" onchange="updateRes({{$planet['id']}},'{{$key}}')">
                                @else
                                    {{$value}}
                                @endif
                            </span>
                        @else
                            @if ($privilege <=1)
                                <span class="badge bg-danger text-dark" style="width: 15%"><img src="storage/img/resource/{{$key}}.png" width="20px">
                                    <input class="form-control form-control-sm" type="text" id="planet-{{$planet['id']}}-{{$key}}" value="{{round($value,1)}}" onchange="updateRes({{$planet['id']}},'{{$key}}')">
                                </span>
                            @endif
                        @endif
                    @endforeach
                </p>
                <p class="col-2 text-center"><button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="buildArmy({{$planet['id']}})">招募陆军</button></p>
            </div>
        </li>
        @endforeach
    </ul>

    {{$planets->links("pagination::bootstrap-5")}}
</div>

@include('components.footer')
{{--<div class="modal fade" id="planetModal" tabindex="-1" aria-labelledby="planetModalLabel" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-lg">--}}
{{--        <div class="modal-content">--}}
{{--            <div class="modal-header">--}}
{{--                <h5 class="modal-title" id="planetName"></h5>--}}
{{--                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>--}}
{{--            </div>--}}
{{--            <div class="modal-body">--}}
{{--                <div class="container my-4 py-4 rounded shadow-lg">--}}
{{--                    <ul class="list-group list-group-flush" id="districtsList"></ul>--}}
{{--                </div>--}}
{{--                <div class="container my-4 py-4 rounded shadow-lg">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col my-4 py-4">--}}
{{--                            <div class="container my-4">--}}
{{--                                <h5 class="text-center">星球市场结余</h5>--}}
{{--                            </div>--}}
{{--                            <div class="container my-4" id="marketProduct"></div>--}}
{{--                        </div>--}}
{{--                        <div class="col my-4 py-4">--}}
{{--                            <div class="container my-4">--}}
{{--                                <h5 class="text-center">星球国库结余</h5>--}}
{{--                            </div>--}}
{{--                            <div class="container my-4" id="countryProduct"></div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="container my-4 py-4 rounded shadow-lg">--}}
{{--                    <div class="row">--}}
{{--                        <div class="col my-4 py-4">--}}
{{--                            <h6 class="text-center">人口</h6>--}}
{{--                            <div class="container my-4">--}}
{{--                                <div class="row" id="pops"></div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="modal-footer">--}}
{{--                <div id="adminButton"></div>--}}
{{--                @if($privilege != 3)--}}
{{--                    <button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="buildArmy()">招募陆军</button>--}}
{{--                    <button type="button" class="btn btn-primary" data-bs-target="#newDistrictModal" data-bs-toggle="modal" data-bs-dismiss="modal">新建区划</button>--}}
{{--                @endif--}}
{{--                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--@if($privilege != 3)--}}
{{--    <div class="modal fade" id="newDistrictModal" tabindex="-1" aria-labelledby="newDistrictModalModalLabel" aria-hidden="true">--}}
{{--        <div class="modal-dialog modal-sm">--}}
{{--            <div class="modal-content">--}}
{{--                <div class="modal-header">--}}
{{--                    <h5 class="modal-title">新建区划</h5>--}}
{{--                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>--}}
{{--                </div>--}}
{{--                <div class="modal-body">--}}
{{--                    @foreach($districts as $district)--}}
{{--                        <button type="button" class="btn btn-light" onclick="buildDistrict('{{$district['name']}}')">{{$district['name']}}</button>--}}
{{--                    @endforeach--}}
{{--                </div>--}}
{{--                <div class="modal-footer">--}}
{{--                    <div id="adminButton"></div>--}}
{{--                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" href="#planetModel">返回</button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--@endif--}}
{{--@if($privilege == 0 || $privilege == 1)--}}
{{--    <div class="modal fade" id="newPopModal" tabindex="-1" aria-labelledby="newPopModalLabel" aria-hidden="true">--}}
{{--        <div class="modal-dialog modal-sm">--}}
{{--            <div class="modal-content">--}}
{{--                <div class="modal-header">--}}
{{--                    <h5 class="modal-title">新建人口</h5>--}}
{{--                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>--}}
{{--                </div>--}}
{{--                <div class="modal-body">--}}
{{--                    @foreach($species as $specie)--}}
{{--                        <button type="button" class="btn btn-light" onclick="adminNewPop('{{$specie['name']}}')">{{$specie['name']}}</button>--}}
{{--                    @endforeach--}}
{{--                </div>--}}
{{--                <div class="modal-footer">--}}
{{--                    <div id="adminButton"></div>--}}
{{--                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" href="#planetModel">返回</button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <div class="modal fade" id="newMarketDistrictModal" tabindex="-1" aria-labelledby="newMarketDistrictModalModalLabel" aria-hidden="true">--}}
{{--        <div class="modal-dialog modal-sm">--}}
{{--            <div class="modal-content">--}}
{{--                <div class="modal-header">--}}
{{--                    <h5 class="modal-title">新建市场区划</h5>--}}
{{--                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>--}}
{{--                </div>--}}
{{--                <div class="modal-body">--}}
{{--                    @foreach($districts as $district)--}}
{{--                        <button type="button" class="btn btn-light" onclick="buildMarketDistrict('{{$district['name']}}')">{{$district['name']}}</button>--}}
{{--                    @endforeach--}}
{{--                </div>--}}
{{--                <div class="modal-footer">--}}
{{--                    <div id="adminButton"></div>--}}
{{--                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" href="#planetModel">返回</button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--@endif--}}
</body>
</html>
