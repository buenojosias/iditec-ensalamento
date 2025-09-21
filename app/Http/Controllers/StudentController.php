<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function json()
    {
        $students = Student::with(['currentTeam', 'modules'])->take(5)->get();
        $students->makeHidden(['created_at', 'updated_at']);

        return response()->json($students);
    }
}
