<!DOCTYPE html>
<html lang="zh">
<head>
    @include('components.header')
    <title>星图 - Madoka</title>
    <style type="text/css">
        html, body {
            margin: 0px;
        }

        canvas {
            background-image: url({{asset('storage/img/map.png')}});
            color: black;
        }
    </style>
</head>
<body>
<div class="container">

    @for($i=0; $i < count($stars); $i++)
        @php
            $x = $stars[$i]['x']*3+960;
            $y = $stars[$i]['y']*3+960;
			$country = $stars[$i]['owner'];
        @endphp

        @foreach($countrys as $key => $value)

            @if($value['tag'] == $country)
                @php

                    $countryColor = $value['color'];
                @endphp
            @endif
        @endforeach
        @if (!isset($countryColor))
            @php
                $countryColor = '#ffffff';
            @endphp
        @endif
    <div>
        <button type='button' class='btn btn-default dropdown-toggle'
                style='position: absolute;
                        top: {{$y-13.75}}px; left: {{$x-13.75}}px; width: 27.5px;height: 27.5px;
                        border-radius: 100%;
                        background-color:{{$countryColor}};
                        border:none ;
                        padding:0px 0px'
                id='MenuLink-{{$stars[$i]['id']}}' data-bs-toggle='dropdown' aria-expanded='false'
                data-bs-target='#star-{{$stars[$i]['id']}}'>
        </button>
        <ul class='dropdown-menu' aria-labelledby='MenuLink-{{$stars[$i]['id']}}' id='star-{{$stars[$i]['id']}}'>
            <li><a class='dropdown-item' onclick='changeOwner({{$stars[$i]['id']}},"")'>无</a></li>
            @for ($j=0; $j < count($countrys); $j++)
                <li><a class='dropdown-item'
                       onclick='changeOwner({{$stars[$i]['id']}},{{$countrys[$j]['id']}})'>{{$countrys[$j]['name']}}</a>
                </li>
            @endfor
        </ul>
    </div>
    @endfor
</div>
    <canvas id="canvas_1">
        <h1>您的浏览器不支持canvas, 请升级后重新访问</h1>
    </canvas>
<script type="text/javascript">
    var canvas_1 = document.getElementById("canvas_1");
    var ctx = canvas_1.getContext("2d");
    canvas_1.width = "1920";
    canvas_1.height = "1920";
    ctx.font = "10px sans-serif";
    var imgBH = new Image();
    imgBH.src = 'img/black_hole.png';
    var imgN = new Image();
    imgN.src = 'img/neutron.png';
    var imgP = new Image();
    imgP.src = 'img/pulsar.png';
    ctx.strokeStyle = 'black';
    @for ($i=0; $i < count($stars); $i++)
        @php
            $x = $stars[$i]['x']*3+960;
            $y = $stars[$i]['y']*3+960;
            $stars[$i]['hyperlane'] = json_decode($stars[$i]['hyperlane'],true);
        @endphp
        ctx.lineWidth = 3;
        ctx.strokeStyle = '#66d1ff';
        @foreach ($stars[$i]['hyperlane'] as $key => $value)
            ctx.beginPath();
            ctx.moveTo({{$x}}, {{$y}});
            ctx.lineTo({{$stars[$value["to"]]['x'] * 3 + 960}}, {{$stars[$value["to"]]['y'] * 3 + 960}});
            ctx.closePath();
            ctx.stroke();
        @endforeach
    @endfor
    @for ($i=0; $i < count($stars); $i++)
        @php
            $x = $stars[$i]['x']*3+960;
            $y = $stars[$i]['y']*3+960;
        @endphp
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.arc({{$x}}, {{$y}}, 13.75, 0, 20 * Math.PI);
        ctx.fillStyle = 'white';
        ctx.fill();
        ctx.stroke();
        ctx.fillStyle = 'white';
        ctx.fillText('{{$stars[$i]['name']}}', {{$x -22.5}}, {{$y +25}});
        @if ($stars[$i]['type'] == 'black_hole')
        ctx.drawImage(imgBH, {{$x -15}}, {{$y -15}}, 24, 24);

        @elseif ($stars[$i]['type'] == 'neutron')
        ctx.drawImage(imgN, {{$x -10}}, {{$y -10}}, 20, 20);

        @elseif ($stars[$i]['type'] == 'pulsar')
        ctx.drawImage(imgP, {{$x-15}}, {{$y-15}}, 24, 24);
        @endif
    @endfor

</script>
@include('components.footer')
</body>
</html>