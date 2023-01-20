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
