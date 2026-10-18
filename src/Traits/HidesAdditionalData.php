<?php

declare(strict_types=1);

namespace CsarCrr\InvoicingIntegration\Traits;

use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;

trait HidesAdditionalData
{
    /**
     * @return array<string, mixed>
     */
    public function transform(
        null|TransformationContextFactory|TransformationContext $transformationContext = null,
    ): array {
        $unsupportedProperties = $this->_additional;
        $this->_additional = [];

        try {
            return parent::transform($transformationContext);
        } finally {
            $this->_additional = $unsupportedProperties;
        }
    }
}
