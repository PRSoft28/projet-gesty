<?php
namespace WCS\EmployeeBundle\Controller\ViewBuilder;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use WCS\CantineBundle\Entity\ActivityBase;
use WCS\CantineBundle\Entity\School;
use WCS\EmployeeBundle\Controller\Mapper\ActivityMapperInterface;
use WCS\EmployeeBundle\Form\Type\ValidateType;


class ValidateViewBuilder extends ViewBuilderAbstract
{
    private $mapper;

    /**
     * ValidateController constructor.
     * @param ActivityMapperInterface $mapper
     */
    public function __construct(ActivityMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @param Request $request
     * @param School $school
     * @param $activity
     * @return array
     */
    public function buildView(Request $request, School $school, $activity)
    {
        $entityClassName = $this->mapper->getEntityClassName();

        $entity = new $entityClassName;
        $form = $this->createForm(new ValidateType(), $entity, array('data_class' => $entityClassName));

        $redirectTo = '';

        if ($this->processForm($request, $form)) {
            $redirectTo = $this->generateUrl(
                'wcs_employee_schools',
                array('activity'=>$activity)
            );
        }

        return array(
            'form_validate' => $form->createView(),
            'redirect_to'   => $redirectTo
        );
    }

    /**
     * @param Request $request
     * @param Form $form
     * @return bool
     */
    private function processForm(Request $request, Form $form)
    {
        $form->handleRequest($request);

        if ($request->getMethod() == 'POST') {
//        if ($form->isSubmitted()) {
            $em = $this->getDoctrineManager();
            $repo = $em->getRepository($this->mapper->getEntityClassName());
            foreach (explode(',', $form["status"]->getData()) as $id)
            {
                if (!empty($id)) {
                    $entity = $repo->find($id);
                    if ($entity) {
                        $entity->setStatus(ActivityBase::STATUS_REGISTERED_AND_PRESENT);
                        $em->persist($entity);
                    }
                }
            }
            $em->flush();
            return true;
        }

        return false;
    }
}
