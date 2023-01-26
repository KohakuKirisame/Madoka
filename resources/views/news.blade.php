<!DOCTYPE html>
<html lang="zh">
<head>
    @include('components.header')
    <meta charset="UTF-8">
    <title>舆情</title>
</head>
<body>
@include('components.nav')
<div class="container my-4">
    <h1 class="text-center">舆情</h1>
</div>
<div class="container my-4">
    <div class="row justify-content-start">
        @if($privilege==3||$privilege<=1)
        <a class="btn col-12 btn-primary my-3" href="/News/New">新建新闻</a>
        @endif
        @foreach($news as $new)
            @if($privilege<=1 || $new["status"]==1)
        <div class="col-12 my-3">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5 class="card-title my-3">
                        @if($new["status"]==0)
                        <span class="badge bg-warning">待审</span>
                        @endif
                        <span class="badge @if($new["type"]==0) bg-primary @elseif($new["type"]==1) bg-success @endif" >@if($new["type"]==0)报道 @elseif($new["type"]==1)简讯 @endif</span>
                        {{$new["title"]}}
                    </h5>
                    <p class="text-secondary d-inline">{{$medias[$new["media"]]["name"]}}</p>
                    <p class="text-secondary float-end">创建时间：{{$new["created_at"]}}</p>
                </div>
                @if($privilege<=1&&$new["status"]==0)
                <div class="card-footer" id="newsFooter-{{$new["id"]}}">
                   <a class="btn btn-success">过审</a>
                </div>
                @endif
            </div>
        </div>
                @endif
        @endforeach
    </div>
    <div class="my-4">
        {{$news->links("pagination::bootstrap-5")}}
    </div>

</div>
@include('components.footer')
</body>
</html>
