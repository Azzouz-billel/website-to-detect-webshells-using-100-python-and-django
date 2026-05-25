<?php

namespace Symfony\Component\DependencyInjection\Tests\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\AutowireRequiredMethodsPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveClassPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
require_once __DIR__ . '/../Fixtures/includes/autowiring_classes.php';
class AutowireRequiredMethodsPassTest extends TestCase
{
    public function testSetterInjection()
    {
        $container = new ContainerBuilder();
        $container->register(Foo::class);
        $container->register(A::class);
        $container->register(CollisionA::class);
        $container->register(CollisionB::class);
        $container->register('setter_injection', SetterInjection::class)->setAutowired(true)->addMethodCall('setWithCallsConfigured', array('manual_arg1', 'manual_arg2'));
        (new ResolveClassPass())->process($container);
        (new AutowireRequiredMethodsPass())->process($container);
        $methodCalls = $container->getDefinition('setter_injection')->getMethodCalls();
        $this->assertEquals(array('setWithCallsConfigured', 'setFoo', 'setDependencies', 'setChildMethodWithoutDocBlock'), array_column($methodCalls, 0));
        $this->assertEquals(array('manual_arg1', 'manual_arg2'), $methodCalls[0][1]);
        $this->assertEquals(array(), $methodCalls[1][1]);
    }
    public function testExplicitMethodInjection()
    {
        $container = new ContainerBuilder();
        $container->register(Foo::class);
        $container->register(A::class);
        $container->register(CollisionA::class);
        $container->register(CollisionB::class);
        $container->register('setter_injection', SetterInjection::class)->setAutowired(true)->addMethodCall('notASetter', array());
        (new ResolveClassPass())->process($container);
        (new AutowireRequiredMethodsPass())->process($container);
        $methodCalls = $container->getDefinition('setter_injection')->getMethodCalls();
        $this->assertEquals(array('notASetter', 'setFoo', 'setDependencies', 'setWithCallsConfigured', 'setChildMethodWithoutDocBlock'), array_column($methodCalls, 0));
        $this->assertEquals(array(), $methodCalls[0][1]);
    }
}
