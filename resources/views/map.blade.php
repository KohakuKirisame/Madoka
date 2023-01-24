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
            color: #000000;
        }
    </style>
    <script type="application/javascript" src="{{asset('js/map.js')}}">

    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        @if ($country == '')
            @php
                $countryColor = '#ffffff';
            @endphp
        @endif
        @if ($privilege == 0 || $privilege == 1)
            <button type='button' class='btn btn-default dropdown-toggle'
                    style='position: absolute;
                            top: {{$y-16}}px; left: {{$x+13.75}}px; width: 20px;height: 20px;
                            border-radius: 100%;
                            background-color:hsla(0,0%,0%,0.00);
                            border:none ;
                            padding:0px 0px'
                    id='PlanetMenuLink-{{$stars[$i]['id']}}' data-bs-toggle='dropdown' aria-expanded='false'
                    data-bs-target='#star-Planet-{{$stars[$i]['id']}}'>
                @if($stars[$i]['havePlanet'] == 1)
                    @foreach($planets as $planet)
                        @php
                        if ($stars[$i]['id'] == $planet['position']) {
                            $img = $planet['type'];
                        }
                        @endphp
                    @endforeach
                    <img src='storage/img/planets/{{$img}}.png' width='20px' />
                @endif
            </button>
            <ul class='dropdown-menu' aria-labelledby='PlanerMenuLink-{{$stars[$i]['id']}}' id='star-Planet-{{$stars[$i]['id']}}'>
                <li><a class='dropdown-item' onclick='newPlanet({{$stars[$i]['id']}},"")'>无</a></li>
                @for ($j=0; $j < count($planetTypes); $j++)
                    <li><a class='dropdown-item'
                           onclick='newPlanet({{$stars[$i]['id']}},"{{$planetTypes[$j]['name']}}")'>{{$planetTypes[$j]['localization']}}</a>
                    </li>
                @endfor
            </ul>
            <button type='button' class='btn btn-default dropdown-toggle'
                    style='position: absolute;
                            top: {{$y-13.75}}px; left: {{$x-13.75}}px; width: 27.5px;height: 27.5px;
                            border-radius: 100%;
                            background-color:{{$countryColor}};
                            border:none ;
                            padding:0px 0px'
                    id='MenuLink-{{$stars[$i]['id']}}' data-bs-toggle='dropdown' aria-expanded='false'
                    data-bs-target='#star-{{$stars[$i]['id']}}'>
                @if($type == 'sc_black_hole' || $type == 'sc_pulsar' || $type == 'sc_neutron_star')
                    <img src='{{asset("storage/img/".$type.".png")}}' width='27.5px' />
                @endif
            </button>
            <ul class='dropdown-menu' aria-labelledby='MenuLink-{{$stars[$i]['id']}}' id='star-{{$stars[$i]['id']}}'>
                <li><a class='dropdown-item' onclick='changeOwner({{$stars[$i]['id']}},"")'>无</a></li>
                @for ($j=0; $j < count($countrys); $j++)
                    <li><a class='dropdown-item'
                           onclick='changeOwner({{$stars[$i]['id']}},"{{$countrys[$j]['tag']}}")'>{{$countrys[$j]['name']}}</a>
                    </li>
                @endfor
            </ul>
            @else
            <button type='button' class='btn btn-default'
                    style='position: absolute;
                        top: {{$y-16}}px; left: {{$x+13.75}}px; width: 20px;height: 20px;
                        border-radius: 100%;
                        background-color:hsla(0,0%,0%,0.00);
                        border:none ;
                        padding:0px 0px'
                    id='Planet-{{$stars[$i]['id']}}' aria-expanded='false'
                    data-bs-target='#star-Planet-{{$stars[$i]['id']}}'>
                @if($stars[$i]['havePlanet'] == 1)
                    @foreach($planets as $planet)
                        @php
                            if ($stars[$i]['id'] == $planet['position']) {
                                $img = $planet['type'];
                            }
                        @endphp
                    @endforeach
                    <img src='storage/img/planets/{{$img}}.png' width='20px'>
                @endif
            </button>
            <button type='button' class='btn btn-default'
                              style='position: absolute;
                    top: {{$y-13.75}}px; left: {{$x-13.75}}px; width: 27.5px;height: 27.5px;
                    border-radius: 100%;
                    background-color:{{$countryColor}};
                    border:none ;
                    padding:0px 0px'
                              data-bs-toggle='popover'
                              data-bs-trigger='hover'
                              data-bs-placement='top'
                              data-bs-container ='body'
                              title={{$stars[$i]['name']}}
                              data-bs-html='true'
                              data-bs-content='当前受控于{{$countryName}}'>
                @if($type == 'sc_black_hole' || $type == 'sc_pulsar' || $type == 'sc_neutron_star')
                    <img src='{{asset("storage/img/".$type.".png")}}' width='27.5px' />
                @endif
            </button>
        @endif
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
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    })
</script>
<footer class="fixed-bottom">
    <div class="container-fluid">

    </div>
</footer>
</body>
</html>
