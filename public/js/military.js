var nowControlling=0;
var type;
var moveID=0;
function changeFleetName(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    name=$("#fleetName-"+id).val();
    $.post('/Action/ChangeFleetName',{
        id : id,
        name : name,
    },function() {});
}
function changeShipName(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    name=$("#shipName-"+id).val();
    $.post('/Action/ChangeShipName',{
        id : id,
        name : name,
    },function() {});
}
function changeFleetComputer(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let computer = $("#fleetComputer-" + id).val();
    $.post('/Action/ChangeFleetComputer',{
        id : id,
        computer : computer,
    },function() {});
}
function changeFleetWeaponA(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let weaponA = $("#weaponA-" + id).val();
    $.post('/Action/ChangeFleetWeaponA',{
        id : id,
        weaponA:weaponA,
    },function() {});
}
function changeFleetWeaponB(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let weaponB = $("#weaponB-" + id).val();
    $.post('/Action/ChangeFleetWeaponB',{
        id : id,
        weaponB:weaponB,
    },function() {});
}
function changeFleetFTL(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let FTL = $("#fleetFTL-" + id).val();
    $.post('/Action/ChangeFleetFTL',{
        id : id,
        FTL : FTL,
    },function() {});
}
function newShip(type) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/NewShip',{
        id : nowControlling,
        type : type,
    },function() {
        // location.reload();
    });
}
function getFleets(type,id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/GetFleets',
    function(data) {
        data = JSON.parse(data);
        if (type == 'merge') {
            for (var key in data) {
                $('#fleets').append("<button type=\"button\" class=\"btn btn-light\" onclick=\"fleetMerge(" + nowControlling + "," + data[key]['id'] + ")\">" + data[key]['name'] + "</button>");
            }
        } else if (type == 'trans') {
            for (var key in data) {
                $('#fleets').append("<button type=\"button\" class=\"btn btn-light\" onclick=\"shipTrans("+nowControlling+","+id+","+data[key]['id']+")\">" + data[key]['name'] + "</button>");
            }
        }
    });
}
function fleetMerge(id1,id2) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/FleetMerge', {
        id1: id1,
        id2: id2,
    },function(data) {
        location.reload();
    });
}
function chooseShip(id) {
    $('#fleets').empty();
    getFleets('trans',id);
}
function shipTrans(f1,id,f2) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/ShipTrans', {
        f1: f1,
        id: id,
        f2: f2,
    },function(data) {
        location.reload();
    });
}
function changeArmyName(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    name=$("#armyName-"+id).val();
    $.post('/Action/ChangeArmyName',{
        id : id,
        name : name,
    },function () {});
}
function moveArmy(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $("#moveType").html('army');
    $("#moveID").html(id)
}
function moveFleet() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $("#moveType").html('fleet');
    $("#moveID").html(nowControlling);
}
function moveTarget() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    type = $("#moveType").text();
    id = $("#moveID").text();
    let target = $("#moveTarget").val();
    $.post('/Action/Move', {
        type: type,
        id: id,
        target : target,
    }, function (data) {
        location.reload();
    })
}
function fleetDelete() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/FleetDelete', {
        id: nowControlling,
    },function(data) {
        location.reload();
    });
}
function deleteArmy(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/ArmyDelete', {
        id: id,
    },function(data) {
        location.reload();
    });
}
function newFleet(owner) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    name = $('#newFleetName').val();
    let weaponA = $('#newFleetWeaponA').val();
    let weaponB = $('#newFleetWeaponB').val();
    $.post('/Action/NewFleet', {
        owner: owner,
        name: name,
        weaponA: weaponA,
        weaponB: weaponB,
    },function() {
        location.reload();
    });
}
function readFleet(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/ReadFleet',{
        id : id,
    },function(data) {
        nowControlling = id;
        $('#shipList').empty();
        $('#shipList2').empty();
        data = JSON.parse(data);
        $('#fleetName').html(data['name']);
        $('#hull').html(data['hull']);
        $('#EDamage').html(data['EDamage']);
        $('#PDamage').html(data['PDamage']);
        $('#armor').html(data['armor']);
        $('#shield').html(data['shield']);
        $('#evasion').html(Math.round(data['evasion']*100)/100);
        $('#speed').html(Math.round(data['speed']));
        $('#power').html(data['power']);
        $('#weaponA').empty();
        $('#weaponB').empty();
        if (data['weaponA'] == 1) {
            $('#weaponA').append("<select class=\"form-select\" aria-label=\"weaponASelect\" id=\"weaponA-"+id+"\" onChange=\"changeFleetWeaponA("+id+",'A')\"><option value=\"1\" selected>能量武器</option><option value=\"2\">动能武器</option></select>");
        } else {
            $('#weaponA').append("<select class=\"form-select\" aria-label=\"weaponASelect\" id=\"weaponA-"+id+"\" onChange=\"changeFleetWeaponA("+id+",'A')\"><option value=\"2\" selected>动能武器</option><option value=\"1\">能量武器</option></select>");
        }
        if (data['weaponB'] == 1) {
            $('#weaponB').append("<select class=\"form-select\" aria-label=\"weaponBSelect\" id=\"weaponB-"+id+"\" onChange=\"changeFleetWeaponB("+id+",'B')\"><option value=\"1\" selected>能量武器</option><option value=\"2\">动能武器</option></select>");
        } else {
            $('#weaponB').append("<select class=\"form-select\" aria-label=\"weaponBSelect\" id=\"weaponB-"+id+"\" onChange=\"changeFleetWeaponB("+id+",'B')\"><option value=\"2\" selected>动能武器</option><option value=\"1\">能量武器</option></select>");
        }
        for (var key in data['shipList']) {
            $('#shipList').append("<div class='row'>" +
                "                       <div class='col-8'>" +
                "                           <span class=\"badge bg-light text-dark\">" +
                "                               <input type=\"text\" class=\"form-control\" id=\"shipName-"+data['shipList'][key][0]+"\" value=\""+data['shipList'][key][1]+"\" onchange=\"changeShipName("+data['shipList'][key][0]+")\" style='display:inline'/>" +
                "                               "+data['shipList'][key][2]+"" +
                "                           </span>" +
                "                       </div>" +
                "                   </div>");
            $('#shipList2').append("<button type=\"button\" class=\"btn btn-light\"  data-bs-toggle=\"modal\" href=\"#fleetMergeModal\" onclick=\"chooseShip("+data['shipList'][key][0]+")\">" +data['shipList'][key][1]+data['shipList'][key][2]+ "</button>")
        }
        const fleetModal = new bootstrap.Modal("#fleetModal");
        fleetModal.show();
    });
}
function battle(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let fleets = $('#battle').val();
    $.post('/Action/Battle', {
        fleets : fleets,
    },function(){

    });
}
