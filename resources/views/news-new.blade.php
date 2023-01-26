<!DOCTYPE html>
<html lang="zh-CN">
<head>
    @include('components.header')
    <title>新建新闻</title>
    {!! editor_css() !!}
</head>
<body>
@include("components.nav")
<div class="container my-4">
    <h1 class="text-center">新建新闻</h1>
</div>
<div class="container my-4>">
     <div class="row">
         <form id="answerForm" method="post" action="/Action/Newnews">
             @csrf
                <div class="col-12">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="title" name="title" placeholder="标题" required>
                        <label for="title">标题</label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-floating mb-3">
                        <select class="form-select" id="type" name="type" aria-label="Floating label select example" required onchange="editorShow()">
                            <option value="0">报道</option>
                            <option value="1">简讯</option>
                        </select>
                        <label for="type">类型</label>
                    </div>
         </form>
     </div>
    <div id="editormd_id">
        <textarea class="d-none" name="answer" form="answerForm"></textarea>
    </div>
    <div class="my-4 row justify-content-between">
        <a class="btn btn-outline-primary col-3" href="/News">返回</a>
        <button class="btn btn-success col-3" onclick="$('#answerForm').submit();" >保存</button>
    </div>
</div>
@include("components.footer")
{!! editor_js() !!}
</body>
</html>
