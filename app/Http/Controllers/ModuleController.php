<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function json()
    {
        $modules = Module::select('id', 'code', 'position', 'name', 'active')->orderBy('code')->get();

        return response()->json($modules);
    }
}
