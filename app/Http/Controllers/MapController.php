<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Team;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;

class MapController extends Controller
{
    public function index()
    {
        $modules = $this->modules();
        $pendingModules = [];

        $file = storage_path('turmas/mapa.pdf');

        $parser = new Parser();
        $pdf = $parser->parseFile($file);
        $text = $pdf->getText();

        $teams = [];
        $lines = preg_split("/\r\n|\n|\r/", $text);

        $buffer = ''; // Para armazenar linha quebrada

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, 'Turma') || str_starts_with($line, 'Total')) {
                continue;
            }

            // Se linha começar com código de turma mas parecer incompleta → guardar no buffer
            if (preg_match('/^4\d{3}\b/', $line) && !preg_match('/\s\d+$/', $line)) {
                $buffer = $line;
                continue;
            }

            // Se buffer está preenchido → juntar
            if ($buffer !== '') {
                $line = $buffer . ' ' . $line;
                $buffer = '';
            }

            $parts = preg_split('/\s+/', $line);

            if (count($parts) < 4) {
                continue;
            }

            // apenas turmas que começam com 4xxx
            if (!preg_match('/^4\d{3}$/', $parts[0])) {
                continue;
            }

            $classCode = $parts[0];
            $schedule = $parts[1];
            $students = (int) array_pop($parts); // último campo

            // agora procurar module_code: primeiro número depois do schedule
            $moduleCode = null;
            $moduleNameParts = [];
            for ($i = 2; $i < count($parts); $i++) {
                if (preg_match('/^\d+$/', $parts[$i]) && $moduleCode === null) {
                    $moduleCode = $parts[$i];
                } elseif ($moduleCode !== null) {
                    $moduleNameParts[] = $parts[$i];
                }
            }
            $moduleName = implode(' ', $moduleNameParts);

            $module = $modules->firstWhere('name', $moduleName);
            if (!$module) {
                $pendingModules[] = [
                    'module_code' => $moduleCode,
                    'module_name' => $moduleName,
                ];
            }

            $teams[] = [
                'code' => $classCode,
                'schedule' => $schedule,
                'module_code' => $moduleCode,
                'module_name' => $moduleName,
                'module_id' => $module->id ?? null,
                'students_number' => $students,
            ];
        }

        $pendingModules = array_values(array_unique($pendingModules, SORT_REGULAR));

        usort($teams, fn($a, $b) => $a['module_name'] <=> $b['module_name']);

        // Team::insert($teams);

        return response()->json([$pendingModules, $teams]);
    }

    public function modules()
    {
        $modules = Module::all();

        return $modules;
    }
}
