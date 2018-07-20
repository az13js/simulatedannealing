<?php
namespace SimulatedAnnealing;

/**
 * 使用GD库为图片添加文字
 */
class GdFont
{
    private $nextWriteHeight = 0;
    private $file = '';
    private $image = null;
    private $trueType = '';
    private $fontSize = 0; // 只有$trueType指定的文件有效时，此值才会发生作用

    public function __construct($file, $trueType = '', $trueTypeFontSize = 12)
    {
        $this->trueType = $trueType;
        $this->file = $file;
        $this->fontSize = $trueTypeFontSize;
        if (!file_exists($file)) {
            throw new \Exception('File '.$file.' not exists!');
        }
        if (!empty($this->trueType)) {
            if (!file_exists($this->trueType)) {
                throw new \Exception('Font file '.$this->trueType.' not exists!');
            }
        }
    }

    /**
     * 将文字写入图片
     *
     * 写入顺序，从左到右，从上到下
     *
     * @param string $text 文字
     * @return bool true|false
     */
    public function printText($text)
    {
        $this->openImage();
        $string = explode(PHP_EOL, $text);
        foreach ($string as $str) {
            if (!$this->printTextLine($str, $this->nextWriteHeight, $this->trueType)) {
                return false;
            }
            if (empty($this->trueType)) {
                $this->nextWriteHeight += 16;
            } else {
                $this->nextWriteHeight += $this->fontSize;
            }
        }
        $this->closeImage();
        return true;
    }

    /**
     * 将文字写入图片
     *
     * 实际测试字符的宽度不超过9，高度最大不超过16。
     *
     * @param string $text 文字
     * @param int $y 距离图片顶部的距离
     * @return bool true|false
     */
    private function printTextLine($text, $y = 0, $trueType = '')
    {
        $result = true;
        try {
            if ('' == $trueType) {
                imagestring($this->image, 5, 0, $y, $text, imagecolorallocate($this->image, 0, 0, 0));
            } else {
                imagettftext($this->image, $this->fontSize, 0, 0, 1.2 * ($this->fontSize + $y), imagecolorallocate($this->image, 0, 0, 0), $trueType, $text);
            }
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    private function openImage()
    {
        $this->image = imagecreatefrompng($this->file);
        imageantialias($this->image, true);
    }

    private function closeImage()
    {
        unlink($this->file);
        imagepng($this->image, $this->file);
        imagedestroy($this->image);
    }
}
