layui.use(['form','layer','jquery'],function(){
    var form = layui.form,
        layer = parent.layer === undefined ? layui.layer : top.layer
        $ = layui.jquery;

    $(".loginBody .seraph").click(function(){
        layer.msg("这只是做个样式，至于功能，你见过哪个后台能这样登录的？还是老老实实的找管理员去注册吧",{
            time:5000
        });
    })
    
    //登录按钮
    form.on("submit(login)",function(data){
        // 获取前端页面输入的信息
        var loginData = data.field;
        var _this = $(this);
        $(this).text("登录中...").attr("disabled","disabled").addClass("layui-disabled");

        $.ajax({
            url:rootUrl+'/Login/checkLogin',
            data:{
                username:loginData.username,
                password:loginData.password
            },
            type:'post',
            dataType:'json',
            success:function (res) {
                if(res.code == 0)
                {
                    
                    layer.msg(res.msg);
                    setTimeout(function(){
                        window.location.href = webRoot ;
                      
                    },1000);
                }
                else
                {
                    layer.msg(res.msg);
                    _this.text("登录").removeAttr("disabled").removeClass("layui-disabled");
                }
            },
            error:function()
            {
                layer.msg('服务器出现错误，请联系系统管理员！');
                _this.text("登录").removeAttr("disabled").removeClass("layui-disabled");
            }
        });

        return false;
    })

    //表单输入效果
    $(".loginBody .input-item").click(function(e){
        e.stopPropagation();
        $(this).addClass("layui-input-focus").find(".layui-input").focus();
    })
    $(".loginBody .layui-form-item .layui-input").focus(function(){
        $(this).parent().addClass("layui-input-focus");
    })
    $(".loginBody .layui-form-item .layui-input").blur(function(){
        $(this).parent().removeClass("layui-input-focus");
        if($(this).val() != ''){
            $(this).parent().addClass("layui-input-active");
        }else{
            $(this).parent().removeClass("layui-input-active");
        }
    })
})
