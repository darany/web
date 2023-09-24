<?php

namespace App\ApiResource;

use App\Entity\User;
use App\State\PariStateProvider;

use Symfony\Component\Serializer\Annotation\Ignore;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;

#[ApiResource(
    shortName: 'Pari',
    description: 'Pari sur un match',
    provider: PariStateProvider::class,
    paginationEnabled: false,
    operations: [
        new Get(
            uriTemplate: '/rencontres/{id}/pari',
            security: "object.owner == user",
        ),
    ]
)]
class ApiPari
{
    public ?int $id = null;

    public ?float $mise = null;

    public ?string $nomEquipe = null;

    public ?float $gain = null;

    #[Ignore]
    public User $owner;
}
