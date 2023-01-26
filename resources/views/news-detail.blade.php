<!DOCTYPE html>
<html lang="zh">
<head>
    @include('components.header')
    <meta charset="UTF-8">
    <title>{{$news["title"]}}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css" integrity="sha384-Xi8rHCmBmhbuyyhbI88391ZKP2dmfnOl4rT9ZfRI7mLTdk1wblIUnrIq35nqwEvC" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js" integrity="sha384-X/XCfMm41VSsqRNQgDerQczD69XqmjOOOwYQvr/uuC+j4OPoNhVgjdGFwhvN02Ja" crossorigin="anonymous"></script>
</head>
<body>
@include('components.nav')
<div class="container my-4">
    <h1 class="text-center">{{$news["title"]}}</h1>
    <h5 class="text-center text-secondary">{{$medias[$news["media"]]["name"]}}</h5>
</div>
<div class="container my-4">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title my-3">
                <span class="badge @if($news["type"]==0) bg-primary @elseif($news["type"]==1) bg-success @endif" >@if($news["type"]==0)报道 @elseif($news["type"]==1)简讯 @endif</span>
            </h5>
            <div class="mx-4">@parsedown($news["content"])</div>
            <p class="text-secondary d-inline">{{$medias[$news["media"]]["name"]}}</p>
            <p class="text-secondary float-end">发表时间：{{$news["created_at"]}}</p>
        </div>
    </div>
</div>
@include('components.footer')
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/contrib/auto-render.min.js" integrity="sha384-+XBljXPPiv+OzfbB3cVmLHf4hdUFHlWNZN5spNQ7rmHTXpd7WvJum6fIACpNNfIR" crossorigin="anonymous"></script>
<script type="application/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        renderMathInElement(document.body, {
            delimiters: [
                {left: "$$$", right: "$$$", display: true},
                {left: "$$", right: "$$", display: false}
            ],
        });
    });
</script>
</body>
</html>
