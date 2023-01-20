function changeOwner(id,owner) {
    $.post('/Action/ChangeOwner',{
        id : id,
        owner : owner,
    },function(color) {
        $("#"+id+"MenuLink").css("background-color",color);
    })
}
