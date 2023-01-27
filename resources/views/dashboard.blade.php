<!DOCTYPE html>
<html lang="zh">
<head>
	@include('components.header')
	<title>主面板 - Madoka</title>
    <script type="application/javascript" src="{{asset('js/dashboard.js')}}"></script>
</head>
<body>
	@include('components.nav')
	<div class="container">
		<div class="row">
			<div class="col-md-3">
				<p>123</p>
			</div>
			<div class="col-md-9">
                <p>我草！你是：@if($privilege==0)超级管理员@elseif($privilege==1)管理员@elseif($privilege==2)代表@endif</p>
			</div>
            @if($privilege == 0)
                <button type="button" class="btn btn-danger" data-bs-dismiss="model" onclick="mainFunction()">Game Start</button>
            @endif
		</div>
	</div>
	@include('components.footer')
</body>
</html>
