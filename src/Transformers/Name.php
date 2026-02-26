<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Transformers;

use Illuminate\Support\Str;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

class Name implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        $value = Str::of($value)->ascii()->squish()->toString();

        if (empty($value)) {
            return null;
        }

        return $value;
    }
}
