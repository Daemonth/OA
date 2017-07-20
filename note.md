ZF2学习笔记
=======================

model层的运行原理
------------
模型实体对象是一个简单的 PHP 类。为了与 Zend\Db 的 TableGateway 类一起工作。需要实现 exchangeArray() 方法。这个方法简单地将 data 数组中的数据拷贝到对应实体属性。

下一步，在 module/Album/src/Album/Model 目录下新建 AlbumTable.php，内容如下：

namespace Album\Model;

use Zend\Db\TableGateway\TableGateway;

class AlbumTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getAlbum($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveAlbum(Album $album)
    {
        $data = array(
            'artist' => $album->artist,
            'title'  => $album->title,
        );

        $id = (int) $album->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getAlbum($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Album id does not exist');
            }
        }
    }

    public function deleteAlbum($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}

使用 ServiceManager 来配置 table gateway（，在本例中，也是数据处理的一部分。使用 Zend Framework 中的 Zend\Db\TableGateway\TableGateway 类，这个类是用来查找，插入，更新和删除数据库中表记录的。） 然后注入到 AlbumTable
为了总是使用同一个的 AlbumTable 实例，我们将使用 ServiceManager 来定义如何创建。最容易的是，在模块类中，我们创建一个名为 getServiceConfig() 的方法，它可以被 ModuleManager 自动调用，适用于 ServiceManager。然后，当我们需要它的时候，就可以取回它。

为了配置 ServiceManager，在 ServiceManager 需要的时候，我们提供一个类的名字或者一个工厂（闭包或者回调），来产生实例化的对象。我们通过实现 getServiceConfig() 来提供一个工厂，这个工厂用来创建一个 AlbumTable。添加这个方法到 module/Album 目录下的 Module.php 文件的底部。

这个方法返回 factories 的数组，在传递给 ServiceManager 之前，通过 ModuleManager 进行合并。Album\Model\AlbumTable 的工厂使用 ServiceManager 来创建一个 AlbumTableGateway 对象，以便传递到 AlbumTable 对象。一般会告知 ServiceManager 对象，AlbumTableGateway 对象的创建是通过得到一个 Zend\Db\Adapter\Adapter对象（也是从 ServiceManager 获取）来完成的，然后使用 AlbumTableGateway 对象来创建一个 TableGateway 对象。TableGateway 对象每当他创建一条新记录时，都会告知一个 Album 对象。TableGateway 类使用原型模式创建结果集和实体。这意味着在请求的时候不是实例化，而是系统克隆先前实例化的对象。


Album\Model\AlbumTable 的工厂->ServiceManager->Zend\Db\Adapter\AdapterServiceFactory(工厂)->Zend\Db\Adapter\Adapter->(创建)AlbumTableGateway->(创建)TableGateway->(操作告知模型)model


表单和动作
------------

我们用 Zend\Form 来处理这些。Zend\Form 控件管理表单和处理表单验证，添加一个 Zend\InputFilter 到 Album 实体。开始写我们的新类 Album\Form\AlbumForm，这个类继承自 Zend\Form\Form。在 module/Album/src/Album/Form 目录下新建一个 AlbumForm.php 文件
在 AlbumForm 的构造函数中，我们需要做一些事情。首先我们要设置表单的名字，调用父类构造函数。接着我们创建四个表单元素：id，title，artist，以及提交按钮。对每一项，我们都要设置各种各样的属性和设置，包括要显示的标签。

