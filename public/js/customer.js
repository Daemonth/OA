//对记录状态进行修改
function update(id,desc,num,params) {

    //alert($('#update1type'+id).val()+'---'+$('#update2type'+id).val()+'---'+num+'---'+$(desc).val());
    url = 'http://localhost/wbysvn/OA/public/record/update';
    data = {update1Type:$('#update1type'+id).val(),update2Type:$('#update2type'+id).val(),desc:$(desc).val(),id:id};
    $.post(url,data,function(text){
        $("#myAlert").alert();
        $("#myAlert").css({"display":"block"});
       window.location.href = 'http://localhost/wbysvn/OA/public/record/listpage/'+employeeId;

    });

}
//批量修改考勤记录
function updateAll(num,params) {
    result = confirm("您确定要修改吗？");
    if(!result) {
        return false;
    }
    url = '/record/updateall?time='+new Date().getTime();
    idString = '';
    valString1 = '';
    valString2 = '';
    descString = '';

    $('#record tbody tr select[name=time1] ').each(function(){
        //alert($(this).val());
        idString += ';'+ $(this).attr('id').substr(11);
        valString1 += ';'+ $(this).val();
    });
    $('#record tbody tr select[name=time2] ').each(function(){

        valString2 += ';'+ $(this).val();
    });
    $('#record tbody tr td :text').each(function(){
       descString += ':'+$(this).val();
    });

    if(idString.length>0){
        idString = idString.substr(1);
    }
    if(valString1.length>0) {
        valString1 = valString1.substr(1);
    }
    if(valString2.length>0) {
        valString2 = valString2.substr(1);
    }
    if(descString.length>0) {
        descString = descString.substr(1);
    }
    data = {ids:idString,val1:valString1,val2:valString2,desc:descString};
    $.post(url, data, function(text){
        alert(text);
        window.location.href = '/record/index/'+num+params;
    });

}

//工作日与非工作日进行调整
function adjust() {

    type = $('#adjust').val();
    day = $('#adjustday').val();
    pattern = /^\d{4}-\d{1,2}-\d{1,2}$/;
    regex = new RegExp(pattern);
    if(!regex.test(day)){
        alert('日期格式不对');
        return;
    }
    if(type == 0 || !day) {
        return false;
    }
    else if(type == 1) {
        result = confirm('您确定要将'+day+'调整为工作日吗？');
    } else if(type == 3) {
        result = confirm('您确认要将'+day+'调整为法定假日1吗？');
    }else if(type == 4) {
        result = confirm('您确认要将'+day+'调整为法定假日2吗？');
    }
    if(!result) {
        return false ;
    }

    url = '/record/adjust';
    data = {'day':day,'type':type};
    $.post(url,data,function(text) {
        alert(text);
    });

}

//免责处理
function mian(state,id) {
    if($(state).val() == '') {
        alert('请先输入调整时间');
        return false ;
    }
    url = '/record/mian?time='+new Date().getTime();


    data = {datetime:$(state).val(),'type':id};

    $.post(url,data, function (text) {
        alert(text);
    });
}

function half()
{
    if($('#halfDay').val() == '')
    {
        alert('日期不能为空');
        return false;
    }

    $.ajax({
        type:'POST',
        url:'/record/half',
        data:{halfDay:$('#halfDay').val(),role:$('#role').val(),dayType:$('input:radio:checked').val()},
        cache:false,
        dataType:'text',
        success:function(data,textStatus) {
            alert(data);
        },
        error:function(xhr,textStatus)  {
            alert('中途出错了！');

        }

    })
}



