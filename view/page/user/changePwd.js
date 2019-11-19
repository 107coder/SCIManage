layui.use(['form','layer','laydate','table','laytpl'],function(){
    var form = layui.form,
        layer = parent.layer === undefined ? layui.layer : top.layer,
        $ = layui.jquery,
        laydate = layui.laydate,
        laytpl = layui.laytpl,
        table = layui.table;

    //添加验证规则
    form.verify({
        newPwd : function(value, item){
            if(value.length < 6){
                return "密码长度不能小于6位";
            }
        },
        confirmPwd : function(value, item){
            if(!new RegExp($("#oldPwd").val()).test(value)){
                return "两次输入密码不一致，请重新输入！";
            }
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
                $(".job_number").val(res['data'].job_number);
                $(".name").val(res['data'].name);
            }
        });
    form.on("submit(changePwd)",function(data){
        $.ajax({
                url : rootUrl+"/user/checkPwd",
                type : "post",
                data : {
                    oldPwd: $(".oldPwd").val()
                },
                async : false,
                success : function(res,status)
                {
                    if(res!="")
                        top.layer.msg("密码错误，请重新输入！");
                    else
                    {
                        $.ajax({
                            url : rootUrl+"/user/changePwd",
                            type : "post",
                            data : {
                                newPwd: $(".newPwd").val()
                            },
                            async : false,
                            success : function(res,status)
                            {
                                if(status=="success"){
                                    top.layer.msg("密码修改成功！");
                                    layer.closeAll("iframe");
                                    parent.location.reload();
                                }else{
                                    top.layer.msg("密码修改失败！");
                                }
                            }
                        });
                    }
                }
        });
    })

})