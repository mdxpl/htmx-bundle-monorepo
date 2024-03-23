<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

use Mdxpl\HtmxBundle\Exception\ReservedViewParamCannotBeOverriddenException;
use Mdxpl\HtmxBundle\Response\View\View;

trait HasDefaultViewDataTrait
{
    /**
     * The build-in view params that are set automatically and cannot be overridden.
     */
    private const BUILD_IN_VIEW_PARAMS = [
        View::RESULT_VIEW_PARAM_NAME,
        View::IS_HTMX_REQUEST_VIEW_PARAM_NAME,
    ];

    private array $defaultViewData = [];

    private function setDefaultViewData(Result $result, bool $isHtmxRequest): void
    {
        $this->defaultViewData[View::RESULT_VIEW_PARAM_NAME] = $result;
        $this->defaultViewData[View::IS_HTMX_REQUEST_VIEW_PARAM_NAME] = $isHtmxRequest;
    }

    private function assertNotOverridesReservedViewParams(mixed $viewData): void
    {
        foreach (self::BUILD_IN_VIEW_PARAMS as $reservedViewParam) {
            if (array_key_exists($reservedViewParam, $viewData)) {
                throw ReservedViewParamCannotBeOverriddenException::withViewParamName($reservedViewParam);
            }
        }
    }
}
