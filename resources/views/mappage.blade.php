<!DOCTYPE html>
<html lang="zh">
<head>
    @include('components.header')
    <title>星图 - Madoka</title>
    <style type="text/css">
        html, body {
            margin: 0px;
            height: 100%;
        }
    </style>
</head>
<body>
@include('components.nav')
<div class="container-fluid" style="height: 85%">
    <iframe src="/MapContent" class="w-100 h-100">

    </iframe>
</div>
@include('components.footer')
</body>
</html>
