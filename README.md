# BarcodeToEAN-13
BarcodeToEAN-13，EAN13规则下的条形码封装

createTextImg()可以直接生成图片并保存到本地，如需保存到本地，请修改下$this->pic_name = 'images/'.$this->number.'.png';路径

如不填写条形码数字，会自动生成符合规则的条形码

调用方法如下：

1、实例化该类 

$Barcode = new Barcode();//默认可不传参

2、获取保存的文件名

$picNmae = $Barcode->getPicNmae();

3、生成条形码

$Barcode->createTextImg();

