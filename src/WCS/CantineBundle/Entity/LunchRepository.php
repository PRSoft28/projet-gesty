<?php

namespace WCS\CantineBundle\Entity;

/**
 * LunchRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LunchRepository extends \Doctrine\ORM\EntityRepository
{
    public function getTodayList($school)
    {
        // Format the date
        $date = new \DateTime('');
        $day = $date->format('Y-m-d');

        // Request pupils to the database from a certain date
        // lien : http://symfony.com/doc/current/book/doctrine.html (src/AppBundle/Entity/ProductRepository.php)
        return $this->getEntityManager()
            ->createQuery(
                'SELECT l FROM WCSCantineBundle:Lunch l JOIN l.eleve e JOIN e.division d WHERE l.date = :day AND d.school = :place ORDER BY e.nom'
            )
            ->setParameter(':day', "%".$day."%")
            ->setParameter(':place', $school)
            ->getResult();
    }

    public function findOneByDateAndEleve($date, $eleve)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT l FROM WCSCantineBundle:Lunch l WHERE l.date = :date AND l.eleve = :eleve'
            )
            ->setParameter(':date', $date)
            ->setParameter(':eleve', $eleve)
            ->getResult();
    }

    public function getNextWeekMealsNumber()
    {
        $day = date('Y-m-d', strtotime('next monday')); //by default strtotime('last monday') returns the current day on mondays
        $result = []; // Initialisation de l'array vide
        for ($i=0;$i<=4;$i++)
        {
            $res = $this->getEntityManager()
                ->createQuery(
                    'SELECT COUNT(d) FROM WCSCantineBundle:Lunch d JOIN d.eleve j WHERE d.date LIKE :day AND j.regimeSansPorc LIKE :pork'
                )
                ->setParameter(':pork', 0)
                ->setParameter(':day', "%".$day."%")
                ->getResult();
            array_push($result, $res[0][1]); // On push le résultat dans l'array
            $day = date('Y-m-d', strtotime($day.' + 1 DAY')); // On ajoute un jour à la date initiale
        }

        return $result;
    }

    public function getNextWeekMealsNumberWithoutPork()
    {
        $day = date('Y-m-d', strtotime('next monday')); //by default strtotime('last monday') returns the current day on mondays
        $result = []; // Initialisation de l'array vide
        for ($i=0;$i<=4;$i++)
        {
            $res = $this->getEntityManager()
                ->createQuery(
                    'SELECT COUNT(d) FROM WCSCantineBundle:Lunch d JOIN d.eleve j WHERE d.date LIKE :day AND j.regimeSansPorc LIKE :pork'
                )
                ->setParameter(':pork', 1)
                ->setParameter(':day', "%".$day."%")
                ->getResult();
            array_push($result, $res[0][1]); // On push le résultat dans l'array
            $day = date('Y-m-d', strtotime($day.' + 1 DAY')); // On ajoute un jour à la date initiale
        }

        return $result;
    }

}
