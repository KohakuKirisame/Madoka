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
