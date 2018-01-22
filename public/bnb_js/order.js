/**
 * Created by WangGang@SDTY mailto:glogger#gmail.com on 2017/12/6.
 */

$(function(){

    // 按钮

    // 取消
    $(document).on("click", ".toCancel", function (e) {

        e.preventDefault();

        var order_sn = $(this).attr('data-sn');
        if (order_sn) {

            var data = {'order_sn': order_sn};

            var btnArray = ['否', '取消订单'];
            mui.confirm(('确定要取消订单吗？'), '取消订单', btnArray, function (c) {

                if (c.index == 1) {
                    $.ajax({
                        url: url_cancel,
                        type: 'post',
                        data: data,
                        dataType: 'json',
                        success: function (result) {
                            if (result.code == 1) {
                                mui.alert(('取消订单成功'), '取消订单', function () {
                                    window.location.href = url_orderlist;
                                });
                            } else {
                                mui.alert('取消订单失败' + result.msg, '错误提示');
                            }

                            return false;
                        }
                    });
                }
            });
        }
    });

    // 删除
    $(document).on("click", ".toDelete", function (e) {

        e.preventDefault();

        var order_sn = $(this).attr('data-sn');
        if (order_sn) {

            var data = {'order_sn': order_sn};

            var btnArray = ['否', '删除订单'];
            mui.confirm(('确定要删除订单吗？'), '删除订单', btnArray, function (c) {

                if (c.index == 1) {
                    $.ajax({
                        url: url_delete,
                        type: 'post',
                        data: data,
                        dataType: 'json',
                        success: function (result) {
                            if (result.code == 1) {
                                mui.alert(('删除订单成功'), '删除订单', function () {
                                    window.location.href = url_orderlist;
                                });
                            } else {
                                mui.alert('删除订单失败' + result.message, '错误提示');
                            }
                            return false;
                        }
                    });
                }
            });
        }
    });



    // 支付
    $(document).on("click", ".toPay", function (e) {

        e.preventDefault();
        var order_sn = $(this).attr('data-sn');
        if (order_sn) {

            var url = url_pay+"?order_sn="+order_sn;
            window.location.href = url;
        }
    });


    // 详情
    $(document).on("click", ".toDetail", function (e) {

        e.preventDefault();
        var order_sn = $(this).attr('data-sn');
        if (order_sn) {

            var url = url_detail+"?order_sn="+order_sn;
            window.location.href = url;
        }
    });


    // 保洁

    $(document).on("click", ".toClean", function (e) {

        e.preventDefault();
        var order_sn = $(this).attr('data-sn');
        if (order_sn) {
            var url = url_clean+"?order_sn="+order_sn;
            window.location.href = url;
        }
    });


    // 评价
    $(document).on("click", ".toEvaluate", function (e) {
        e.preventDefault();
        var order_sn = $(this).attr('data-sn');
        if (order_sn) {

            var url = url_evaluate+"?order_sn="+order_sn;
            window.location.href = url;
        }
    });


    // 再次预订
    $(document).on("click", ".toOrderagain", function (e) {
        e.preventDefault();
        var bnb_id = $(this).attr('data-bnb_id');
        if (bnb_id) {
            var url = url_orderagain+"?id="+bnb_id;
            window.location.href = url;
        }
    });

});