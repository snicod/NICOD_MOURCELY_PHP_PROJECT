<?php

namespace App\Security;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class EventVoter extends Voter
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // seulement voter pour ces actions sur l'objet Event
        return in_array($attribute, ['edit', 'delete'])
            && $subject instanceof \App\Entity\Event;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            // l'utilisateur doit être connecté ; sinon, accès refusé
            return false;
        }

        // vous pouvez également vérifier les rôles administrateurs
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // vous pouvez implémenter une logique complexe ici
        /** @var Event $event */
        $event = $subject;

        switch ($attribute) {
            case 'edit':
            case 'delete':
                return $this->canEditOrDelete($event, $user);
        }

        return false;
    }

    private function canEditOrDelete(Event $event, User $user): bool
    {
        // l'utilisateur peut modifier ou supprimer l'événement s'il en est le créateur
        return $user === $event->getCreator();
    }
}
