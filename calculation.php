<?php
/**
 * 算账
 * @author mybsdc <mybsdc@gmail.com>
 * @date 2018/8/14
 * @time 21:07
 */

header('Content-Type: application/json');

/**
 * 自定义错误处理
 */
register_shutdown_function('customize_error_handler');
function customize_error_handler()
{
    if (!is_null($error = error_get_last())) {
        system_log($error);

        $response = [
            'STATUS' => 9,
            'MESSAGE_ARRAY' => array(
                array(
                    'MESSAGE' => '程序执行出错，请稍后再试。'
                )
            ),
            'SYSTEM_DATE' => date('Y-m-d H:i:s')
        ];

        echo json_encode($response);
    }
}

/**
 * 记录程序日志
 * @param array|string $logContent 日志内容
 * @param string $mark LOG | ERROR | WARNING 日志标志
 */
function system_log($logContent, $mark = 'LOG')
{
    try {
        $logPath = __DIR__ . '/logs/' . date('Y') . '/' . date('m') . '/';
        $logFile = $logPath . date('d') . '.php';

        if (!is_dir($logPath)) {
            mkdir($logPath, 0777, true);
            chmod($logPath, 0777);
        }

        $handle = fopen($logFile, 'a'); // 文件不存在则自动创建

        if (!filesize($logFile)) {
            fwrite($handle, "<?php defined('VENDOR_PATH') or die('No direct script access allowed.'); ?>" . PHP_EOL . PHP_EOL);
            chmod($logFile, 0666);
        }

        fwrite($handle, $mark . ' - ' . date('Y-m-d H:i:s') . ' --> ' . (PHP_SAPI === 'cli' ? 'CLI' : 'URI: ' . $_SERVER['REQUEST_URI'] . PHP_EOL . 'REMOTE_ADDR: ' . $_SERVER['REMOTE_ADDR'] . PHP_EOL . 'SERVER_ADDR: ' . $_SERVER['SERVER_ADDR']) . PHP_EOL . (is_string($logContent) ? $logContent : var_export($logContent, true)) . PHP_EOL); // CLI模式下，$_SERVER中几乎无可用值

        fclose($handle);
    } catch (\Exception $e) {
        // DO NOTHING
    }
}

try {
    $data_regex = '/([^—\d\n]+)(\d+(?:\.\d+)?元? *\+ *\d+(?:\.\d+)?元? *[＝=] *(\d+(?:\.\d+)?)元?|(?<![≈―])\d+)元?(?=\n*)/imu';

    if (!isset($_POST['money_data']) || !strlen($_POST['money_data'])) throw new \Exception('你没有输入任何内容，无法计算');
    system_log("\n========================== 原始数据（输入） ==========================\n\n" . $_POST['money_data']);
    if (!preg_match_all($data_regex, $_POST['money_data'], $matches, PREG_SET_ORDER)) throw new \Exception('你输入的内容格式好像不正确，去问下罗叔叔');

    // 开始计算
    $total = 0;
    $category_total = []; // 分类合计
    foreach ($matches as $v) {
        if (isset($v[3])) { // 手算结果的情况
            $total += $v[3];

            if (array_key_exists($v[1], $category_total)) { // 同类型归为一类
                $category_total[$v[1]] += $v[3];
            } else {
                $category_total[$v[1]] = floatval($v[3]);
            }
        } else {
            $total += $v[2];

            if (array_key_exists($v[1], $category_total)) { // 同类型归为一类
                $category_total[$v[1]] += $v[2];
            } else {
                $category_total[$v[1]] = floatval($v[2]);
            }
        }
    }

    $chart_data = [];
    $reports = ''; // 总结
    foreach ($category_total as $name => $value) {
        $reports .= $name . "共花了<span class='amount'>" . $value . "</span>元，\n";
        $chart_data[] = [
            'name' => $name,
            'value' => $value
        ];
    }
    $reports = sprintf("小冷君，你的钱都用在了这些地方：\n%s\n罗叔叔帮你算了一下，总共花了<span class='amount'>%s</span>元。可以说相当土豪了~", $reports, $total);
    system_log("\n========================== 执行报告（输出） ==========================\n\n" . $reports);

    $response = [
        'STATUS' => 0,
        'CHART_DATA' => $chart_data,
        'TOTAL' => $total,
        'MESSAGE_ARRAY' => array(
            array(
                'MESSAGE' => $reports
            )
        ),
        'SYSTEM_DATE' => date('Y-m-d H:i:s')
    ];

    echo json_encode($response);
} catch (\Exception $e) {
    $response = [
        'STATUS' => 9,
        'MESSAGE_ARRAY' => array(
            array(
                'MESSAGE' => $e->getMessage()
            )
        ),
        'SYSTEM_DATE' => date('Y-m-d H:i:s')
    ];

    echo json_encode($response);
}