<?php

namespace yiiunit\framework\filters;

use Yii;
use yii\base\Action;
use yii\filters\AjaxFilter;
use yii\web\Controller;
use yii\web\Request;
use yiiunit\TestCase;
class AjaxFilterTest extends TestCase
{
    protected function mockRequest($isAjax)
    {
        $request = $this->getMockBuilder('\\yii\\web\\Request')->setMethods(['getIsAjax'])->getMock();
        $request->method('getIsAjax')->willReturn($isAjax);
        return $request;
    }
    public function testFilter()
    {
        $this->mockWebApplication();
        $controller = new Controller('id', Yii::$app);
        $action = new Action('test', $controller);
        $filter = new AjaxFilter();
        $filter->request = $this->mockRequest(true);
        $this->assertTrue($filter->beforeAction($action));
        $filter->request = $this->mockRequest(false);
        $this->expectException('yii\\web\\BadRequestHttpException');
        $filter->beforeAction($action);
    }
}
