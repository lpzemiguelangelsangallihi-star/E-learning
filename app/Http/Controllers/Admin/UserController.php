<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\User;
use App\Services\UserDeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(private UserDeletionService $usuarios) {}

    public function index(Request $request)
    {
        $search =
            trim(
                $request
                    ->string('search')
                    ->toString()
            );

        $rolId =
            $request->integer('rol');

        $estado =
            $request
                ->string('estado')
                ->toString();

        $usuarios = User::query()

            ->with('rol')

            ->when(
                $search !== '',
                function ($query) use ($search) {

                    $query->where(
                        function ($query) use ($search) {

                            $query
                                ->where(
                                    'nombre',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'apellido_paterno',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'apellido_materno',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                );

                        }
                    );

                }
            )

            ->when(
                $rolId,
                fn ($query) => $query->where(
                    'rol_id',
                    $rolId
                )
            )

            ->when(
                in_array(
                    $estado,
                    [
                        'activo',
                        'inactivo',
                    ],
                    true
                ),
                fn ($query) => $query->where(
                    'estado',
                    $estado
                )
            )

            ->orderByDesc('creado_en')

            ->paginate(10)

            ->withQueryString();

        $roles = Rol::query()
            ->orderBy('nombre')
            ->get();

        return view(
            'admin.users.index',
            compact(
                'usuarios',
                'roles',
                'search',
                'rolId',
                'estado',
            )
        );
    }

    public function create()
    {
        $roles = Rol::query()
            ->orderBy('nombre')
            ->get();

        return view(
            'admin.users.create',
            compact('roles')
        );
    }

    public function store(Request $request)
    {
        $validated =
            $request->validate([

                'rol_id' => [
                    'required',
                    'exists:roles,id',
                ],

                'nombre' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'apellido_paterno' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'apellido_materno' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:150',
                    'unique:users,email',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],

                'estado' => [
                    'required',
                    Rule::in([
                        'activo',
                        'inactivo',
                    ]),
                ],

            ]);

        User::create([

            'rol_id' => $validated['rol_id'],

            'nombre' => trim($validated['nombre']),

            'apellido_paterno' => trim(
                $validated[
                    'apellido_paterno'
                ]
            ),

            'apellido_materno' => trim(
                $validated[
                    'apellido_materno'
                ]
            ),

            'email' => mb_strtolower(
                trim(
                    $validated['email']
                )
            ),

            'password' => Hash::make(
                $validated['password']
            ),

            'estado' => $validated['estado'],

            'email_verified_at' => now(),

        ]);

        return redirect()

            ->route(
                'admin.users.index'
            )

            ->with(
                'success',
                'Usuario creado correctamente.'
            );
    }

    public function edit(User $user)
    {
        $roles = Rol::query()
            ->orderBy('nombre')
            ->get();

        return view(
            'admin.users.edit',
            compact(
                'user',
                'roles'
            )
        );
    }

    public function update(
        Request $request,
        User $user
    ) {

        $validated =
            $request->validate([

                'rol_id' => [
                    'required',
                    'exists:roles,id',
                ],

                'nombre' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'apellido_paterno' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'apellido_materno' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:150',

                    Rule::unique(
                        'users',
                        'email'
                    )->ignore(
                        $user->id
                    ),
                ],

                'password' => [
                    'nullable',
                    'string',
                    'min:8',
                    'confirmed',
                ],

                'estado' => [
                    'required',
                    Rule::in([
                        'activo',
                        'inactivo',
                    ]),
                ],

            ]);

        $data = [

            'rol_id' => $validated['rol_id'],

            'nombre' => trim(
                $validated['nombre']
            ),

            'apellido_paterno' => trim(
                $validated[
                    'apellido_paterno'
                ]
            ),

            'apellido_materno' => trim(
                $validated[
                    'apellido_materno'
                ]
            ),

            'email' => mb_strtolower(
                trim(
                    $validated['email']
                )
            ),

            'estado' => $validated['estado'],
        ];

        if (! empty(
            $validated['password']
        )) {

            $data['password'] =
                Hash::make(
                    $validated['password']
                );

        }

        $user->update($data);

        return redirect()

            ->route(
                'admin.users.index'
            )

            ->with(
                'success',
                'Usuario actualizado correctamente.'
            );
    }

    public function toggleStatus(
        User $user
    ) {

        if ($user->id === auth()->id()) {

            return back()->with(
                'error',
                'No puedes desactivar tu propia cuenta.'
            );

        }

        $nuevoEstado = $user->estado === 'activo'
            ? 'inactivo'
            : 'activo';

        $user->update([
            'estado' => $nuevoEstado,
        ]);

        // Cierra de inmediato las sesiones abiertas del usuario desactivado.
        if ($nuevoEstado === 'inactivo' && Schema::hasTable('sessions')) {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();
        }

        return back()->with(
            'success',
            'Estado actualizado correctamente.'
        );
    }

    public function destroy(
        User $user
    ) {

        if ($user->id === auth()->id()) {

            return back()->with(
                'error',
                'No puedes eliminar tu propia cuenta.'
            );

        }

        $dependencias = $this->usuarios->academicDependencies($user);

        if ($dependencias !== []) {
            return back()->with(
                'error',
                'No se puede eliminar al usuario porque todavía es responsable de '
                    .implode(', ', $dependencias)
                    .'. Reasigna o elimina ese contenido primero.'
            );
        }

        $this->usuarios->delete($user);

        return redirect()

            ->route(
                'admin.users.index'
            )

            ->with(
                'success',
                'Usuario eliminado correctamente.'
            );
    }
}
