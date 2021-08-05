<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Form\Tests\Doctrine\Type;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Klipper\Component\Form\Doctrine\ChoiceList\ORMQueryBuilderLoader;
use Klipper\Component\Form\Doctrine\ChoiceList\QueryBuilderTransformer;
use Klipper\Component\Form\Doctrine\Type\EntityType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Tests case for entity type.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class EntityTypeTest extends TestCase
{
    public function testGetLoader(): void
    {
        /** @var ManagerRegistry $mr */
        $mr = $this->getMockBuilder(ManagerRegistry::class)->getMock();
        /** @var ObjectManager $om */
        $om = $this->getMockBuilder(ObjectManager::class)->getMock();
        /** @var QueryBuilder $qb */
        $qb = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        /** @var FormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject $builder */
        $builder = $this->getMockBuilder(FormBuilderInterface::class)->getMock();

        $type = new EntityType($mr);
        $type->configureOptions(new OptionsResolver());
        $loader = $type->getLoader($om, $qb, \stdClass::class);
        $type->buildForm($builder, [
            'multiple' => false,
            'query_builder_transformer' => new QueryBuilderTransformer(),
        ]);

        static::assertInstanceOf(ORMQueryBuilderLoader::class, $loader);
    }
}
