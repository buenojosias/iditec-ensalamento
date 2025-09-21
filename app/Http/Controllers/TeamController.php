<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Team;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;

class TeamController extends Controller
{
    public function index()
    {
        $teamId = 4330;
        $file = $teamId . '.pdf';

        $allModules = Module::all();
        $team = Team::with('module')->findOrFail($teamId);

        $parser = new Parser();
        $pdf = $parser->parseFile(storage_path('turmas/' . $file));

        $ids = [];
        $students = [];

        $teamPrefix = $team->schedule ?? null;
        if ($teamPrefix) {
            $teamPrefix = preg_replace('/-\d+$/', '', $teamPrefix);
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

            // // Captura número da turma no cabeçalho, se ainda não capturado
            // if (!$teamId && preg_match('/Turma\(s\):\s*;(\d{4});/u', $text, $mTurma)) {
            //     $teamId = $mTurma[1];
            // }

            // if (!$teamId) {
            //     // fallback (se por algum motivo não achou na página corrente)
            //     continue;
            // }

            $studentTemplate = '/^(\d{5})\s+([^\n]+?)\s*([AB])\s+' . $teamId . '\s+(\d{3}-)\s*(.*?)(?=(?:\n\d{5}\s)|\z)/usm';

            if (preg_match_all($studentTemplate, $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $id = (int) $m[1];
                    $nameRaw = trim($m[2]);
                    $status = $m[3];
                    $prefix = str_replace('-', '', $m[4]);
                    $moduleBlock = trim($m[5]);

                    // Normaliza o nome para "Título" (opcional)
                    $name = mb_convert_case(mb_strtolower($nameRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

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
                            $isCurrent = trim(string: $mm[1]) === $team->module->name;
                            $modules[] = [
                                'id' => $module->id ?? null,
                                'position' => (int) ($nameRawParts[0] ?? null),
                                'name' => trim(string: $mm[1]),
                                'grade' => (int) $mm[2],
                                'frequency' => (int) $mm[3],
                                'status' => $isCurrent ? 'C' : ((int) $mm[2] >= 70 && (int) $mm[3] >= 75 ? 'A' : 'R'),
                            ];
                        }
                    }

                    if (!$teamPrefix) {
                        $teamPrefix = $prefix; // Captura o prefixo da turma se ainda não foi definido
                    }

                    $students[] = [
                        'id' => $id,
                        'name' => $name,
                        'status' => $status,
                        'team' => $teamId,
                        'schedule' => $teamPrefix,
                        'modules' => $modules,
                    ];
                }
            }
        }

        // Remover ids 72852 e 72896 do array de alunos apenas para teste
        // $students = array_filter($students, fn($student) => $student['id'] !== '72852');
        // $students = array_filter($students, fn($student) => $student['id'] !== '72896');

        // Conferência dos ids
        $totalIds = count($ids);
        $totalExtractedStudents = count($students);

        // Elimina duplicados no array de ids (caso apareçam repetidos por algum motivo)
        $uniqueIds = array_unique($ids);

        // Diferença: ids no texto mas não no array de alunos
        $extractedIds = array_column($students, 'id');
        $missing = array_diff($uniqueIds, $extractedIds);

        // Saída
        // echo "Turma detectada: {$teamId}<br>\n";
        // echo "Prefixo da turma: {$teamPrefix}<br>\n";
        // echo "IDs encontrados no texto (com possíveis repetições): {$totalIds}<br>\n";
        // echo "IDs únicos no texto: " . count($uniqueIds) . "<br>\n";
        // echo "Total de alunos extraídos: {$totalExtractedStudents}<br><hr>\n";

        // if (count($uniqueIds) != $team->students_number) {
        //     echo "⚠️ Atenção: quantidade de IDs únicos (".count($uniqueIds).") não confere com a quantidade esperada de alunos ({$teamModule['students_count']})!\n";
        // } else {
        //     echo "✅ Quantidade de IDs únicos confere com a quantidade esperada de alunos.\n";
        // }

        // if (!empty($missing)) {
        //     echo "⚠️ IDs encontrados no texto mas não extraídos: " . implode(', ', $missing) . "\n";
        // } else {
        //     echo "✅ Todos os IDs encontrados no texto foram extraídos.\n";
        // }

        // Modules grouped
        $groupedModules = [];
        foreach ($students as $student) {
            foreach ($student['modules'] as $module) {
                $groupedModules[$module['name']][] = $student['id'];
            }
        }

        return response()->json([$team, $students]);
    }

    public function student()
    {
        $studentId = 4330;
        $file = 'aluno.pdf';

        $allModules = Module::all();

        $parser = new Parser();
        $pdf = $parser->parseFile(storage_path('turmas/' . $file));
        $text = $pdf->getText();

        // Extrair código e nome do aluno (parar quando houver 2+ espaços — separador para o endereço)
        if (preg_match('/(\d{5})\s+([\p{L}\s\.\-]+?)(?=\s{2,})/u', $text, $matches)) {
            $student = [
                'id' => (int) $matches[1],
                // colapsa múltiplos espaços dentro do nome para 1 e remove espaços nas pontas
                'name' => preg_replace('/\s+/', ' ', trim($matches[2])),
            ];
        } else {
            $student = [
                'id' => null,
                'name' => null,
            ];
        }

        // Extrair linhas da tabela de módulos
        $modules = [];
        $pattern = '/
            (\d+)\s+                                # Código do módulo
            ([A-Z0-9\-\sÇÉÁÍÓÚÂÊÔÃÕ]+?)\s{2,}        # Nome do módulo
            (\d+)\s+                                # Team_id
            (\d{2}\/\d{2}\/\d{4})\s+                 # Data início
            (\d{2}\/\d{2}\/\d{4})\s+                 # Data fim
            (\d+)\s+                                # Nota
            (\d+)                                   # Frequência
        /xu';

        if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $endDate = date('Y-m-d', strtotime(str_replace('/', '-', $m[5])));
                $modules[] = [
                    'id' => $allModules->firstWhere('name', $m[2])?->id,
                    'code' => $m[1],
                    'name' => trim($m[2]),
                    'team_id' => $m[3],
                    'start' => date('Y-m-d', strtotime(str_replace('/', '-', $m[4]))),
                    'end' => $endDate,
                    'grade' => (int) $m[6],
                    'frequency' => (int) $m[7],
                    'situation' => $endDate > date('Y-m-d') && (int) $m[6] === 0 ? 'C' : (((int) $m[6] >= 70 && (int) $m[7] >= 75) ? 'A' : 'R'), // Aprovado/Reprovado
                ];
            }

            $team = array_filter($modules, function ($mod) {
                return $mod['grade'] === 0 && strtotime($mod['end']) >= time();
            });
            $currentTeam = array_first($team);

            $team = Team::query()
                ->where('module_id', $currentTeam['id'] ?? null)
                ->where('period', 'current')
                ->whereId($currentTeam['team_id'] ?? null)
                ->first();

            if ($team) {
                $student['current_team_id'] = $team->id;
                $student['schedule'] = substr($team->schedule, 0, 3);
            } else {
                return response()->json(['error' => 'Turma atual não encontrada'], 404);
            }
        }

        return response()->json([$student, $modules, $team]);
    }

    public function json()
    {
        $teams = Team::with('students.modules')->take(50)->get();
        $teams->makeHidden(['created_at', 'updated_at']);

        return response()->json($teams);
    }

}
