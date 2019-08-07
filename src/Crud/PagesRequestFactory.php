<?php
declare(strict_types=1);

namespace Biig\Melodiia\Crud;

use Symfony\Component\HttpFoundation\Request;

class PagesRequestFactory
{
    /**
     * @param Request $request
     * @return PagesRequest
     */
    public static function build(Request $request)
    {
        $page       = $request->query->getInt('page', 1);
        $maxPerPage = $request->attributes->get(CrudControllerInterface::MAX_PER_PAGE_ATTRIBUTE, 30);
        if (true === $request->attributes->getBoolean(CrudControllerInterface::ALLOW_USER_DEFINE_MAX_PAGE, false)) {
            $maxPerPageQueryAttribute = $request->attributes->get(CrudControllerInterface::MAX_PER_PAGE_QUERY_ATTRIBUTE, 'max_per_page');
            if (0 !== $userMax = $request->query->getInt($maxPerPageQueryAttribute, 0)) {
                $maxPerPage = $userMax;
            }
        }

        if ($maxPerPage > $maxAllowed = $request->attributes->getInt(CrudControllerInterface::MAX_PER_PAGE_ALLOWED, 250)) {
            $maxPerPage = $maxAllowed;
        }

        return new PagesRequest($page, $maxPerPage);
    }
}
