/**
 * Created by wen on 2016/4/6.
 */



    $(document).on('blur','.js_employeeId',function(){
        var val = $(this).val();
        var id = $('.js_id').val();
        if(id==0){
            var opType=2;
        }else {
            var opType=1;
        }
        var url = '/user/existemployee?time='+new Date().getTime();
        var data = {employeeId:val,type:opType,id:id};
        $.post(url,data,function(text){
            if(text == 'fail'){
                $('#showError').text("*已存在该员工号");
                $('.js_employeeId').focus();
            }
        })

    });

$(document).on('blur','.js_email',function(){
    var val = $(this).val();
    var id = $('.js_id').val();
    if(id==0){
        var opType=2;
    }else {
        var opType=1;
    }
    var url = '/user/existemail?time='+new Date().getTime();
    var data = {email:val,type:opType,id:id};
    $.post(url,data,function(text){
        if(text == 'fail'){
            $('#showError').text("*已存在该邮箱号");
            $('.js_email').focus();
        }
    })

});

function checkSubmit(){

    if($('#showError').text() != ''){
        alert($('#showError').text()+',请填入正确信息');
        return false;
    }

    return true;
}



