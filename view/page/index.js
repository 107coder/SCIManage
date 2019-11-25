layui.use(['form','layer','laydate','table','laytpl'],function(){
    var form = layui.form,
        layer = parent.layer === undefined ? layui.layer : top.layer,
        $ = layui.jquery,
        laydate = layui.laydate,
        laytpl = layui.laytpl,
        table = layui.table;

    $.ajax({
            url : rootUrl+"/user/teacherInfo",
            dataType:"JSON",
            type : "post",
            data : {},
            async : false,
            success : function(res)
            {
                $(".adminName").text(res['data'].name);
                $(".userName").text(res['data'].name);
            }
    });


})

// 退出登录的操作

function signOut(){
    $.ajax({
        url:rootUrl+"/Login/signOut",
        type:'post',
        data:{},
        dataType:'json',
        success:function(res){  
            if(res.code == 0){
                layer.msg(res.msg);
                setTimeout(() => {
                    // 崔少峰
                    window.location.href = 'http://localhost/107/SCIManage/view/page/login/login.html';
                    //赵士顺
                    // window.location.href = 'http://localhost:88/SCIManage/view/page/login/login.html';      
                }, 1000);
            }
        },error:function(){
            layer.msg("服务器错误，退出失败！");
        }            
    });
}