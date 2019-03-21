<?php
// +----------------------------------------------------------------------
// | CoolCms [ DEVELOPMENT IS SO SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 http://www.coolcms.ccn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: Alan <251956250@qq.com>
// +----------------------------------------------------------------------

class Barcode
{
    //条形码数字
    public $number;
    public $scale;
    public $height;
    public $width;
    public $color;//条形码颜色为 0 到 255 的整数或者十六进制的 0x00 到 0xFF；格式为 ('0x00', '0x00', '0x00') 或者 (255,255,255),顺序为 红，绿，蓝
    private $key;
    private $code;
    private $images;
    public $pic_name;

    /**
     * 实例化
     * Barcode constructor.
     * @param null $number          条形码编码
     * @param int $scale            条形码大小倍数
     * @param int $height
     * @param array $color
     */
    public function __construct($number = null, $scale = 2, $height = 60, $color = array(0xFF, 0xFF, 0xFF))
    {
        $number = ($number == null) ? $this->_random() : $number;
        if (strlen($number) == 12) {
            $this->number = (string)$number . $this->_checknum($number);
        } else {
            $this->number = (string)$number;
        }
        $this->pic_name = 'images/'.$this->number.'.png';
        $this->scale = $scale;
        $this->height = $height * $this->scale;
        $this->width = 1.85 * $this->height;
        $this->color = $color;
        $this->key = $this->_parity('PARITY_KEY', substr($this->number, 0, 1));
        $this->code = $this->_encode();
        $this->_createImage();
        $this->_createBar();

    }
    //获取生成文件名称
    public function getPicNmae(){
        return $this->pic_name;
    }

    //生成code条形码
    public function _encode()
    {
        //开始位置
        $barcode[] = $this->_parity('GUARDS', 1);
        for ($i = 1; $i <= strlen($this->number) - 1; $i++) {
            if ($i < 7) {
                $barcode[] = $this->_parity('LEFT_PARITY', $this->key[$i - 1], substr($this->number, $i, 1));
            } else {
                $barcode[] = $this->_parity('RIGHT_PARITY', substr($this->number, $i, 1));
            }

            if ($i == 6) {
                $barcode[] = $this->_parity('GUARDS', 1);
            }
        }
        $barcode[] = $this->_parity('GUARDS', 2);
        return $barcode;

    }

    //创建一个空白图片
    public function _createImage()
    {
        $this->images = imagecreate($this->width, $this->height);
        imagecolorallocate($this->images, $this->color[0], $this->color[1], $this->color[2]);
    }


    //创建条形码
    public function _createBar()
    {
        $bar_color = ImageColorAllocate($this->images, 0x00, 0x00, 0x00);

        define("MAX", $this->height * 0.025);
        define("FLOOR", $this->height * 0.825);
        define("WIDTH", $this->scale);

        $x = ($this->height * 0.15) - WIDTH;

        foreach ($this->code as $bar) {
            $tall = 0;
            if (strlen($bar) == 3 || strlen($bar) == 5)
                $tall = ($this->height * 0.15);

            for ($i = 1; $i <= strlen($bar); $i++) {
                if (substr($bar, $i - 1, 1) === '1')
                    //在 image 图像中画一个用 color 颜色填充了的矩形，其左上角坐标为 x1，y1，右下角坐标为 x2，y2。0, 0 是图像的最左上角。
                    imagefilledrectangle($this->images, $x, MAX, $x + WIDTH-0.05, FLOOR + $tall, $bar_color);
                $x += WIDTH;
            }
        }
    }

    //创建包含文字的条形码图片
    public function createTextImg()
    {
        header('Content-type: image/png');
        $x = $this->width * 0.03;
        $y = $this->height * 0.99;

        $text_color = imagecolorallocate($this->images, 0, 0, 0);

        $font = dirname(__FILE__) . "/" . "Arial.ttf";
        $fontsize = $this->scale * (7);
        $kerning = $fontsize * 1;

        for ($i = 0; $i < strlen($this->number); $i++) {
            imagettftext($this->images, $fontsize, 0, $x, $y, $text_color, $font, $this->number[$i]);
            if ($i == 0 || $i == 6)
                $x += $kerning * 0.6;
            $x += $kerning;
        }
        //以 PNG 格式将图像输出
//        imagePng($this->images,$this->pic_name);
        imagePng($this->images);
        //释放资源
        imageDestroy($this->images);

    }


    //生成一个12位的随机数
    private function _random()
    {
        return substr(number_format(time() * rand(), 0, '', ''), 0, 12);
    }

    //通过12位数字 生成最后一位校验码
    private function _checknum($ean)
    {
        $ean = (string)$ean;
        $even = true;
        $esum = 0;
        $osum = 0;
        for ($i = strlen($ean) - 1; $i >= 0; $i--) {
            if ($even) $esum += $ean[$i]; else $osum += $ean[$i];
            $even = !$even;
        }
        return (10 - ((3 * $esum + $osum) % 10)) % 10;
    }

    //获取每个位置对应的转换码
    private function _parity($location, $i, $j = 0)
    {
        $key = array(
            'PARITY_KEY' => array(
                0 => "000000",
                1 => "001011",
                2 => "001101",
                3 => "001110",
                4 => "010011",
                5 => "011001",
                6 => "011100",
                7 => "010101",
                8 => "010110",
                9 => "011010"
            ),
            'LEFT_PARITY' => array(
                // Odd Encoding
                0 => array(
                    0 => "0001101",
                    1 => "0011001",
                    2 => "0010011",
                    3 => "0111101",
                    4 => "0100011",
                    5 => "0110001",
                    6 => "0101111",
                    7 => "0111011",
                    8 => "0110111",
                    9 => "0001011"
                ),
                // Even Encoding
                1 => array(
                    0 => "0100111",
                    1 => "0110011",
                    2 => "0011011",
                    3 => "0100001",
                    4 => "0011101",
                    5 => "0111001",
                    6 => "0000101",
                    7 => "0010001",
                    8 => "0001001",
                    9 => "0010111"
                )
            ),
            'RIGHT_PARITY' => array(
                0 => "1110010",
                1 => "1100110",
                2 => "1101100",
                3 => "1000010",
                4 => "1011100",
                5 => "1001110",
                6 => "1010000",
                7 => "1000100",
                8 => "1001000",
                9 => "1110100"
            ),
            'GUARDS' => array(
                '0' => "101",//start
                '1' => "01010",//middle
                '2' => "101",//end
            )
        );
        if ($location == 'LEFT_PARITY') {
            return $key[$location][$i][$j];
        } else {
            return $key[$location][$i];
        }

    }
}

$ss = new Barcode(6921734976529);
//echo $ss->getPicNmae();
$ss->createTextImg();
