layui.use(['form','layer'],function(){
    var form = layui.form
        layer = parent.layer === undefined ? layui.layer : top.layer,
        $ = layui.jquery;

    form.verify({
        identity : function(val){
            if(!(val>=0&&val<=2)){
                return '请输入正确的身份！';
            }
        },
    })
    
    form.on("submit(editUser)",function(data){
        var index = top.layer.msg('数据提交中，请稍候',{icon: 16,time:false,shade:0.8});
        $.ajax({
                    url : rootUrl+"/user/editUser",
                    type : "post",
                    data : {
                        job_number : $(".job_number").val(),  //工号
                        name : $(".name").val(),  //姓名
                        gender : data.field.gender,  //性别
                        academy : $(".academy").val(),  //学院
                        identity : $(".identity").val(),  
                        birthday: $(".birthday").val(),
                        edu_background: $(".edu_background").val(),
                        degree: $(".degree").val(),
                        job_title: $(".job_title").val(),
                        job_title_rank: $(".job_title_rank").val(),
                        job_title_series: $(".job_title_series").val(),
                        full_spell: $(".full_spell").val()
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