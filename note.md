ZF2学习笔记
=======================

model层的运行原理
------------
模型实体对象是一个简单的 PHP 类。为了与 Zend\Db 的 TableGateway 类一起工作。需要实现 exchangeArray() 方法。这个方法简单地将 data 数组中的数据拷贝到对应实体属性。

下一步，在 module/Album/src/Album/Model 目录下新建 AlbumTable.php，内容如下：

