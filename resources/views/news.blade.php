<!DOCTYPE html>
<html lang="zh">
<head>
	@include('components.header')
	<meta charset="UTF-8">
	<title>舆情 - Madoka</title>
    <style>
        .linkb{
            transition: all 0.2s ease-in-out;
        }
        .linkb:hover{
            box-shadow: none!important;
        }
    </style>
</head>
<body>
@include('components.nav')
<div class="container my-4">
	<h1 class="text-center">舆情</h1>
</div>
<div class="container mt-4 mb-5">
	<div class="row justify-content-start">
		<a class="btn btn-lg col-12 btn-primary my-3" href="/News/New">新建舆情</a>
		@foreach($news as $new)
			@if($privilege<=1 || $new["status"]==1 || ($new["media"]==$user["media"]&&$new["media"]!=0) || $new["editor"]==$user["uid"])
				<div class="col-12 my-3">
					<div class="card shadow-lg @if($new["type"]!=1) linkb @endif" >
						<div class="card-body" @if($new["type"]!=1) onclick="window.open('/News/{{$new["id"]}}','_blank')" @endif>
							<h5 class="card-title my-3">
								@if($new["status"]==0)
									<span class="badge bg-warning">待审</span>
								@endif
								<span class="badge @if($new["type"]==0) bg-primary @elseif($new["type"]==1) bg-success @elseif($new["type"]==2) bg-danger @endif" >@if($new["type"]==0)报道 @elseif($new["type"]==1)简讯 @elseif($new["type"]==2)声明 @endif</span>
								{{$new["title"]}}
							</h5>
							<p class="text-secondary d-inline">{{$medias[$new["media"]]["name"]}}</p>
							<p class="text-secondary float-end">创建时间：{{$new["created_at"]}}</p>
						</div>
						@if($new["status"]==0)
							<div class="card-footer" id="newsFooter-{{$new["id"]}}">
								@if($privilege<=1)<a class="btn btn-success mx-2" href="/Action/PassNews/{{$new["id"]}}">过审</a>@endif
                                @if($new["editor"]==$user["uid"]||$privilege<=1)<a class="btn btn-primary mx-2" href="/News/Edit/{{$new["id"]}}">编辑</a> @endif
							</div>
						@endif
					</div>
				</div>
			@endif
		@endforeach
	</div>
	<div class="mt-4 mb-5">
		{{$news->links("pagination::bootstrap-5")}}
	</div>

</div>
@include('components.footer')
</body>
</html>
