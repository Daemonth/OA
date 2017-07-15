
      <div class="navbar-header aside-md">
        <a class="btn btn-link visible-xs" data-toggle="class:nav-off-screen,open" data-target="#nav,html">
          <i class="fa fa-bars"></i>
        </a>
        <a href="#" class="navbar-brand" data-toggle="fullscreen"><img src="<?php echo $this->basePath();?>/img/logo.png" class="m-r-sm">考勤管理系统</a>
        <a class="btn btn-link visible-xs" data-toggle="dropdown" data-target=".nav-user">
          <i class="fa fa-cog"></i>
        </a>
      </div>



      <ul class="nav navbar-nav navbar-right m-n hidden-xs nav-user">

        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <span class="thumb-sm avatar pull-left">
              <img src="<?php echo $this->basePath();?>/img/largelogo.png">
            </span>
            <?php
                    if(!empty($_SESSION['Zend_Auth'])) {
                echo '您好： ',$_SESSION['Zend_Auth']->storage->name;

      }  ?>

          </a>

        </li>
        <li>
          <a href="<?php echo $this->url('oa/default',array('controller'=>'user','action'=>'logout')); ?>"  >
              <?php
              if(!empty($_SESSION['Zend_Auth'])) {
                  echo '退出';
              }  ?>
              </a>
        </li>
      </ul>      
