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
            if(value.length < 6 ) {
                return "密码长度不能小于6位";
            }else if(value.length > 20){
                return "密码长度不能大于20位";
            }
        },
        confirmPwd : function(value, item){
            if(!new RegExp($("#oldPwd").val()).test(value)){
                return "两次输入密码不一致，请重新输入！";
            }
        }
    })
    $.ajax({
        url : rootUrl+"/User/teacherInfo",
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
    form.on("submit(changePwd)",function(){

        $.ajax({
            url : rootUrl+"/User/changePwd",
            type : "post",
            data : {
                oldPwd: $(".oldPwd").val(),
                newPwd: $(".newPwd").val()
            },
            dataType:'json',
            success : function(res)
            {
                if(res.code == 2){
                    layer.msg(res.msg);
                }else if(res.code == 0){
                    setTimeout('window.location.reload()',1000); // 刷新页面
                    layer.msg("密码修改成功！");
                }else{
                    layer.msg("密码修改失败！");
                }
            },error:function(){
                layer.msg("服务器错误！");
            }
        });
    })

})