<?php
// src/Security/Voter/HikeVoter.php
namespace App\Security\Voter;

use App\Entity\Hike;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class HikeVoter extends Voter
{
    public const EDIT = 'HIKE_EDIT';
    public const DELETE = 'HIKE_DELETE';

    public function __construct(private Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        // On ne s'intéresse qu'aux entités Hike et aux actions définies ci-dessus
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof Hike;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Hike $hike */
        $hike = $subject;

        // Exemple : un admin peut tout faire
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($hike, $user),
            self::DELETE => $this->canDelete($hike, $user),
            default => false,
        };
    }

    private function canEdit(Hike $hike, User $user): bool
    {
        // seul le créateur peut modifier
        return $hike->getCreator() === $user;
    }

    private function canDelete(Hike $hike, User $user): bool
    {
        // même règle ici, mais tu peux la rendre différente si besoin
        return $hike->getCreator() === $user;
    }
}
