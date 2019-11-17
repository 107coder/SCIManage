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

    form.on("submit(addUser)",function(data){
        var index = top.layer.msg('数据提交中，请稍候',{icon: 16,time:false,shade:0.8});
        $.ajax({
                    url : rootUrl+"/user/addFullSpell",
                    type : "post",
                    data : {
                        full_spell: $(".full_spell").val()
                    },
                    async : false,
                    success : function(res,status)
                    {
                        if(status=="success"){
                            top.layer.close(index);
                            top.layer.msg("提交成功！");
                            var index = parent.layer.getFrameIndex(window.name); 
                            parent.layer.close(index); 
                            parent.location.reload();
                        }else{
                            top.layer.msg("提交失败，请重新提交！");
                        }
                    }
            });
    })

})