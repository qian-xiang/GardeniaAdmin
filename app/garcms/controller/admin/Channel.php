<?php


namespace app\garcms\controller\admin;


use app\admin\AdminController;
use app\garcms\validate\admin\ChannelValidate;

class Channel extends AdminController
{
    public function index() {

    }
    public function add() {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $validate = new ChannelValidate();
            $rule = $validate->setAddChannelRule();
            if (!$validate->check($data)) {
                error($validate->getError());
            }
            $data = make_validate_rule_data($rule,$data);

        }
        $this->view();
    }
    public function edit() {

    }
    public function delete() {

    }
}