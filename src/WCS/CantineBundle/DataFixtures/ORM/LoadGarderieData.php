<?php
namespace WCS\CantineBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WCS\CantineBundle\Entity\Garderie;


class LoadGarderieData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $inscs  = [
            ['2016-06-21', 'Bouteiller-Isabelle', true, true],
            ['2016-06-21', 'Dupontel-Jean-Kevin', true, true],
            ['2016-06-21', 'Dupontel-Kevina', true, true],
            ['2016-06-21', 'Dupontel-Mathilde', true, true],
            ['2016-06-21', 'Robert-Mickael', true, false],
            ['2016-06-21', 'Larissa-Viviane', false, true],
            ['2016-06-21', 'Larissa-Gaelle', false, true],
            ['2016-06-21', 'Larissa-Melina', false, true],
            ['2016-06-21', 'Larissa-Astrid', false, true],
            ['2016-06-21', 'Veron-Matheos', false, true],
            ['2016-06-21', 'Veron-Kevin', false, true],
            ['2016-06-21', 'Batista-Jean', true, true],

            ['2016-06-22', 'Dupontel-Jean-Kevin', true, true],
            ['2016-06-22', 'Dupontel-Kevina', true, true],
            ['2016-06-22', 'Dupontel-Mathilde', true, true],
            ['2016-06-22', 'Robert-Mickael', true, false],
            ['2016-06-22', 'Larissa-Viviane', true, true],
            ['2016-06-22', 'Larissa-Gaelle', false, true],
            ['2016-06-22', 'Larissa-Melina', false, true],
            ['2016-06-22', 'Larissa-Astrid', false, true],
            ['2016-06-22', 'Veron-Matheos', false, true],
            ['2016-06-22', 'Veron-Kevin', false, true],
            ['2016-06-22', 'Batista-Jean', true, true],

        ];
        foreach ($inscs as $insc) {
            $entity = new Garderie();
            $entity->setDate(new \DateTime($insc[0]));
            $entity->setEleve($this->getReference($insc[1]));
            $entity->setEnableMorning($insc[2]);
            $entity->setEnableEvening($insc[3]);
            $manager->persist($entity);
        }


        $manager->flush();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getOrder()
    {
        return 9; // ordre d'appel
    }
}
