layui.use(['form','layer','jquery'],function(){
    var form = layui.form,
        layer = parent.layer === undefined ? layui.layer : top.layer
        $ = layui.jquery;
        
    //判断是否首次登陆已填写姓名全拼
    $.ajax({
            url:rootUrl+'/Login/checkAddFullSpell',
            type:'post',
            dataType:"JSON",
            async : false,
            success:function (res) {
                if(res['data'].full_spell==""||res['data'].full_spell==null)
                   ifAddFullSpell(); 
            },
        });
    function ifAddFullSpell(){
        var index=layer.open({
            type:2,
            title:"提示框",
            move:false,
            area: ['500px', '220px'],
            
            content: webRoot + '/page/login/addFullSpell.html',
            // 右上角关闭按钮的点击事件
            cancel: function(){
                layer.msg("您必须先填写您的姓名全拼！");
                index.open();
            }
        }); 
    }
})
