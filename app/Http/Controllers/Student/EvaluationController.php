<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentEvaluationService;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    public function __construct(
        private StudentEvaluationService $evaluaciones
    ) {
    }

    /**
     * Evaluaciones pendientes.
     */
    public function index()
    {
        $pendientes = $this->evaluaciones->pending(Auth::id());

        return view('student.evaluations.index', compact('pendientes'));
    }

    /**
     * Historial de evaluaciones.
     */
    public function history()
    {
        $historial = $this->evaluaciones->history(Auth::id());

        return view('student.evaluations.history', compact('historial'));
    }
}
