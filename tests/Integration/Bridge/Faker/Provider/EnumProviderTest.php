<?php

/*
 * This file is part of the "elao/enum" package.
 *
 * Copyright (C) Elao
 *
 * @author Elao <contact@elao.com>
 */

namespace Elao\Enum\Tests\Integration\Bridge\Faker\Provider;

use Elao\Enum\Bridge\Faker\Provider\EnumProvider;
use Elao\Enum\Tests\Fixtures\Enum\Permissions;
use Elao\Enum\Tests\Fixtures\Enum\SimpleEnum;
use Elao\Enum\Tests\Fixtures\EnumAware\SimpleEntity;
use Faker\Factory as FakerGeneratorFactory;
use Faker\Generator as FakerGenerator;
use Nelmio\Alice\Loader\NativeLoader;

class EnumProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testEnumProvider()
    {
        $loader = new EnumLoader();

        $entitySet = $loader->loadData([
            SimpleEntity::class => [
                'simple_entity1' => [
                    'permissions' => '<enum(Permissions::READ|WRITE)>',
                    'simpleEnum' => '<enum(Simple::FIRST)>',
                ],
                'simple_entity2' => [
                    'permissions' => '<randomEnum(Permissions)>',
                    'simpleEnum' => '<randomEnum(Simple)>',
                ],
            ],
        ]);

        $entities = $entitySet->getObjects();

        /** @var SimpleEntity $entity1 */
        $entity1 = $entities['simple_entity1'];
        $this->assertInstanceOf(Permissions::class, $entity1->permissions);
        $this->assertInstanceOf(SimpleEnum::class, $entity1->simpleEnum);
        $this->assertTrue($entity1->permissions->hasFlag(Permissions::READ));
        $this->assertTrue($entity1->permissions->hasFlag(Permissions::WRITE));
        $this->assertFalse($entity1->permissions->hasFlag(Permissions::EXECUTE));
        $this->assertTrue($entity1->simpleEnum->is(SimpleEnum::FIRST));

        /** @var SimpleEntity $entity2 */
        $entity2 = $entities['simple_entity2'];
        $this->assertInstanceOf(Permissions::class, $entity2->permissions);
        $this->assertInstanceOf(SimpleEnum::class, $entity2->simpleEnum);
    }
}

class EnumLoader extends NativeLoader
{
    protected function createFakerGenerator(): FakerGenerator
    {
        $generator = FakerGeneratorFactory::create();
        $generator->addProvider(new EnumProvider([
            'Simple' => SimpleEnum::class,
            'Permissions' => Permissions::class,
        ]));
        $generator->seed($this->getSeed());

        return $generator;
    }
}
