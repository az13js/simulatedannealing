<?php
/**
 * 将模拟退火的数据转换为图片，输出到Images文件夹内。
 *
 * 命令：php main.php 温度 循环次数 生成的第一张图片序号
 * 示例：php main.php 0.5 10 0
 * 示例说明：温度0.5，循环10次，输出图片从0开始命名
 *
 * 最后一次迭代出来的解，会序列化保存为“Solution.dat”。下次启动程序时，
 * 假如目录下存在此文件，则会从此文件中获取上一次的解。
 *
 * 问题规模是1000个城市，假如需要修改城市的个数，只需修改这个文件：
 * SimulatedAnnealing\SimulatedAnnealing.php
 * 里面有个const CITY_NUM = 1000;代表城市的个数。
 */
spl_autoload_register(function($class) {
    $ds = DIRECTORY_SEPARATOR;
    $file = __DIR__ . $ds . str_replace('\\', $ds, $class) . '.php';
    require $file;
});

use SimulatedAnnealing\SimulatedAnnealing;
use SimulatedAnnealing\Tsp;
use SimulatedAnnealing\GdFont;
use SimulatedAnnealing\Solution;

try {

    echo 'Temperature=' . $_SERVER['argv'][1] . PHP_EOL;
    echo 'Loop=' . $_SERVER['argv'][2] . PHP_EOL;
    echo 'Start=' . $_SERVER['argv'][3] . '.png' .  PHP_EOL;

    if (!file_exists('Solution.dat')) {
        $sa = new SimulatedAnnealing();
    } else {
        $sa = new SimulatedAnnealing(unserialize(file_get_contents('Solution.dat')));
    }

    $tspCityPoint = $sa->getSolution()->getTspQuestion()->getCityPoint();

    $data = $sa->keep($_SERVER['argv'][1], $_SERVER['argv'][2]);
    file_put_contents('Solution.dat', serialize($sa->getSolution()));

    $startId = $_SERVER['argv'][3];

    foreach ($data as $k => $d) {
        echo ($startId+$k).'.png'.PHP_EOL;

        $tsp = new Tsp(Solution::CITY_NUM);
        $tsp->setCityPoint($tspCityPoint);
        $tsp->setIndex($d['feature']);
        $tsp->saveLocationToImage('Images/'.($startId+$k).'.png');

        $texter = new GdFont('Images/'.($startId+$k).'.png', 'DejaVuSansMono.ttf', 16);
        $texter->printText(
            ' Temperature:'.$d['temperature'].PHP_EOL
            .' Cost:'.$d['cost'].PHP_EOL
            .' N:'.($startId+$k)
        );
    }
} catch (\Exception $e) {
    echo 'Error:' . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
}
