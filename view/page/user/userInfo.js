layui.use(['form','layer'],function(){
    var form = layui.form
        layer = parent.layer === undefined ? layui.layer : top.layer,
        $ = layui.jquery;

    form.verify({
        full_spell : function(value){
            if(value==""){
                return "姓名全拼不能为空！";
            }
            if(value.indexOf(",")==-1)
                return "请输入正确的格式！";
            }
        })
    $.ajax({
            url : rootUrl+"/user/userInfo",
            dataType:"JSON",
            type : "post",
            data : {},
            async : false,
            success : function(res)
            {
                if(res['data'].identity==2)
                    $identity="校级管理员";
                else if(res['data'].identity==1)
                    $identity="院级管理员";
                else 
                    $identity="普通用户";
                $(".job_number").val(res['data'].job_number);  
                $(".name").val(res['data'].name);
                $(".gender input[value="+res['data'].gender+"]").next(".layui-form-radio").find('i').click();
                $(".gender input[value!="+res['data'].gender+"]").attr("disabled",true);    //让其它单选框不可选 
                $(".academy").val(res['data'].academy);  
                $(".identity").val($identity); 
                $(".birthday").val(res['data'].birthday);
                $(".edu_background").val(res['data'].edu_background);
                $(".degree").val(res['data'].degree); 
                $(".full_spell").val(res['data'].full_spell);  
                $(".job_title").val(res['data'].job_title);
                $(".job_title_rank").val(res['data'].job_title_rank);
                $(".job_title_series").val(res['data'].job_title_series);
            }
        });
    
    form.on("submit(userInfo)",function(data){
        var index = top.layer.msg('数据提交中，请稍候',{icon: 16,time:false,shade:0.8});
        $.ajax({
                    url : rootUrl+"/user/changeUser",
                    type : "post",
                    data : {
                        job_number : $(".job_number").val(), 
                        birthday: $(".birthday").val(),
                        full_spell: $(".full_spell").val()
                        /*name : $(".name").val(),  
                        gender : data.field.gender,  
                        academy : $(".academy").val(),  
                        identity : data.field.identity,*/                          
                        /*edu_background: $(".edu_background").val(),
                        degree: $(".degree").val(),
                        job_title: $(".job_title").val(), 
                        job_title_rank: $(".job_title_rank").val(),
                        job_title_series: $(".job_title_series").val(),*/                       
                    },
                    async : false,
                    success : function(res,status)
                    {
                        if(status=="success"){
                            top.layer.close(index);
                            top.layer.msg("用户修改成功！");
                            layer.closeAll("iframe");
                            parent.location.reload();
                        }else{
                            top.layer.close(index);
                            top.layer.msg("用户修改失败！");
                        }
                    }
            });
    })

})