function changeOwner(id,owner) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/ChangeOwner',{
        id : id,
        owner : owner,
    },function(color) {
        $("#MenuLink-"+id).css("background-color",color);
    })
}
function newPlanet(id,type) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/NewPlanet',{
        id : id,
        type : type,
    },function() {

    })
}
function colonize(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/Colonize',{
        id : id,
    },function(data) {
        location.reload();
    });
}
function setTradeHub(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/SetTradeHub',{
        id : id,
    },function(data) {
        location.reload();
    });
}
