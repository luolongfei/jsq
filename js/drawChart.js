/*绘制echarts统计图*/

function drawChart() {
    return chartModel = {
        myChart: null,
        eConfig: '',
        pieOption: { // 饼状图
            title: {
                text: '画个饼状图',
                subtext: '小冷君可以说是土豪了~',
                x: 'center'
            },
            tooltip: {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c}元 ({d}%)"
            },
            legend: {
                orient: 'vertical',
                left: 'left',
                data: []
            },
            series: [
                {
                    name: '烧钱记录',
                    type: 'pie',
                    radius: '55%',
                    center: ['50%', '60%'],
                    data: [],
                    itemStyle: {
                        emphasis: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    },
                    roseType: false, // 展示成南丁格尔图 'radius' 扇区圆心角展现数据的百分比，半径展现数据的大小。'area' 所有扇区圆心角相同，仅通过半径展现数据大小。
                    labelLine: {
                        smooth: false,
                        lineStyle: {
                            width: 1
                        }
                    },
                }
            ],
        },
        initChart: function (id) {
            this.myChart = echarts.init(document.getElementById(id));
            this.eConfig = echarts.config;
        },
        showPie: function (data) {
            let option = this.pieOption;
            let legendData = [];
            for (let i = 0; i < data.length; i++) {
                legendData.push(data[i].name);
            }

            // legendData = ['直接访问', '邮件营销', '联盟广告', '视频广告', '搜索引擎'];
            option.legend.data = legendData;
            option.series[0].data = data;
            if (option && typeof option === 'object') {
                this.myChart.setOption(option, true);
            }
        }
    }
}