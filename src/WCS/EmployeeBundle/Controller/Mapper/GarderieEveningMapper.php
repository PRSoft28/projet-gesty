<?php
namespace WCS\EmployeeBundle\Controller\Mapper;


use WCS\CantineBundle\Entity\ActivityBase;

class GarderieEveningMapper implements ActivityMapperInterface
{
    /**
     * @return string the title displayed in the today list view
     */
    public function getTodayListTitle()
    {
        return "Garderie soir - Feuille de présence";
    }

    /**
     * @return string fully qualified entity class name
     */
    public function getEntityClassName()
    {
        return 'WCS\CantineBundle\Entity\Garderie';
    }

    /**
     * @inheritdoc
     */
    public function preUpdateEntity(ActivityBase $entity)
    {
        /**
         * @var \WCS\CantineBundle\Entity\Garderie $entity
         */
        $entity->setEnableEvening(true);
    }

    /**
     * @inheritdoc
     */
    public function getDayListAdditionalOptions()
    {
        return array('is_morning'=>false);
    }

    /**
     * @inheritdoc
     */
    public function getActivityType()
    {
        return \WCS\CantineBundle\Service\GestyScheduler\ActivityType::GARDERIE_EVENING;
    }

}
