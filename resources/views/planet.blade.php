<!DOCTYPE html>
<html>
<head>
    @include('components.header')
    <title>星球 - Madoka</title>
</head>
<body>
@include('components.nav')
<div class="container my-4">
    <h1 class="text-center">星球</h1>
</div>
<div class="container my-4 py-4 rounded shadow-lg" style="background: #FFFFFF">
    <ul class="list-group list-group-flush">
        <li class="list-group-item">
            <div class="row">
                <h5 class="col-2 text-center">星球</h5>
                <h5 class="col-2 text-center">位置</h5>
                <h5 class="col-2 text-center">类型</h5>
                <h5 class="col-2 text-center">大小</h5>
                <h5 class="col-2 text-center">人口</h5>
                <h5 class="col-2 text-center">详情</h5>
            </div>
        </li>
        @foreach($planets as $planet)
        <li class="list-group-item">
            <div class="row">
                <div class="col-2">
                    <input type="text" class="form-control" value="{{$planet['name']}}" />
                </div>
                <p class="col-2 text-center">{{$planet['position']}}</p>
                <p class="col-2 text-center">{{$planet['type']}}</p>
                <p class="col-2 text-center">{{$planet['size']}}</p>
                <p class="col-2 text-center">{{count($planet['pops'])}}</p>
                <p class="col-2 text-center">
                    <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#planet-{{$planet['id']}}">详情</button>
                </p>
            </div>
        </li>
        @endforeach
    </ul>
    <div class="modal fade" id="planetModal" tabindex="-1" aria-labelledby="planetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="planetName"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container my-4 py-4 rounded shadow-lg">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <div class="row">
                                    <h5 class="col-2 text-center">星球</h5>
                                    <h5 class="col-2 text-center">位置</h5>
                                    <h5 class="col-2 text-center">类型</h5>
                                    <h5 class="col-2 text-center">大小</h5>
                                    <h5 class="col-2 text-center">人口</h5>
                                    <h5 class="col-2 text-center">详情</h5>
                                </div>
                            </li>
                            @foreach($planets as $planet)
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-2">
                                            <input type="text" class="form-control" value="{{$planet['name']}}" />
                                        </div>
                                        <p class="col-2 text-center">{{$planet['position']}}</p>
                                        <p class="col-2 text-center">{{$planet['type']}}</p>
                                        <p class="col-2 text-center">{{$planet['size']}}</p>
                                        <p class="col-2 text-center">{{count($planet['pops'])}}</p>
                                        <p class="col-2 text-center">
                                            <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#planet-{{$planet['id']}}">详情</button>
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    {{$planets->links("pagination::bootstrap-5")}}
</div>

@include('components.footer')
</body>
</html>
