<?php

namespace App\ApiResource;

use App\State\CommentaireStateProvider;

use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;

#[ApiResource(
    shortName: 'Commentaire',
    description: 'Commentaire d\'un match',
    provider: CommentaireStateProvider::class,
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/rencontres/{id}/commentaires',
            normalizationContext: ['groups' => 'commentaire:list']
        ),
        new Post(
            security: "is_granted('ROLE_COMMENTATEUR')",
            normalizationContext: ['groups' => 'commentaire:write']
        ),
    ]
)]
class ApiCommentaire
{
    public ?int $id = null;

    #[Groups(['commentaire:write'])]
    public ?int $rencontreId = null;

    #[Groups(['commentaire:write'])]
    public ?int $scoreEquipeA = null;

    #Groups(['commentaire:write'])]
    public ?int $scoreEquipeB = null;

    #[Groups(['commentaire:write'])]
    public ?string $texte = null;

    #[Groups(['commentaire:list'])]
    public ?string $texteDate = null;
}
