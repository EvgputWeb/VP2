<?php

require_once 'Controller.php';
require_once APP . '/models/User.php';


class MainController extends Controller
{
    public function actionIndex()
    {
        $userInfo = User::getUserInfoByCookie();

        $viewData = ['curSection' => ''];

        if ($userInfo['isLogined']) {
            $viewData['login'] = $userInfo['login'];
            $viewData['name'] = $userInfo['name'];
        }

        $this->view->render('main', $viewData);
    }
}
