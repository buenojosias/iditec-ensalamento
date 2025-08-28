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
        $classCode = '4330';
        $file = $classCode . '.pdf';

        $allModules = Module::all();
        $class = Team::where('code', $classCode)->with('module')->first();

        $parser = new Parser();
        $pdf = $parser->parseFile(storage_path('turmas/' . $file));

        $codes = [];
        $students = [];

        $classPrefix = $class->schedule ?? null;
        if ($classPrefix) {
            $classPrefix = preg_replace('/-\d+$/', '', $classPrefix);
        }

        foreach ($pdf->getPages() as $page) {
            $text = $page->getText();

            // Antes de qualquer filtragem, já captura todos os códigos de 5 dígitos que aparecem
            if (preg_match_all('/\b\d{5}\b/u', $text, $matchesCodes)) {
                foreach ($matchesCodes[0] as $cod) {
                    $codes[] = $cod;
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
            // if (!$classCode && preg_match('/Turma\(s\):\s*;(\d{4});/u', $text, $mTurma)) {
            //     $classCode = $mTurma[1];
            // }

            // if (!$classCode) {
            //     // fallback (se por algum motivo não achou na página corrente)
            //     continue;
            // }

            $studentTemplate = '/^(\d{5})\s+([^\n]+?)\s*([AB])\s+' . $classCode . '\s+(\d{3}-)\s*(.*?)(?=(?:\n\d{5}\s)|\z)/usm';

            if (preg_match_all($studentTemplate, $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $code = $m[1];
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
                            $isCurrent = trim(string: $mm[1]) === $class->module->name;
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

                    if (!$classPrefix) {
                        $classPrefix = $prefix; // Captura o prefixo da turma se ainda não foi definido
                    }

                    $students[] = [
                        'code' => $code,
                        'name' => $name,
                        'status' => $status,
                        'class' => $classCode,
                        'prefix' => $classPrefix,
                        'modules' => $modules,
                    ];
                }
            }
        }

        // Remover codigos 72852 e 72896 do array de alunos apenas para teste
        // $students = array_filter($students, fn($student) => $student['code'] !== '72852');
        // $students = array_filter($students, fn($student) => $student['code'] !== '72896');

        // Conferência dos códigos
        $totalCodes = count($codes);
        $totalExtractedStudents = count($students);

        // Elimina duplicados no array de códigos (caso apareçam repetidos por algum motivo)
        $uniqueCodes = array_unique($codes);

        // Diferença: códigos no texto mas não no array de alunos
        $extractedCodes = array_column($students, 'code');
        $missing = array_diff($uniqueCodes, $extractedCodes);

        // Saída
        // echo "Turma detectada: {$classCode}<br>\n";
        // echo "Prefixo da turma: {$classPrefix}<br>\n";
        // echo "Códigos encontrados no texto (com possíveis repetições): {$totalCodes}<br>\n";
        // echo "Códigos únicos no texto: " . count($uniqueCodes) . "<br>\n";
        // echo "Total de alunos extraídos: {$totalExtractedStudents}<br><hr>\n";

        // if (count($uniqueCodes) != $class->students_number) {
        //     echo "⚠️ Atenção: quantidade de códigos únicos (".count($uniqueCodes).") não confere com a quantidade esperada de alunos ({$classModule['students_count']})!\n";
        // } else {
        //     echo "✅ Quantidade de códigos únicos confere com a quantidade esperada de alunos.\n";
        // }

        // if (!empty($missing)) {
        //     echo "⚠️ Códigos encontrados no texto mas não extraídos: " . implode(', ', $missing) . "\n";
        // } else {
        //     echo "✅ Todos os códigos encontrados no texto foram extraídos.\n";
        // }

        // Modules grouped
        $groupedModules = [];
        foreach ($students as $student) {
            foreach ($student['modules'] as $module) {
                $groupedModules[$module['name']][] = $student['code'];
            }
        }

        return response()->json([$class, $students]);
    }
}
