<?php

namespace craft\web\twig\nodes;

use craft\helpers\Template;
use Twig\Compiler;
use Twig\Node\Node;
class ProfileNode extends Node
{
    public function __construct(string $stage, string $type, string $name)
    {
        parent::__construct([], compact('stage', 'type', 'name'));
    }
    public function compile(Compiler $compiler)
    {
        $compiler->write(Template::class . '::' . $this->getAttribute('stage') . 'Profile(')->repr($this->getAttribute('type'))->raw(', ')->repr($this->getAttribute('name'))->raw(");\n");
    }
}
