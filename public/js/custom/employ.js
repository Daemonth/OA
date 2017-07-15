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
        var url = '/employ/existemployee?time='+new Date().getTime();
        var data = {employeeId:val,type:opType,id:id};
        $.post(url,data,function(text){
            if(text == 'fail'){
                $('#showError').text("*已存在该员工号");
                $('.js_employeeId').focus();
            }
        })

    });

    $(document).on('blur','.js_attendId',function(){
        var val = $(this).val();
        var id = $('.js_id').val();
        if(id==0){
            var opType=2;
        }else {
            var opType=1;
        }
        var url = '/employ/existattendid?time='+new Date().getTime();
        var data = {attendId:val,type:opType,id:id};
        $.post(url,data,function(text){
            if(text == 'fail'){
                $('#showError').text("*已存在该考勤号");
                $('.js_attendId').focus();
            }
        })

    });

$(document).on('blur','.js_identify',function(){
    var val = $(this).val();
    var id = $('.js_id').val();
    if(id==0){
        var opType=2;
    }else {
        var opType=1;
    }
    var url = '/employ/existidentify?time='+new Date().getTime();
    var data = {identify:val,type:opType,id:id};
    $.post(url,data,function(text){
        if(text == 'fail'){
            $('#showError').text("*已存在该身份证号");
            $('.js_identify').focus();
        }
    })

});

function checkSubmit(){


    if($('#showError').text() != '' ){
        alert($('#showError').text()+',请填入正确信息');
        return false;
    }
    return true;
}



