<?php
/**
 * Created by PhpStorm.
 * User: corentinboutillier
 * Date: 25/11/2018
 * Time: 23:31
 */

namespace App\EventListener;

use Doctrine\Common\EventSubscriber;
//les adresses de nos utilisateur sont stockés dans une entité "Contact"
use App\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Container;

class DoctrineEvent implements EventSubscriber {

    private $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function getSubscribedEvents() {
        return array('prePersist');
    }

    public function prePersist(LifecycleEventArgs $args) {
        if (method_exists($args->getObject(),'setUuid')) {
            $args->getEntity()->setUuid($this->container->get('app.uuidhelper')->uuidGeneration());
        }
    }
}