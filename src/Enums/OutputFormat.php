<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Enums;

enum OutputFormat: string
{
    case PDF_BASE64 = 'pdf_base64';
    case ESCPOS = 'escpos';
}
