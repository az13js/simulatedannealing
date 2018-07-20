<?php
namespace SimulatedAnnealing;

/**
 * 实现了SolutionTemplete的具体类
 */
class Solution implements SolutionTemplate
{
    const CITY_NUM = 1000;

    private $tsp = null;
    private $length = null;

    /**
     * 构造方法
     *
     * 如果创建实例的时候提供了一个特征，那么创建出来的实例将在这个特征上轻微变动一下，作为新的解。
     * 默认特征是null，将随机生成一个解。
     *
     * @param null|array $feature 特征值
     */
    public function __construct($feature = null)
    {

        $this->tsp = new Tsp(self::CITY_NUM);
        if (!is_null($feature)) {
            $index = $feature;
            $p1 = mt_rand(0, self::CITY_NUM - 1);
            $p2 = mt_rand(0, self::CITY_NUM - 1);
            $tmp = $index[$p1];
            $index[$p1] = $index[$p2];
            $index[$p2] = $tmp;
            $this->tsp->setIndex($index);
        }
    }

    public function getCost()
    {
        if (is_null($this->length)) {
            $this->length = $this->tsp->getIndexLength();
        }
        return $this->length;
    }

    public function getFeature()
    {
        return $this->tsp->getIndex();
    }

    public function getTspQuestion()
    {
        return $this->tsp;
    }
}
