<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * A trait for id and uuid properties in every entities.
 * You can also use MappedSuperclass but not recommended.
 *
 * @see     https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/inheritance-mapping.html
 * @see     https://medium.com/@galopintitouan/using-traits-to-compose-your-doctrine-entities-9b516335119b
 *
 * @author  Gaëtan Rolé-Dubruille <gaetan.role@gmail.com>
 */
trait EntityIdTrait
{

    /**
     * The internal primary identity key.
     *
     * @var UuidInterface
     *
     */
    #[ORM\Column(type: "uuid", unique: true)]
    protected $uuid;

    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
}
