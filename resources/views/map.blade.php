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
			$type = $stars[$i]['type'];
        @endphp
        @foreach($countrys as $key => $value)
            @if($value['tag'] == $country)
                @php
                    $countryColor = $value['color'];
					$countryName = $value['name'];
                @endphp
            @endif
        @endforeach
        @if ($country == '')
            @php
                $countryColor = '#ffffff';
				$countryName = '';
            @endphp
        @endif
        @if ($privilege == 0 || $privilege == 1)
            @if($stars[$i]['havePlanet'] == 1)
                <button type='button' class='btn btn-default dropdown-toggle'
                        style='position: absolute;
                                top: {{$y-20}}px; left: {{$x+13.75}}px; width: 20px;height: 20px;
                                border-radius: 100%;
                                background-color:hsla(0,0%,0%,0.00);
                                border:none ;
                                padding:0px 0px'
                        id='PlanetMenuLink-{{$stars[$i]['id']}}' data-bs-toggle='dropdown' aria-expanded='false'
                        data-bs-target='#star-Planet-{{$stars[$i]['id']}}'>
                        @foreach($planets as $planet)
                            @php
                                if ($stars[$i]['id'] == $planet['position']) {
                                    $img = 'wet';
                                }
                            @endphp
                        @endforeach
                        <img src='storage/img/planets/{{$img}}.png' width='20px'>
                </button>
                <ul class='dropdown-menu' aria-labelledby='PlanerMenuLink-{{$stars[$i]['id']}}' id='star-Planet-{{$stars[$i]['id']}}'>
                    <li><a class='dropdown-item' onclick='newPlanet({{$stars[$i]['id']}},"")'>无</a></li>
                    @for ($j=0; $j < count($planetTypes); $j++)
                        <li><a class='dropdown-item'
                               onclick='newPlanet({{$stars[$i]['id']}},"{{$planetTypes[$j]['name']}}")'>{{$planetTypes[$j]['localization']}}</a>
                        </li>
                    @endfor
                </ul>
            @endif
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
                @if($stars[$i]['havePlanet'] == 1)
                    @php
                        $ownered = False;
                        foreach ($planets as $planet) {
							if ($stars[$i]['id'] == $planet['position'] && $planet['controller'] != '') {
                                $ownered = true;
                                $countryImg = $planet['controller'];
                                break;
							}
                        }
                    @endphp
                    @if ($ownered)
                        <img src='storage/img/countries/{{$countryImg}}.png' width='27.5px' />
                    @endif
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
            @if($stars[$i]['havePlanet'] == 1)
                @php
                    $ownered = False;
                    foreach($planets as $planet) {
                        if ($stars[$i]['id'] == $planet['position']) {
                            $img = 'wet';
                            if ($planet['controller'] != '') {
                                $ownered = true;
                            }
                            break;
                        }
                    }
                @endphp
                @if (!$ownered)
                <button type='button' class='btn btn-default'
                        style='position: absolute;
                            top: {{$y-20}}px; left: {{$x+13.75}}px; width: 20px;height: 20px;
                            border-radius: 100%;
                            background-color:hsla(0,0%,0%,0.00);
                            border:none ;
                            padding:0px 0px'
                        id='Planet-{{$stars[$i]['id']}}' aria-expanded='false'
                        data-bs-target='#star-Planet-{{$stars[$i]['id']}}'>
                    <img src='storage/img/planets/{{$img}}.png' width='20px' onclick="colonize({{$planet['id']}})"/>
                </button>
                @endif
            @endif
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
                              data-bs-content='当前受控于{{$countryName}}
                              <p>本星系包含
                                @foreach($stars[$i]['resource'] as $res=>$value)
                                    @if($value == 0)
                                        @php continue;@endphp
                                    @endif
                                    <span class="badge bg-light text-dark"><img src="storage/img/resource/{{$res}}.png"/ width="20px">{{$value}}</span>
                                @endforeach
                              </p>'>
                @if($type == 'sc_black_hole' || $type == 'sc_pulsar' || $type == 'sc_neutron_star')
                    <img src='{{asset("storage/img/".$type.".png")}}' width='27.5px' />
                @endif
                @if($stars[$i]['havePlanet'] == 1)
                    @php
                        $ownered = False;
                        foreach ($planets as $planet) {
                            if ($stars[$i]['id'] == $planet['position'] && $planet['controller'] != '') {
                                $ownered = true;
                                $countryImg = $planet['controller'];
                                break;
                            }
                        }
                    @endphp
                    @if ($ownered)
                        <img src='storage/img/countries/{{$countryImg}}.png' width='27.5px' />
                    @endif
                @endif
            </button>
        @endif
        <button type='button' class='btn btn-default'
                style='position: absolute;
                top: {{$y-25}}px; left: {{$x+10}}px; width: 20px;height: 20px;
                border-radius: 100%;
                background-color:hsla(0,0%,0%,0.00);
                border:none ;
                padding:0px 0px' onclick="setTradeHub({{$stars[$i]['id']}})">
            @if($stars[$i]['isTradeHub'] == 1)
                <img src='storage/img/trade.png' width='17.5px' />
            @endif
        </button>
        <button type='button' class='btn btn-default'
                style='position: absolute;
            top: {{$y}}px; left: {{$x+10}}px; width: 20px;height: 20px;
            border-radius: 100%;
            background-color:hsla(0,0%,0%,0.00);
            border:none ;
            padding:0px 0px'>
            @if($stars[$i]['isCapital'] == 1)
                <img src='storage/img/capital.png' width='17.5px' />
            @endif
        </button>
        @foreach($fleets as $key=>$value)
            @if($value['position'] == $stars[$i]['id'])
                <button class='btn btn-default'
                    style='position: absolute;
                        top: {{$y-25}}px; left: {{$x-30}}px; width: 20px;height: 20px;
                        border-radius: 100%;
                        background-color:hsla(0,0%,0%,0);
                        border:none ;
                        padding:0px 0px'
                    data-bs-toggle='popover'
                    data-bs-trigger='hover'
                    data-bs-placement='top'
                    data-bs-container ='body'
                    data-bs-html='true'
                    title={{$value['name']}}
                    data-bs-content='隶属于{{$value['owner']}}<br>舰队实力{{$value['power']}}'>
                    @if($value['owner'] == $selfCountry)
                        <img src='storage/img/military/fleet_green.png' width='20px' />
                    @elseif(in_array($value['owner'],$allied))
                        <img src='storage/img/military/fleet_blue.png' width='20px' />
                    @elseif(in_array($value['owner'],$war))
                        <img src='storage/img/military/fleet_red.png' width='20px' />
                    @else
                        <img src='storage/img/military/fleet_yellow.png' width='20px' />
                    @endif
                </button>
            @endif
        @endforeach
        @foreach($armys as $key=>$value)
            @if($value['position'] == $stars[$i]['id'])
                <button class='btn btn-default'
                        style='position: absolute;
                    top: {{$y-25}}px; left: {{$x-30}}px; width: 20px;height: 20px;
                    border-radius: 100%;
                    background-color:hsla(0,0%,0%,0.00);
                    border:none ;
                    padding:0px 0px'
                        data-bs-toggle='popover'
                        data-bs-trigger='hover'
                        data-bs-placement='top'
                        data-bs-container ='body'
                        data-bs-html='true'
                        title={{$value['name']}}
                data-bs-content='隶属于{{$value['owner']}}'>
                    @if($value['owner'] == $selfCountry)
                        <img src='storage/img/military/army_green.png' width='20px' />
                    @elseif(in_array($value['owner'],$allied))
                        <img src='storage/img/military/army_blue.png' width='20px' />
                    @elseif(in_array($value['owner'],$war))
                        <img src='storage/img/military/army_red.png' width='20px' />
                    @else
                        <img src='storage/img/military/army_yellow.png' width='20px' />
                    @endif
                </button>
            @endif
        @endforeach
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
