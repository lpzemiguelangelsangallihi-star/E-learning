<?php

namespace App\Models\Concerns;

/**
 * Corrige los nombres de relación que el pluralizador inglés de Laravel
 * no resuelve bien en español (`leccion`, `material`, `examen`).
 */
trait ResuelveBindingsEspanol
{
    protected function childRouteBindingRelationshipName($childType)
    {
        $relaciones = $this->relacionesDeRuta();

        return $relaciones[$childType]
            ?? parent::childRouteBindingRelationshipName($childType);
    }

    /**
     * @return array<string, string>
     */
    protected function relacionesDeRuta(): array
    {
        return [];
    }
}
