nowTech = '';
function chooseTech(id,area) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/ChooseTech',{
        id: id,
        area: area
    },function() {
        location.reload();
    });
}
function deleteTech(id,tech) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/DeleteTech',{
        id: id,
        tech:tech,
    },function() {
        location.reload();
    });
}
function readAllowance(allowance,tech) {
    let nowTech = tech;
    $('#allowance').val(allowance);
    $('#nowTech').html(nowTech);
}
function changeAllowance(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let allowance = $('#allowance').val();
    nowTech = $('#nowTech').html();
    console.log(nowTech);
    $.post('/Action/ChangeAllowance',{
        id: id,
        tech:nowTech,
        allowance:allowance
    },function() {
        location.reload();
    });
}
function adminAddTech(country,tech) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post('/Action/AdminAddTech',{
        country: country,
        tech: tech
    },function() {
        location.reload();
    });
}
