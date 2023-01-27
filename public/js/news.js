function editorShow(){
    var isShow = $("#type").val();
    if(isShow != 1){
        $("#editormd_id").show();
    }else{
        $("#editormd_id").hide();
    }
    if(isShow == 2) {
        $("#media_select").hide();
    }else{
        $("#media_select").show();
    }
}
editorShow();
