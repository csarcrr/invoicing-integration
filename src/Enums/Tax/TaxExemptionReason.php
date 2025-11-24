<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums\Tax;

enum TaxExemptionReason: string
{
    case M01 = 'M01';
    case M02 = 'M02';
    case M03 = 'M03';
    case M04 = 'M04';
    case M05 = 'M05';
    case M06 = 'M06';
    case M07 = 'M07';
    case M08 = 'M08';
    case M09 = 'M09';
    case M10 = 'M10';
    case M11 = 'M11';
    case M12 = 'M12';
    case M13 = 'M13';
    case M14 = 'M14';
    case M15 = 'M15';
    case M16 = 'M16';
    case M19 = 'M19';
    case M20 = 'M20';
    case M21 = 'M21';
    case M25 = 'M25';
    case M26 = 'M26';
    case M30 = 'M30';
    case M31 = 'M31';
    case M32 = 'M32';
    case M33 = 'M33';
    case M34 = 'M34';
    case M40 = 'M40';
    case M41 = 'M41';
    case M42 = 'M42';
    case M43 = 'M43';
    case M44 = 'M44';
    case M45 = 'M45';
    case M46 = 'M46';
    case M99 = 'M99';

    public function laws(): array
    {
        return match ($this) {
            self::M01 => [
                'Artigo 16.º, n.º 6, alínea a) do CIVA',
                'Artigo 16.º, n.º 6, alínea b) do CIVA',
                'Artigo 16.º, n.º 6, alínea c) do CIVA',
                'Artigo 16.º, n.º 6, alínea d) do CIVA',
            ],
            self::M02 => [
                'Artigo 6.º do Decreto‐Lei n.º 198/90, de 19 de junho',
            ],
            self::M03 => ['Não utilizar após 2022'],
            self::M04 => ['Artigo 13.º do CIVA'],
            self::M05 => ['Artigo 14.º do CIVA'],
            self::M06 => ['Artigo 15.º do CIVA'],
            self::M07 => ['Artigo 9.º do CIVA'],
            self::M08 => ['Utilizar alternativa entre M30 e M43'],
            self::M09 => [
                'Artigo 60.º CIVA',
                'Artigo 72.º n.º 4 do CIVA',
            ],
            self::M10 => [
                'Artigo 53.º n.º 1 do CIVA',
                'Artigo 57.º do CIVA',
            ],
            self::M11 => ['Decreto-Lei n.º 346/85, de 23 de agosto'],
            self::M12 => ['Decreto-Lei n.º 221/85, de 3 de julho'],
            self::M13 => ['Decreto-Lei n.º 199/96, de 18 de outubro'],
            self::M14 => ['Decreto-Lei n.º 199/96, de 18 de outubro'],
            self::M15 => ['Decreto-Lei n.º 199/96, de 18 de outubro'],
            self::M16 => ['Artigo 14.º do RITI'],
            self::M19 => ['Isenções temporárias em diploma próprio'],
            self::M20 => ['Artigo 59.º-D n.º2 do CIVA'],
            self::M21 => ['Artigo 72.º n.º 4 do CIVA'],
            self::M25 => ['Artigo 38.º n.º 1 alínea a)'],
            self::M26 => ['Lei n.º 17/2023'],
            self::M30 => ['Artigo 2.º n.º 1 alínea i) do CIVA'],
            self::M31 => ['Artigo 2.º n.º 1 alínea j) do CIVA'],
            self::M32 => ['Artigo 2.º n.º 1 alínea l) do CIVA'],
            self::M33 => ['Artigo 2.º n.º 1 alínea m) do CIVA'],
            self::M34 => ['Artigo 2.º n.º 1 alínea n) do CIVA'],
            self::M40 => ['Artigo 6.º n.º 6 alínea a) do CIVA, a contrário'],
            self::M41 => ['Artigo 8.º n.º 3 do RITI'],
            self::M42 => ['Decreto-Lei n.º 21/2007, de 29 de janeiro'],
            self::M43 => ['Decreto-Lei n.º 362/99, de 16 de setembro'],
            self::M44 => ['Artigo 6.° do CIVA'],
            self::M45 => ['Artigo 58.°-A do CIVA'],
            self::M46 => ['Decreto-lei n.° 19/2017, de 14 de fevereiro'],
            self::M99 => [
                'Artigo 2.º, n.º 2 do CIVA',
                'Artigo 3.º, n.º 4 do CIVA',
                'Artigo 3.º, n.º 6 do CIVA',
                'Artigo 3.º, n.º 7 do CIVA',
                'Artigo 4.º, n.º 5 do CIVA',
            ],
        };
    }
}
