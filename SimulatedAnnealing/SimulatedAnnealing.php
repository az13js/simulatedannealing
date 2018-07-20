<?php
namespace SimulatedAnnealing;

/**
 * 模拟退火过程主要模块
 *
 * 依赖于SolutionFactory。
 */
class SimulatedAnnealing
{
    /**
     * @var float 玻尔兹曼常数
     */
    const kb = 1.0;

    /**
     * @var null|SolutionTemplate 保存当前的解
     */
    private $solution = null;

    /**
     * @var SolutionFactory 用于获取新的解的工厂
     */
    private $factory = null;

    /**
     * 构造方法
     *
     * 当不带参数时，随机设置初始解。带入一个解的对象时，方法将会设置一个在给定解附近的解作为初始值。
     *
     * @param null|SolutionTemplate $solution 默认为null
     */
    public function __construct(SolutionTemplate $solution = null)
    {
        $this->factory = new SolutionFactory();
        if (is_null($solution)) {
            $this->solution = $this->factory->getRandomSolution();
        } else {
            $this->solution = $this->factory->getSolution($solution);
        }
    }

    /**
     * 在给定温度下循环一定次数，返回循环过程中的算法数据。
     *
     * 数据是一个二维数组，包含特征值，代价，温度，平均计算时间。
     *
     * @param float $t 温度
     * @param int $count 循环次数
     * @return array 算法运行相关的数据
     */
    public function keep($t, $count)
    {
        if ($t <= 0) {
            return [];
        }
        $costs = [];
        $startTime = explode(' ',microtime());
        for ($i = 0; $i < $count; $i++) {
            $tmp = $this->factory->getSolution($this->solution);
            $this->solution = $this->select($this->solution, $tmp, $t);
            $costs[$i]['feature'] = $this->solution->getFeature();
            $costs[$i]['cost'] = $this->solution->getCost();
            $costs[$i]['temperature'] = $t;
            $costs[$i]['time'] = 0;
        }
        $endTime = explode(' ',microtime());
        $perCount = (($endTime[1] - $startTime[1]) + ($endTime[0] - $startTime[0])) / $count;
        for ($i = 0; $i < $count; $i++) {
            $costs[$i]['time'] = $perCount;
        }
        return $costs;
    }

    /**
     * 获得当前的解
     *
     * @return SolutionTemplate 一个解对象
     */
    public function getSolution()
    {
        return $this->solution;
    }

    private function select($s, $tmp, $t)
    {
        $dE = $tmp->getCost() - $s->getCost();
        if ($dE < 0) {
            return $tmp;
        }
        $P = exp(-$dE / (self::kb * $t));
        if (mt_rand() / mt_getrandmax() < $P) {
            return $tmp;
        }
        return $s;
    }
}
