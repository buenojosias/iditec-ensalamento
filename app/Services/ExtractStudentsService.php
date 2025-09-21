<?php

namespace App\Services;

use App\Models\Module;
use App\Models\Team;
use Smalot\PdfParser\Parser;

class ExtractStudentsService
{
    static public function extract($filePath, $team_id)
    {
        $file = storage_path($filePath);

        $parser = new Parser();
        $pdf = $parser->parseFile($file);
        $text = $pdf->getText();

        if (preg_match('/Turma\(s\):\s*;(\d{4});/u', $text, $mTeam)) {
            $teamId = $mTeam[1];
            if ($teamId != $team_id) {
                return [
                    'error' => "O código da turma no arquivo ($teamId) não corresponde ao código selecionado ($team_id).",
                ];
            }
        } else {
            return [
                'error' => "Não foi possível identificar o código da turma no arquivo.",
            ];
        }

        $ids = [];
        $students = [];
        $allModules = Module::all();
        $team = Team::with('module')->findOrFail($teamId);

        $schedule = $team->schedule ?? null;
        if ($schedule) {
            $schedule = preg_replace('/-\d+$/', '', $schedule);
        }

        foreach ($pdf->getPages() as $page) {
            $text = $page->getText();

            // Antes de qualquer filtragem, já captura todos os códigos de 5 dígitos que aparecem
            if (preg_match_all('/\b\d{5}\b/u', $text, $matchesIds)) {
                foreach ($matchesIds[0] as $cod) {
                    $ids[] = $cod;
                }
            }

            // Normaliza espaços e quebras
            $text = preg_replace("/\r\n|\r/", "\n", $text);
            $text = preg_replace("/[ \t]+/", " ", $text);

            // Remove cabeçalho e rodapé (linhas fixas)
            $rows = array_filter(array_map('trim', explode("\n", $text)));
            $rows = array_values(array_filter($rows, function ($row) {
                // Mantém linhas de dados, remove ruídos comuns
                return !preg_match('/^(1 - IDITEC|0529-TURMAS|Pg:|\d+\/\d+|St Turma Atual|Doc de impressora|IDITEC\s*$)/u', $row);
            }));
            $text = implode("\n", $rows);

            $studentTemplate = '/^(\d{5})\s+([^\n]+?)\s*([AB])\s+' . $teamId . '\s+(\d{3}-)\s*(.*?)(?=(?:\n\d{5}\s)|\z)/usm';

            if (preg_match_all($studentTemplate, $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $id = (int) $m[1];
                    $nameRaw = trim($m[2]);
                    $status = $m[3];
                    $prefix = str_replace('-', '', $m[4]);
                    $moduleBlock = trim($m[5]);

                    // Normaliza o nome para "Título" (opcional)
                    // $name = mb_convert_case(mb_strtolower($nameRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                    $name = $nameRaw;

                    // Quebra o bloco de módulos por linhas
                    $rowsMod = preg_split('/\n+/', $moduleBlock);
                    $rowsMod = array_filter($rowsMod, fn($ln) => !str_starts_with(trim($ln), 'NAO CONCLUIDO'));

                    $modules = [];

                    foreach ($rowsMod as $ln) {
                        $ln = trim($ln);
                        if ($ln === '')
                            continue;

                        if (preg_match('/\s*(.+?)\s+(\d{1,3})\s+(\d{1,3})$/u', $ln, $mm)) {
                            $nameRawParts = explode('-', trim(string: $mm[1]));

                            // Find module ID
                            $module = $allModules->firstWhere('name', trim(string: $mm[1]));

                            if (!$module) {
                                $createdModule = self::createModule(trim(string: $mm[1]), (int) (trim(string: $mm[0]) ?? 20) + 200);
                                if ($createdModule) {
                                    $allModules->push((object) $createdModule);
                                    $module = $createdModule;
                                }
                            }

                            $isCurrent = trim(string: $mm[1]) === $team->module->name;
                            $modules[] = [
                                'id' => $module->id ?? null,
                                'position' => (int) ($nameRawParts[0] ?? null),
                                'name' => trim(string: $mm[1]),
                                'grade' => (int) $mm[2],
                                'frequency' => (int) $mm[3],
                                'situation' => $isCurrent ? 'C' : ((int) $mm[2] >= 70 && (int) $mm[3] >= 75 ? 'A' : 'R'),
                            ];
                        }
                    }

                    if (!$schedule) {
                        $schedule = $prefix; // Captura o prefixo da turma se ainda não foi definido
                    }

                    $modules_not_found = array_filter($modules, fn($mod) => $mod['id'] === null);

                    $students[] = [
                        'id' => $id,
                        'name' => $name,
                        'status' => $status,
                        'schedule' => $schedule,
                        'saved' => false,
                        'modules' => $modules,
                        'has_missing_modules' => count($modules_not_found) > 0,
                    ];
                }
            }
        }

        // Remover ids 72852 e 72896 do array de alunos apenas para teste
        // $students = array_filter($students, fn($student) => $student['id'] !== 72852);
        // $students = array_filter($students, fn($student) => $student['id'] !== 72896);

        // Conferência dos ids
        $totalIds = count($ids);
        $totalExtractedStudents = count($students);

        $uniqueIds = array_unique($ids);

        // Diferença: ids no texto mas não no array de alunos
        $extractedIds = array_column($students, 'id');
        $missing = array_diff($uniqueIds, $extractedIds);

        if (count($extractedIds) != $team->students_number) {
            $quantityPassed = false;
        } else {
            $quantityPassed = true;
        }

        unlink($file);

        return [
            'team_id' => (int) $teamId,
            'quantity_passed' => $quantityPassed,
            'total_extracted_students' => $totalExtractedStudents,
            'missing_ids' => array_values($missing),
            'students' => $students,
        ];
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
