function mainFunction() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/MainFunction', {
    },function(data) {
    });
}
function changeTax(id,type) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let val = 0;
    if  (type == 'districtTax') {
        val = $("#districtTax").val();
        $("#dTax").html(val);
    } else if (type == 'upPopTax') {
        val = $("#upPopTax").val();
        $("#upTax").html(val);
    } else if (type == 'midPopTax') {
        val = $("#midPopTax").val();
        $("#midTax").html(val);
    } else if (type == 'lowPopTax') {
        val = $("#lowPopTax").val();
        $("#lowTax").html(val);
    }
    $.post('/Action/ChangeTax', {
        id:id,
        type:type,
        tax : val,
    },function(data) {
    });
}
