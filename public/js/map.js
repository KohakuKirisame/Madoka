function changeOwner(id,owner) {
    $.post('/Action/ChangeOwner',{
        id : id,
        owner : owner,
        type : "changeOwner"
    },function(color) {
        $("#"+id+"MenuLink").css("background-color",color);
    })
}
