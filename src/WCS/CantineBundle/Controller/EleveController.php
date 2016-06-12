<?php

namespace WCS\CantineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Application\Sonata\UserBundle\Entity\User;
use WCS\CantineBundle\Entity\Eleve;
use WCS\CantineBundle\Form\DataTransformer\DaysOfWeeks;
use WCS\CantineBundle\Form\Handler\EleveHandler;
use WCS\CantineBundle\Form\Model\EleveFormEntity;
use WCS\CantineBundle\Form\Type\EleveEditType;
use WCS\CantineBundle\Form\Type\EleveType;
use WCS\CalendrierBundle\Service\Periode\Periode;

/**
 * Eleve controller.
 *
 */
class EleveController extends Controller
{
    /**
     * Creates a new Eleve entity.
     *
     */
    public function createAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // Enregistre les élèves en BDD
        $entity = new EleveFormEntity();
        $form = $this->createCreateForm($entity);
        
        $handler = new EleveHandler($form, $request, $this->getDoctrine()->getManager(), $this->getUser());
        if ($handler->process($entity)) {
            return $this->redirect($this->generateUrl('wcs_cantine_dashboard'));
        }

        return $this->render('WCSCantineBundle:Eleve:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView()
        ));
    }

    /**
     * Creates a form to create a Eleve entity.
     *
     * @param Eleve $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(EleveFormEntity $eleve)
    {
        $form = $this->createForm(new EleveType(), $eleve, array(
            'action' => $this->generateUrl('eleve_create'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Displays a form to edit an existing Eleve entity.
     *
     */
    public function editAction(Request $request, Eleve $eleve)
    {
        $editForm = $this->createEditForm($eleve);

        return $this->render('WCSCantineBundle:Eleve:edit.html.twig', array(
            'eleve' => $eleve,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Eleve entity.
     *
     * @param Eleve $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Eleve $entity)
    {
        $form = $this->createForm(new EleveEditType(), $entity, array(
            'action' => $this->generateUrl('eleve_update', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Edits an existing Eleve entity.
     *
     */

    public function updateAction(Request $request, Eleve $eleve)
    {
        $editForm = $this->createEditForm($eleve);

        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($eleve);
            $em->flush();

            return $this->redirectToRoute('wcs_cantine_dashboard');
        }


        return $this->render('WCSCantineBundle:Eleve:edit.html.twig', array(
            'entity' => $eleve,
            'edit_form' => $editForm->createView()
        ));
    }
    

    /**
     * Affiche le dashboard
     *
     * @param Request $request  contient les paramètres passés en URL
     *
     * @return Response     renvoit une reponse HTTP après rendu du template dashboard
     */
    public function dashboardAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getEntityManager();

        // liste des enfants
        $children                   = $em->getRepository("WCSCantineBundle:Eleve")->findChildren($user);
        $nbChildrenVoyageInscrits   = $em->getRepository('WCSCantineBundle:Eleve')->findNbEnfantInscritsVoyage($user);

        // periodes TAP/Garderie
        $periodesScolaires = $this->get("wcs.calendrierscolaire")->getPeriodesAnneeRentreeScolaire();
        $periode_tap = $periodesScolaires->getCurrentOrNextPeriodeEnClasse();

        // récupère les days of week sélectionnés
        $daysOfWeek = new DaysOfWeeks(
            new Periode($this->get("wcs.datenow")->getDate(), $periode_tap->getFin()),
            $this->get('wcs.daysoff')
        );

        // récupère les taps et les garderies de chaque enfants
        /**
         * @var Eleve $child
         */
        $children_activities = array();
        foreach ($children as $child) {
            $children_activities[$child->getId()]["taps"]         = $daysOfWeek->getTapSelectionToArray($child->getTaps());
            $children_activities[$child->getId()]["garderies"]    = $daysOfWeek->getGarderieSelectionToArray($child->getGarderies());
        }

        return $this->render('WCSCantineBundle:Eleve:dashboard.html.twig', array(
            'user'                      => $user,
            'children'                  => $children,
            'files'                     => $this->getFiles($user, $nbChildrenVoyageInscrits),
            'periode_tap'               => $periode_tap,
            'children_activities'       => $children_activities,
            'nbChildrenVoyageInscrits'  => $nbChildrenVoyageInscrits
        ));
    }

    /**
     * @param $user
     * @param $nbChildrenVoyageInscrits
     * @return array
     */
    private function getFiles(User $user, $nbChildrenVoyageInscrits)
    {
        $filesArray = array();
        $filesArray[User::TYPE_PRESTATIONS] = array(
            'libelle_justif' => 'Justificatif de CAF',
            'exists' => is_file($user->getAbsolutePathPrestations())
        );

        $filesArray[User::TYPE_DOMICILE]    = array(
            'libelle_justif' => 'Justificatif de domicile',
            'exists' => is_file($user->getAbsolutePathDomicile())
        );

        $filesArray[User::TYPE_SALAIRE1]    = array(
            'libelle_justif' => 'Justificatif de salaire 1',
            'exists' => is_file($user->getAbsolutePathSalaire1())
        );

        $filesArray[User::TYPE_SALAIRE2]    = array(
            'libelle_justif' => 'Justificatif de salaire 2',
            'exists' => is_file($user->getAbsolutePathSalaire2())
        );

        $filesArray[User::TYPE_SALAIRE3]    = array(
            'libelle_justif' => 'Justificatif de salaire 3',
            'exists' => is_file($user->getAbsolutePathSalaire3())
        );

        if ($nbChildrenVoyageInscrits) {
            $filesArray[User::TYPE_IMPOTS]    = array(
                'libelle_justif' => "Justificatif avis d'imposition",
                'exists' => is_file($user->getAbsolutePathImpot())
            );
        }

        return $filesArray;
    }
}
