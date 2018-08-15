/**
 * localdb
 * @type {{check: (function(): Storage), save: localdb.save, get: (function(*=): (string | null)), get_key: (function(*=): (string | null)), remove: localdb.remove, remove_all: localdb.remove_all, length: (function(): number)}}
 */
var localdb = {
    check: function () {
        return window.localStorage;
    },
    set: function (key, value) {
        localStorage.setItem(key, value);
    },
    get: function (key) {
        return localStorage.getItem(key);
    },
    get_key: function (index) {
        return localStorage.key(index);
    },
    remove: function (key) {
        localStorage.removeItem(key);
    },
    remove_all: function () {
        localStorage.clear();
    },
    length: function () {
        return localStorage.length;
    },
};

let start = $('#start');
let clear = $('#clear');
let reports = $('#reports');
let money_data = $('#money_data');
money_data.focus();

start.click(function () {
    start.prop({disabled: true});
    start.html('计算中');
    $.post('calculation.php', {'money_data': money_data.val()}, function (result) {
        start.prop({disabled: false});
        start.html('开始计算');
        if (result.STATUS === 9) {
            swal(result.MESSAGE_ARRAY[0].MESSAGE);

            return false;
        } else if (result.STATUS === 0) { // 正确调用
            console.log(result.CHART_DATA);
            let draw_chart = drawChart();
            draw_chart.initChart('pie-chart');
            draw_chart.showPie(result.CHART_DATA);

            // 总结
            reports.html(result.MESSAGE_ARRAY[0].MESSAGE);
            reports.show();
        }
    }, 'json');
});

clear.click(function () {
    money_data.val('');
});

if (localdb.check()) {
    // 恢复输入框值
    money_data.val(localdb.get('money_data') ? localdb.get('money_data') : '');

    // 监听输入事件
    money_data.bind('input porpertychange', function () {
        localdb.set('money_data', money_data.val());
    });
} else {
    swal('你的浏览器不支持localStorage，本地的输入不会被实时保存，请注意');
}