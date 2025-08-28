<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [ 'code' => 201, 'position' => 1, 'name' => '01-TECNOLOGIA DA INFOR'],
            [ 'code' => 202, 'position' => 2, 'name' => '02-ARTE DIGITAL'],
            [ 'code' => 203, 'position' => 3, 'name' => '03-ADMINISTRATIVO'],
            [ 'code' => 204, 'position' => 4, 'name' => '04-EDITOR DE TEXTO'],
            [ 'code' => 205, 'position' => 5, 'name' => '05-PLANILHAS'],
            [ 'code' => 206, 'position' => 6, 'name' => '06-EDIÇÃO FOTOGRAFICA'],
            [ 'code' => 207, 'position' => 7, 'name' => '07-MARKETING DIGITAL'],
            [ 'code' => 208, 'position' => 8, 'name' => '08-DESIGNER GRAFICO'],
            [ 'code' => 210, 'position' => 10, 'name' => '10-GESTÃO DE PESSOAS'],
            [ 'code' => 211, 'position' => 11, 'name' => '11-CONTABIL'],
            [ 'code' => 214, 'position' => 14, 'name' => 'IA - INTELIGENCIA ARTIFIC'],
            [ 'code' => 215, 'position' => 15, 'name' => 'POWERPOINT'],
        ];

        Module::insert($modules);
    }
}
