<?php
namespace SimulatedAnnealing;

/**
 * 问题的解方案的接口类
 *
 * 具体的解的类需要实现这个接口，然后会在工厂方法里被创建。
 */
interface SolutionTemplate
{
    public function __construct($feature = null); // 生成一个在给定特征附近的解

    public function getCost(); // 当前解的代价值

    public function getFeature(); // 返回当前解的特征
}
