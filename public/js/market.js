let preChoose = '';
let preChooseRes = '';
function newTrade(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let target = $("#country").html();
    let resource = $("#resource").html();
    let value = $("#value").val();
    $.post('/Action/NewTrade',{
        id:id,
        target:target,
        resource:resource,
        value:value,
    },function(data) {
        location.reload();
    });
}
function deleteTrade(id,target, resource,value) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/DeleteTrade', {
        id: id,
        target: target,
        resource: resource,
        value: value,
    },function(data) {
        location.reload();
    });
}
function readMarket(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/ReadMarket',{
        id:id,
    },function(data) {
        data = JSON.parse(data);
        $("#prices").empty();
        if (preChoose!= '') {
            $("#"+preChoose+"").css("background-color","");
        }
        $("#country").empty();
        $("#"+id+"").css("background-color","lightgreen")
        $("#country").html(id);
        for(var key in data) {
            $('#prices').append("<div class=\"row\">" +
                "                   <button class=\"btn text-dark\" id=\""+key+"\" onclick=\"chooseResource('"+key+"')\">" +
                "                       <img src=\"storage/img/resource/"+key+".png\" width=\"30px\">"+Math.round(data[key]['price'])+" G/pre" +
                "                   </button>" +
                "               </div>"
            )
        }
        preChoose = id;
    });
}
function chooseResource(key){
    $("#resource").empty();
    if (preChooseRes != '') {
        $("#"+preChooseRes+"").css("background-color","white")
    }
    $("#resource").html(key);
    $("#"+key+"").css("background-color","lightgreen")
    preChooseRes = key;
}
