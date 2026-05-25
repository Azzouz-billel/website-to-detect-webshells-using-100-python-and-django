<?php

namespace craft\web\twig\nodes;

use craft\helpers\UrlHelper;
use Twig\Compiler;
use Twig\Node\Node;
class RedirectNode extends Node
{
    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        if ($this->hasNode('error')) {
            $compiler->write('\\Craft::$app->getSession()->setError(')->subcompile($this->getNode('error'))->raw(");\n");
        }
        if ($this->hasNode('notice')) {
            $compiler->write('\\Craft::$app->getSession()->setNotice(')->subcompile($this->getNode('notice'))->raw(");\n");
        }
        $compiler->write('\\Craft::$app->getResponse()->redirect(' . UrlHelper::class . '::url(')->subcompile($this->getNode('path'))->raw('), ')->subcompile($this->getNode('httpStatusCode'))->raw(");\n")->write('\\Craft::$app->end();');
    }
}
