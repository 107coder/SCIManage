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
                console.log(res[0].full_spell);
                if(res[0].full_spell==""||res[0].full_spell==null)
                   ifAddFullSpell(); 
            },
        });
    function ifAddFullSpell(){
        var index=layer.open({
            type:2,
            title:"提示框",
            move:false,
            area: ['400px', '200px'],
            // 赵士顺
            /*content: 'http://localhost:88/SCIManage/view/page/login/addFullSpell.html',*/
            // 崔少峰
            content: 'http://127.0.0.1/107/SCIManage/view/page/login/addFullSpell.html',
            // 右上角关闭按钮的点击事件
            cancel: function(){
                layer.msg("您必须先填写您的姓名全拼！");
                index.open();
            }
        }); 
    }
})
