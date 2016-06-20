<?php

namespace WCS\CantineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use WCS\CantineBundle\Entity\Eleve;
use WCS\CantineBundle\Form\Type\CantineType;



class CantineController extends Controller
{
    /**
     * @param Request $request
     * @param Eleve $eleve
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function inscrireAction(Request $request, Eleve $eleve)
    {
        // récupère les instances de Doctrine et de Calendrier pour l'année en cours
        $em             = $this->getDoctrine()->getManager();
        $calendrier     = $this->get("wcs.calendrierscolaire")->getCalendrierRentreeScolaire();

        // récupère la liste des réservations effectuée
        // cette info est utile uniquement pour la gestion du formulaire dans Twig
        // En effet on doit supprimer toutes les réservations qui ne sont pas retournées
        // par le formulaire, hors les réservations effectuées, mais passées doivent être présentes
        // afin de ne pas être effacées de la base. Par ailleurs, on ne peut tout afficher
        // au risque du coup d'enregistrer des réservations qui n'ont pas été sélectionnées
        $listLunchesSelected = array();
        /**
         * @var \WCS\CantineBundle\Entity\Lunch $lunch
         */
        foreach($eleve->getLunches() as $lunch) {
            $listLunchesSelected[] = $lunch->getDate()->format("Y-m-d");
        }

        //------------------------------------------------------------------------
        // inscriptions possible à partir de la date du jour + un délai de N jours
        // pour les voyages
        //------------------------------------------------------------------------
        $first_day_available = $this->get("wcs.datenow")->getFirstDayAvailable('canteen');

        // la réservation à la cantine ne peut être effectué que 7 jours après
        // la date du jour (soit le 8e jour)
        // on désactive donc le jour actuel et les 7 jours suivants.
        if (!is_null($calendrier)) {
            $calendrier->addDaysPastFrom(
                //new \DateTimeImmutable($calendrier->getDateToday()), new \DateInterval('P8D')
                $this->get("wcs.datenow")->getDate(), new \DateInterval('P8D')
            );
        }

        // créé le formulaire associé à l'élève
        $form = $this->createForm(new CantineType( $em ), $eleve, array(
            'action' => $this->generateUrl('cantine_inscription', array("id"=>$eleve->getId())),
            'method' => 'POST'
        ));

        // traite les infos saisies dans le formulaire
        if ($this->processPostedForm($request, $form, $eleve)) {
            return $this->redirect($this->generateUrl('wcs_cantine_dashboard'));
        }


        // génère la vue avec les paramètres attendus
        return $this->render(
            'WCSCantineBundle:Cantine:inscription.html.twig',
            array(
                "form" => $form->createView(),
                "eleve" => $eleve,
                "calendrier" => $calendrier,
                "first_day_available" => $first_day_available,
                "listLunchesSelected" => $listLunchesSelected
            )
        );

    }

    /**
     * @param Request $request
     * @param Form $form
     * @param Eleve $eleve
     * @return bool
     */
    private function processPostedForm(Request $request, Form $form, Eleve $eleve)
    {
        $em = $this->getDoctrine()->getManager();

        $form->handleRequest($request);
        if ($form->isValid()) {

            // la nouvelle sélection de dates (avec celles déjà présentes en
            // base de données, et les nouvelles à ajouter
            // (cette liste a été mise à jour avec LunchToStringTransformer)
            $lunchesNew = $eleve->getLunches();

            // récupère les réservations actuellement en base de données
            $lunchesOld = $em->getRepository("WCSCantineBundle:Lunch")->findByEleve($eleve);

            // supprime les dates qui ne sont plus sélectionnées
            foreach($lunchesOld as $lunchOld) {
                if (!$lunchesNew->contains($lunchOld)) {
                    $em->remove($lunchOld);
                }
            }

            // met à jour la fiche élève (le régime alimentaire,...)
            $em->persist($eleve);

            $em->flush();
            return true;
        }

        return false;
    }
}
