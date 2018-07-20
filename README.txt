这份文件只说明如何安装并运行这个程序。

首先它依赖这样的环境：
PHP>=5.3，开启GD库；
ffmpeg，程序不需要，如果希望将图片合并为视频则最好装上。

另外，请在文件夹里放上一个DejaVuSansMono.ttf，其它字体文件也可以，但是文件名
需要修改（只支持TrueType字体）。

测试程序能不能跑起来：
php main.php 0.5 10 0

合成视频：
ffmpeg -threads 4 -y -r 24 -i "Images/%d.png" -vcodec libx264 -crf 18 output.mp4
