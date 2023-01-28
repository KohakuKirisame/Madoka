var nowControlling=0;
function adminNewPop(species) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/AdminNewPop',{
        id : nowControlling,
        species : species,
    },function() {
        $('#planetPop-'+nowControlling).html(Number($('#planetPop-'+nowControlling).html())+1);
    })
}
function changePlanetName(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    name=$("#planetName-"+id).val();
    $.post('/Action/ChangePlanetName',{
        id : id,
        name : name,
    },function() {});
}
function changeSize(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    size=$("#planetSize-"+id).val();
    $.post('/Action/ChangeSize',{
        id : id,
        size : size,
    },function() {});
}
function buildDistrict(district) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/BuildDistrict',{
        id : nowControlling,
        district : district,
    },function() {});
}
function buildMarketDistrict(district) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/BuildMarketDistrict',{
        id : nowControlling,
        district : district,
    },function() {});
}
function buildArmy() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/BuildArmy',{
        id : nowControlling,
    },function(data) {
    });
}
function readPlanet(id,privilege){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/ReadPlanet',{
        id : id,
    },function(data) {
        nowControlling=id;
        data=JSON.parse(data);
        console.log(data);
        $("#planetName").html(data['name']);
        $("#districtsList").empty();
        $("#districtsList").append("<li class=\"list-group-item\">\n" +
            "                                <div class=\"row\">\n" +
            "                                    <h5 class=\"col-3 text-center\">区划</h5>\n" +
            "                                    <h5 class=\"col-2 text-center\">区划大小</h5>\n" +
            "                                    <h5 class=\"col-2 text-center\">所有制</h5>\n" +
            "                                    <h5 class=\"col-3 text-center\">现金池</h5>\n" +
            "                                    <h5 class=\"col-2 text-center\">利润</h5>\n" +
            "                                </div>\n" +
            "                            </li>");
        for (var key in data['districts']) {
            //data['districts'] = JSON.parse(data['districts']);
            if (data['districts'][key]['ownership'] == 0) {
                data['districts'][key]['ownership'] = "私有";
            } else if (data['districts'][key]['ownership'] == 1) {
                data['districts'][key]['ownership'] = "公有";
            } else {
                data['districts'][key]['ownership'] = "国有";
            }
            $("#districtsList").append("<li class=\"list-group-item\">\n" +
        "                                <div class=\"row\">\n" +
        "                                    <p class=\"col-3 text-center\">"+data['districts'][key]['name']+"</p>\n" +
        "                                    <p class=\"col-2 text-center\">"+data['districts'][key]['size']+"</p>\n" +
        "                                    <p class=\"col-2 text-center\">"+data['districts'][key]['ownership']+"</p>\n" +
        "                                    <p class=\"col-3 text-center\">"+Math.round(data['districts'][key]['cash'])+"</p>\n" +
        "                                    <p class=\"col-2 text-center\">"+data['districts'][key]['profit']+"</p>\n" +
        "                                </div>\n" +
        "                            </li>");
        }
        $("#pops").empty();
        for (var key in data['pops']) {
            $("#pops").append("<div class=\"card col-2\">\n" +
                "                   <div class=\"card-body\">\n" +
                "                       <h7 class=\"card-title text-center\">"+data['pops'][key][0]+"</h7>" +
                "                       <p class='text-center'>"+data['pops'][key][1]+"</p>" +
                "                   </div>" +
                "              </div> ");
        }
        $("#marketProduct").empty();
        for (var key in data['product']['market']) {
            if (data['product']['market'][key] > 0) {
                $("#marketProduct").append("<span class=\"badge bg-light text-dark\"><img src='storage/img/resource/"+key+".png'/>"+data['product']['market'][key]+"</span>");
            } else if (data['product']['market'][key] > 0) {
                $("#marketProduct").append("<span class=\"badge bg-light text-dark\"><img src='storage/img/resource/"+key+".png'/>"+data['product']['market'][key]+"</span>");
            }
        }
        $("#countryProduct").empty();
        for (var key in data['product']['country']) {
            if (data['product']['country'][key] > 0) {
                $("#countryProduct").append("<span class=\"badge bg-light text-dark\"><img src='storage/img/resource/"+key+".png'/>"+data['product']['country'][key]+"</span>");
            } else if (data['product']['country'][key] > 0) {
                $("#countryProduct").append("<span class=\"badge bg-danger text-dark\"><img src='storage/img/resource/"+key+".png'/>"+data['product']['country'][key]+"</span>");
            }
        }
        $("#adminButton").empty();
        if (privilege == 0 || privilege == 1) {
            $("#adminButton").append("<button type=\"button\" class=\"btn btn-primary\" data-bs-target=\"#newPopModal\" data-bs-toggle=\"modal\" data-bs-dismiss=\"modal\">新建人口</button>");
            $("#adminButton").append("<button type=\"button\" class=\"btn btn-primary\" data-bs-target=\"#newMarketDistrictModal\" data-bs-toggle=\"modal\" data-bs-dismiss=\"modal\">新建市场区划</button>");
        }
    });
    const planetModal = new bootstrap.Modal("#planetModal");
    planetModal.show();
}
