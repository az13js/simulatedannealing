<?php
namespace SimulatedAnnealing;

/**
 * 工厂类，提供问题的解
 *
 * 主要设计用来给SimulationAnnealing类调用，提供解。依赖接口类SolutionTemplate和实现该接口的Solution类。
 */
class SolutionFactory
{
    /**
     * 返回一个随机的解
     *
     * @return solutionTemplate 返回一个solutionTemplate对象
     */
    public function getRandomSolution()
    {
        return new Solution();
    }

    /**
     * 根据一个已有的解生成一个与此解比较接近的解
     *
     * @param solutionTemplate $solution 一个实现了solutionTemplate的对象
     * @return solutionTemplate 返回一个新的solutionTemplate对象
     */
    public function getSolution(SolutionTemplate $solution)
    {
        return new Solution($solution->getFeature());
    }
}