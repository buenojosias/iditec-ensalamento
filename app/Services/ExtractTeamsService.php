<?php

namespace App\Services;

use App\Models\Module;
use Smalot\PdfParser\Parser;

class ExtractTeamsService
{
    static public function extract($filePath)
    {
        $modules = Module::all();
        $pendingModules = [];

        $file = storage_path($filePath);

        $parser = new Parser();
        $pdf = $parser->parseFile($file);
        $text = $pdf->getText();

        $teams = [];
        $lines = preg_split("/\r\n|\n|\r/", $text);

        // Armazenar linha quebrada
        $buffer = '';

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, 'Turma') || str_starts_with($line, 'Total')) {
                continue;
            }

            // Se linha começar com código de turma mas parecer incompleta → guardar no buffer
            if ((preg_match('/^4\d{3}\b/', $line) || preg_match('/^5\d{3}\b/', $line)) && !preg_match('/\s\d+$/', $line)) {
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
            if (!preg_match('/^4\d{3}$/', $parts[0]) && !preg_match('/^5\d{3}$/', $parts[0])) {
                continue;
            }

            $classCode = $parts[0];
            $schedule = $parts[1];
            $students = (int) array_pop($parts); // último campo

            // Agora procurar module_code: primeiro número depois do schedule
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
                $createdModule = self::createModule($moduleName, $moduleCode);
                if ($createdModule) {
                    $modules->push((object) $createdModule);
                    $module = $createdModule;
                } else {
                    $pendingModules[] = [
                        'id' => $classCode,
                        'schedule' => $schedule,
                        'module_code' => $moduleCode,
                        'module_name' => $moduleName,
                        'students_number' => $students,
                    ];
                }
            }

            $teams[] = [
                'id' => $classCode,
                'module_id' => $module->id ?? null,
                'schedule' => $schedule,
                'module_name' => $moduleName,
                'students_number' => $students,
                'saved' => false,
            ];
        }

        unlink($file);
        return ['teams' => $teams, 'pending_modules' => $pendingModules];
    }

    public static function createModule($moduleName, $moduleCode)
    {
        $module = Module::create([
            'code' => $moduleCode,
            'position' => (int) substr((string) $moduleCode, 1, 2),
            'name' => $moduleName,
        ]);

        return $module;
    }
}
