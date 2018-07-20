<?php
namespace SimulatedAnnealing;

/**
 * 假设有N个城市，它们从0开始编号直到N-1。给定一个具有N个元素的数组，这个数组内
 * 的元素都是整数，元素之间唯一不重复。于是数组就表示了N个城市的旅行顺序。
 *
 * 城市的位置并不是随机生成的，它们是以原点为中心半径为1的圆上的均匀分割的点的集合。
 * 选择这种做法的原因是，这样生成的城市在算法看来是随机的，但在人看来，可以直观
 * 地知道最优的值在什么位置。
 */
class Tsp
{
    /** $var array 代表N个城市，元素是数组，表示这个城市的坐标 */
    private $city = [];

    /** $var array 代表这N个城市的旅行顺序 */
    private $index = [];

    public function __construct($cityNumber)
    {
        for ($i = 0; $i < $cityNumber; $i++) {
            $this->city[] = [
                'x' => cos(2 * M_PI * $i / $cityNumber),
                'y' => sin(2 * M_PI * $i / $cityNumber),
            ];
            $this->index[] = $i;
        }
        // 初始化，打乱index的顺序
        for ($i = 0; $i < $cityNumber; $i++) {
            $r = mt_rand(0, $cityNumber - 1);
            $tmp = $this->index[$i];
            $this->index[$i] = $this->index[$r];
            $this->index[$r] = $tmp;
        }
    }

    /**
     * 将城市坐标保存到CSV文件内。
     *
     * @param string $file 文件名
     */
    public function saveCityToCSV($file)
    {
        $fileString = 'x,y'.PHP_EOL;
        foreach ($this->city as $city) {
            $fileString .= $city['x'].','.$city['y'].PHP_EOL;
        }
        file_put_contents($file, $fileString, LOCK_EX);
    }

    /**
     * 从指定文件读取城市坐标
     *
     * @param string $file 文件名
     */
    public function readCityFromCsv($file)
    {
        $fileString = file_get_contents($file);
        $fileDataRow = explode(PHP_EOL, $fileString);
        unset($fileString);
        $size = count($this->city, COUNT_NORMAL);
        for ($i = 0; $i < $size; $i++) {
            $data = explode(',', $fileDataRow[$i + 1]);
            $this->city[$i]['x'] = $data[0];
            $this->city[$i]['y'] = $data[1];
        }
    }

    /**
     * 将当前的城市坐标和旅行路径画出来，保存到图片文件
     *
     * 文件被保存为png格式，$file的参数格式如example.png、带完整路径的C:\\images\\example.png。
     *
     * @param string $file 图片文件名，可以使用完整的路径
     * @return bool true|false 成功返回true，失败返回false
     */
    public function saveLocationToImage($file)
    {
        $result = true;
        try {
            $width = 800;
            $height = 600;
            $citySize = 6;
            $citys = $this->changeCityFormat($this->city, $width, $height);
            $image = imagecreatetruecolor($width, $height);

            imageantialias($image, true);

            imagefilledrectangle($image,
                0, 0, $width, $height,
                imagecolorallocate($image, 255, 255, 255)
            ); // 背景色填充

            // 多边形绘制旅行路线
            $points = [];
            $num = count($citys, COUNT_NORMAL);
            for ($i = 0; $i < $num; $i++) {
                $points[] = $citys[$this->index[$i]]['x'];
                $points[] = $citys[$this->index[$i]]['y'];
            }
            imagepolygon($image, $points, $num, imagecolorallocate($image, 255, 0, 0));

            // 绘制城市的点
            $cityColor = imagecolorallocate($image, 0, 0, 255);
            foreach ($citys as $city) {
                imagefilledellipse($image, $city['x'], $city['y'], $citySize, $citySize, $cityColor); // 绘制圆点
            }

            if (file_exists($file)) {
                unlink($file);
            }

            imagepng($image, $file);
            imagedestroy($image);
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 获取旅行顺序
     *
     * @return array 数组，是第0到N-1个城市的次序
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * 设置旅行顺序
     *
     * @param array $idx 数组，是第0到N-1个城市的次序
     */
    public function setIndex(array $idx)
    {
        if (count($idx, COUNT_NORMAL) != count($this->index, COUNT_NORMAL)) {
            throw new \Exception('Tsp length of index: '.count($idx, COUNT_NORMAL).' != '.count($this->index, COUNT_NORMAL));
        }
        $this->index = $idx;
    }

    /**
     * 获取当前旅行顺序下的旅行路程
     *
     * @return float 路程
     */
    public function getIndexLength()
    {
        return $this->getLength($this->index);
    }

    /**
     * 获取城市坐标
     *
     * @return array 数组，是第0到N-1个城市的坐标
     */
    public function getCityPoint()
    {
        return $this->city;
    }

    /**
     * 设置城市坐标
     *
     * @param array $city 数组，是第0到N-1个城市
     */
    public function setCityPoint(array $city)
    {
        if (count($this->city, COUNT_NORMAL) != count($city, COUNT_NORMAL)) {
            throw new \Exception('Tsp length of city: '.count($city, COUNT_NORMAL).' != '.count($this->city, COUNT_NORMAL));
        }
        $this->city = $city;
    }

    /**
     * 根据旅行顺序计算旅行完成的距离
     *
     * @return float
     */
    public function getLength(array $index)
    {
        if (count($index, COUNT_NORMAL) != count($this->city, COUNT_NORMAL)) {
            return 0;
        }

        $size = count($index, COUNT_NORMAL);
        $totalLength = 0;
        for ($i = 0; $i < $size - 1; $i++) {
            // 计算index[i]到index[i+1]的城市之间的距离
            $totalLength += $this->calculateLength($this->city[$index[$i]], $this->city[$index[$i + 1]]);
        }
        $totalLength += $this->calculateLength($this->city[$index[0]], $this->city[$index[$size - 1]]);

        return $totalLength;
    }

    /**
     * 计算两个城市的距离
     *
     * @param array $pointA
     * @param array $pointB
     * @return float
     */
    private function calculateLength($pointA, $pointB)
    {
        $dx = $pointB['x'] - $pointA['x'];
        $dy = $pointB['y'] - $pointA['y'];
        return sqrt($dx * $dx + $dy * $dy);
    }

    /**
     * 坐标转换
     *
     * 城市的坐标要画到GD创建的图片上，需要先经过一个转换
     *
     * @param array $city $this->city
     * @param int $width GD图片宽
     * @param int $height GD图片高
     * @return array 转换后的坐标
     */
    private function changeCityFormat(array $city, $width, $height)
    {
        $num = count($city, COUNT_NORMAL);

        $xMin = $city[0]['x'];
        $yMin = $city[0]['y'];
        $xMax = $city[0]['x'];
        $yMax = $city[0]['y'];
        for ($i = 0; $i < $num; $i++) {
            if ($city[$i]['x'] < $xMin) {
                $xMin = $city[$i]['x'];
            }
            if ($city[$i]['y'] < $yMin) {
                $yMin = $city[$i]['y'];
            }
            if ($city[$i]['x'] > $xMax) {
                $xMax = $city[$i]['x'];
            }
            if ($city[$i]['y'] > $yMax) {
                $yMax = $city[$i]['y'];
            }
        }

        $dWidth = $xMax - $xMin;
        $dHeight = $yMax - $yMin;
        $dataCenter = ['x' => $dWidth/2 + $xMin, 'y' => $dHeight/2 + $yMin];

        // 计算放大倍数，X和Y的倍数选择最小的
        $alpha = 0;
        if ($dWidth > 0) {
            $alpha = $width * 0.9 / $dWidth;
        }
        if ($dHeight > 0) {
            $alphaY = $height * 0.9 / $dHeight;
            if ($alphaY < $alpha) {
                $alpha = $alphaY;
            }
        }

        // 将全部数据平移到新的中心，然后乘以倍数进行缩放，最后以左上角为零点转换成GD库绘图用坐标
        $leftTop = ['x' => -$width/2, 'y' => $height/2];
        $data = [];
        for ($i = 0; $i < $num; $i++) {
            $temp = [
                'x' => ($city[$i]['x'] - $dataCenter['x']) * $alpha,
                'y' => ($city[$i]['y'] - $dataCenter['y']) * $alpha,
            ];
            $data[] = [
                'x' => intval(abs($temp['x'] - $leftTop['x'])),
                'y' => intval(abs($temp['y'] - $leftTop['y'])),
            ];
        }

        return $data;
    }
}
