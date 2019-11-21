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