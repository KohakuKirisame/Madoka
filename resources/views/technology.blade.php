<!DOCTYPE html>
<html lang="zh">
<head>
    @include('components.header')
    <title>科研 - Madoka</title>
    <script type="application/javascript" src="{{asset('js/technology.js')}}"></script>
</head>
<body>
@include('components.nav')
<div class="container my-4">
    <h2 class="text-center">科研面板</h2>
</div>
<div class="row">
    <div class="container col-3 my-4 py-4 rounded shadow-lg" style="background:#FFF">
        <h4 class="text-center">已完成科技研究</h4>
        @for($i = 0; $i < 2; $i++)
            <div class="card mb-4">
                <div class="card-body">
                    {{$country['techs'][$i]}}
                </div>
            </div>
        @endfor
        <button class="btn btn-primary" type="button" data-bs-toggle="modal" href="#techsModal")>完整列表</button>
    </div>
    <div class="container col-6 my-4 py-4 rounded shadow-lg" style="background:lightblue">
        <h4 class="text-center">科研槽位</h4>
        @for($i = 0; $i < intval($slots); $i++)
            <div class="card mb-4">
                <div class="card-body">
                    @if($i>=count($country['techList'])||!key_exists($i,$country['techList']))
                        <button class="btn btn-light" type="button" data-bs-toggle="modal" href="#newTechModal")>科研方向选择</button>
                    @else
                        {{$country['techList'][$i]['area']}}<br>
                        正在研究{{$country['techList'][$i]['tech']}}<br>
                        正在补贴{{$country['techList'][$i]['allowance']}}<br>
                        {{$country['techList'][$i]['process']}}/{{$country['techList'][$i]['cost']}}<input type="range" class="form-range" id="disabledRange" min="0" max="{{$country['techList'][$i]['cost']}}" value="{{$country['techList'][$i]['process']}}" disabled>
                        <button class="btn btn-danger" style="float:right" type="button" onclick="deleteTech('{{$country['tag']}}','{{$country['techList'][$i]['tech']}}')")>取消研究</button>
                        <button class="btn btn-success" style="float:right" type="button" data-bs-toggle="modal" href="#allowanceModal" onclick="readAllowance('{{$country['techList'][$i]['allowance']}}','{{$country['techList'][$i]['tech']}}')")>设置补贴</button>
                    @endif
                </div>
            </div>
        @endfor
    </div>
</div>
@include('components.footer')
<div class="modal fade" id="techsModal" tabindex="-1" aria-labelledby="techsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">已完成科研</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    @foreach($country['techs'] as $tech)
                        <div class="col-3">
                            <div class="card">
                                <div class="card-body">
                                    {{$tech}}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                @if($privilege <= 1)
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" href="#allTechModal")>增加科技</button>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-dismiss="modal">返回</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="allowanceModal" tabindex="-1" aria-labelledby="allowanceModal" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><div id="nowTech"></div>补贴设置</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control" id="allowance" value=""/>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" type="button" data-bs-toggle="modal" onclick="changeAllowance('{{$country['tag']}}')">确认</button>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-dismiss="modal">返回</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="newTechModal" tabindex="-1" aria-labelledby="newTechModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">科研方向选择</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    @foreach($techArea as $area)
                        @if($area['subject'] == '物理学')
                            <div class="card p-2" style="background:lightblue">
                                <div class="card-body">
                                    {{$area['area']}}<button class="btn btn-primary" style="float:right" type="button" data-bs-dismiss="modal" onclick="chooseTech('{{$country['tag']}}','{{$area['area']}}')")>选择此项</button>
                                </div>
                            </div>
                            @elseif($area['subject'] == '社会学')
                            <div class="card p-2" style="background:lightgreen">
                                <div class="card-body p-4">
                                    {{$area['area']}}<button class="btn btn-primary" style="float:right" type="button" data-bs-dismiss="modal" onclick="chooseTech('{{$country['tag']}}','{{$area['area']}}')")>选择此项</button>
                                </div>
                            </div>
                            @else
                            <div class="card p-2" style="background:lightyellow">
                                <div class="card-body p-4">
                                    {{$area['area']}}<button class="btn btn-primary" style="float:right" type="button" data-bs-dismiss="modal" onclick="chooseTech('{{$country['tag']}}','{{$area['area']}}')")>选择此项</button>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <div id="adminButton"></div>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" href="#planetModel">返回</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="allTechModal" tabindex="-1" aria-labelledby="allTechModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新增科技</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    @foreach($techs as $tech)
                        @if($tech['subject'] == '物理学')
                            <div class="col-3">
                                <div class="card" style="background:lightblue">
                                    <div class="card-body">
                                        {{$tech['name']}}<button class="btn btn-primary" style="float:right" type="button" data-bs-dismiss="modal" onclick="adminAddTech('{{$country['tag']}}','{{$tech['name']}}')")>选择</button>
                                    </div>
                                </div>
                            </div>
                        @elseif($tech['subject'] == '社会学')
                            <div class="col-3">
                                <div class="card" style="background:lightgreen">
                                    <div class="card-body">
                                        {{$tech['name']}}<button class="btn btn-primary" style="float:right" type="button" data-bs-dismiss="modal" onclick="adminAddTech('{{$country['tag']}}','{{$tech['name']}}')")>选择</button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="col-3">
                                <div class="card" style="background:lightyellow">
                                    <div class="card-body">
                                        {{$tech['name']}}<button class="btn btn-primary" style="float:right" type="button" data-bs-dismiss="modal" onclick="adminAddTech('{{$country['tag']}}','{{$tech['name']}}')")>选择</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <div id="adminButton"></div>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" href="#planetModel">返回</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
