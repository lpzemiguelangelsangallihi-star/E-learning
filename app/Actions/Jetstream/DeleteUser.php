<?php

namespace App\Actions\Jetstream;

use App\Models\User;
use App\Services\UserDeletionService;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    public function __construct(private UserDeletionService $usuarios) {}

    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        $dependencias = $this->usuarios->academicDependencies($user);

        if ($dependencias !== []) {
            throw ValidationException::withMessages([
                'password' => 'No puedes eliminar tu cuenta mientras seas responsable de '
                    .implode(', ', $dependencias)
                    .'. Reasigna o elimina ese contenido primero.',
            ]);
        }

        $this->usuarios->delete($user);
    }
}
