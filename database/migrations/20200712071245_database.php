<?php

require_once './app/admin/common.php';

use think\migration\Migrator;
use think\migration\db\Column;

class Database extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table  =  $this->table('user',array(
                'engine'=>'InnoDB',
                'collation'=>'utf8mb4_general_ci',
            ));

        // 创建用户表
        $table->addColumn('username', 'string',array('limit'  =>  15,'default'=>'','comment'=>'用户名，登陆使用'))
            ->addColumn('password', 'string',array('limit'  =>  255,'default'=> password_encrypt('123456'),'comment'=>'用户密码'))
            ->addColumn('login_status', 'boolean',array('limit'  =>  1,'default'=>1,'comment'=>'登陆状态'))
            ->addColumn('login_code', 'string',array('limit'  =>  32,'default'=> '','comment'=>'排他性登陆标识'))
            ->addColumn('last_login_ip', 'string',array('limit'  =>  11,'default'=> '','comment'=>'最后登录IP'))
            ->addColumn('last_login_time', 'biginteger',array('limit'  =>  10,'default'=>0,'comment'=>'最后登录时间'))
            ->addColumn('is_delete', 'boolean',array('limit'  =>  1,'default'=>0,'comment'=>'删除状态，1已删除'))
            ->addColumn('create_time', 'biginteger',array('limit'  =>  10,'default'=>0,'comment'=>'创建时间'))
            ->addColumn('update_time', 'biginteger',array('limit'  =>  10,'default'=>0,'comment'=>'更新时间'))
            ->addIndex(array('username'), array('unique'  =>  true))
            ->create();

        // 创建用户角色表
        $table  =  $this->table('role',array(
            'engine'=>'InnoDB',
            'collation'=>'utf8mb4_general_ci',
        ));
        $table->addColumn('name', 'string',
            array('limit'  =>  32,'default'=> '管理员','comment'=>'管理员'))
            ->addColumn('status', 'boolean',
                array('limit'  =>  1,'default'=> 1,'comment'=>'状态'))
            ->addIndex(array('name'), array('unique'  =>  true))
            ->create();
    }
    public function up()
    {
//        parent::up(); // TODO: Change the autogenerated stub
    }
    public function down()
    {
//        parent::down(); // TODO: Change the autogenerated stub
    }
}
