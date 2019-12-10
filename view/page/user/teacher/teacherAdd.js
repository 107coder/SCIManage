layui.use(['form','layer'],function(){
    var form = layui.form
        layer = parent.layer === undefined ? layui.layer : top.layer,
        $ = layui.jquery;

    form.on("submit(addTeacher)",function(data){
        var index = top.layer.msg('数据提交中，请稍候',{icon: 16,time:false,shade:0.8});
        $.ajax({
                    url : rootUrl+"/user/addTeacher",
                    type : "post",
                    data : {
                        job_number : $(".job_number").val(),  //工号
                        name : $(".name").val(),  //姓名
                        gender : data.field.gender,  //性别
                        academy : $(".academy").val(),
                        birthday : $(".birthday").val(),
                        edu_background : $(".edu_background").val(),
                        degree : $(".degree").val(),
                        job_title : $(".job_title").val(),
                        job_title_rank : $(".job_title_rank").val(),
                        job_title_series : $(".job_title_series").val(),
                        identity : data.field.identity,  //用户等级 
                        full_spell: $(".full_spell").val()
                    },
                    async : false,
                    success : function(res,status)
                    {
                        if(status=="success"){
                            top.layer.close(index);
                            top.layer.msg("用户添加成功！");
                            layer.closeAll("iframe");
                            parent.location.reload();
                        }else{
                            top.layer.close(index);
                            top.layer.msg("用户添加失败，请重新添加！");
                        }
                    }
            });
    })
    // 通过不同身份验证，判断不同身份的用户显示的按钮不同
    $.ajax({
        url:rootUrl+'/Login/isLogin',
        type:'post',
        dataType:'json',
        success:function (res) {
            if(res.code == 4)
            {
                location.href = webRoot + '/page/login/login.html';
                layer.msg("请先登录");
                return ;
            }else if(res.code == 0){
                if(res.data['identity']==2){
                    // 校级管理员 可以更改用户权限
                }else if(res.data['identity']==1){
                    $('.tips-rm').remove();
                    $('.academy').attr('disabled','disabled');
                    $('.academy').val(res.data['academy']);
                    $("dd[lay-value='1']").remove();
                    $("dd[lay-value='2']").remove();
                }else{
                    return false;
                }
            }
        },error:function()
        {
            console.log('error');
        }
    });
})