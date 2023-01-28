<!DOCTYPE html>
<html lang="zh-CN">
<head>
    @include('components.header')
    <title>@if(isset($news))修改@else新建@endif舆情 - Madoka</title>
    {!! editor_css() !!}

</head>
<body>
@include("components.nav")
<div class="container my-4">
    <h1 class="text-center">@if(isset($news))修改@else新建@endif舆情</h1>
</div>
<div class="container my-4>">
     <div class="row">
         <form id="newsForm" method="post" action="/Action/SaveNews">
             @csrf
             @if(isset($news))
             <div class="col-12" style="display: none">
                 <div class="form-floating mb-3">
                     <input type="text" class="form-control" id="newsid" name="newsid" placeholder="标题" required value="{{$news["id"]}}" />
                 </div>
             </div>
             @endif
                <div class="col-12">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="title" name="title" placeholder="标题" required @if(isset($news))value="{{$news["title"]}}" @endif />
                        <label for="title">标题</label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-floating mb-3">
                        <select class="form-select" id="type" name="type" aria-label="Floating label" required onchange="editorShow()">
                            @if($privilege!=2)
                            <option value="0" @if(isset($news)) @if($news["type"]==0) selected @endif @endif>报道</option>
                            <option value="1" @if(isset($news)) @if($news["type"]==1) selected @endif @endif>简讯</option>
                            @endif
                            @if($privilege<=2)
                                <option value="2" @if(isset($news)) @if($news["type"]==2) selected @endif @endif>声明</option>
                            @endif
                        </select>
                        <label for="type">类型</label>
                    </div>
                </div>
             <div class="col-12" id="media_select">
                 <div class="form-floating mb-3">
                     <select class="form-select" id="media" name="media" aria-label="Floating label" @if($privilege>1) readonly="readonly" @endif>
                         @foreach($medias as $media)
                             @if($privilege<=1||($privilege==3 && $media["id"]==$user["media"]))
                             <option value="{{$media["id"]}}" @if($user["media"]==$media["id"]) selected @endif>{{$media["name"]}}</option>
                             @endif
                         @endforeach
                     </select>
                     <label for="type">媒体</label>
                 </div>
             </div>
         </form>
    <div id="editormd_id" class="my-3">
        <textarea class="d-none" name="content" form="newsForm">@if(isset($news)){!! $news["content"] !!}@endif</textarea>
    </div>
    </div>
    <div class="my-4 row justify-content-between">
        <a class="btn btn-outline-primary col-3" href="/News">返回</a>
        <button class="btn btn-success col-3" onclick="$('#newsForm').submit();" >保存</button>
    </div>
</div>
@include("components.footer")
{!! editor_js() !!}
<script type="application/javascript" src="{{asset('js/news.js')}}"></script>
</body>
</html>
