<?php

namespace Biig\Melodiia\Crud\Pagination;

use Biig\Melodiia\Crud\CrudControllerInterface;
use Biig\Melodiia\MelodiiaConfigurationInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class PaginationRequestFactoryTest extends TestCase
{
    /** @var MelodiiaConfigurationInterface|ObjectProphecy */
    private $configuration;

    /** @var Request|ObjectProphecy */
    private $request;

    /** @var ParameterBag */
    private $queryBag;

    /** @var ParameterBag */
    private $attributesBag;

    /** @var PaginationRequestFactoryInterface */
    private $subject;

    protected function setUp()
    {
        $this->configuration = $this->prophesize(MelodiiaConfigurationInterface::class);
        $this->request = $this->prophesize(Request::class);
        $this->queryBag = $this->prophesize(ParameterBag::class);
        $this->attributesBag = $this->prophesize(ParameterBag::class);

        $this->attributesBag->getInt(CrudControllerInterface::MAX_PER_PAGE_ATTRIBUTE, PaginationRequestFactory::DEFAULT_ITEMS_PER_PAGE)->willReturn(PaginationRequestFactory::DEFAULT_ITEMS_PER_PAGE);
        $this->attributesBag->get(PaginationRequestFactory::DEFAULT_MAX_PER_PAGE_ATTRIBUTE, PaginationRequestFactory::DEFAULT_MAX_PER_PAGE_ATTRIBUTE)->willReturn(PaginationRequestFactory::DEFAULT_MAX_PER_PAGE_ATTRIBUTE);
        $this->attributesBag->getInt(CrudControllerInterface::MAX_PER_PAGE_ALLOWED, 250)->willReturn(30);
        $this->attributesBag->getBoolean(CrudControllerInterface::ALLOW_USER_DEFINE_MAX_PAGE, false)->willReturn(false);
        $this->queryBag->getInt('page', Argument::any())->willReturn(1);

        $this->request->attributes = $this->attributesBag->reveal();
        $this->request->query = $this->queryBag->reveal();
        $this->subject = new PaginationRequestFactory($this->configuration->reveal());
    }

    public function testItDoesNotAllowUserToAskForALimitIfNotConfigured()
    {
        $this->attributesBag->getBoolean(CrudControllerInterface::ALLOW_USER_DEFINE_MAX_PAGE, false)->willReturn(false);
        $this->queryBag->getInt('page', PaginationRequestFactory::DEFAULT_PAGE)->willReturn(PaginationRequestFactory::DEFAULT_PAGE);
        $this->queryBag->getInt('max_per_page', 0)->shouldNotBeCalled();

        $this->configuration->getApiConfigFor($this->request->reveal())->willReturn($this->configApi())->shouldBeCalled();

        $this->assertEquals(new PaginationRequest(1, 30), $this->subject->createPaginationRequest($this->request->reveal()));
    }

    public function testItDoAllowUserToAskForALimitIfConfigured()
    {
        $this->attributesBag->getBoolean(CrudControllerInterface::ALLOW_USER_DEFINE_MAX_PAGE, false)->willReturn(true);
        $this->queryBag->getInt(PaginationRequestFactory::DEFAULT_MAX_PER_PAGE_ATTRIBUTE, 0)->willReturn(15)->shouldBeCalled();

        $paginationRequest = $this->subject->createPaginationRequest($this->request->reveal());
        $this->assertEquals(1, $paginationRequest->getPage());
        $this->assertEquals(15, $paginationRequest->getMaxPerPage());
    }

    public function testThatNoUserRequestCanSurpassTheConfiguredLimitPerPage()
    {
        $this->attributesBag->getBoolean(CrudControllerInterface::ALLOW_USER_DEFINE_MAX_PAGE, false)->willReturn(true);
        $this->attributesBag->getInt(CrudControllerInterface::MAX_PER_PAGE_ALLOWED, 250)->willReturn(555);
        $this->queryBag->getInt(PaginationRequestFactory::DEFAULT_MAX_PER_PAGE_ATTRIBUTE, 0)->willReturn(666 * 666 * 666)->shouldBeCalled();

        $paginationRequest = $this->subject->createPaginationRequest($this->request->reveal());
        $this->assertEquals(1, $paginationRequest->getPage());
        $this->assertEquals(555, $paginationRequest->getMaxPerPage());
    }

    public function testItRetrieveLimitPerPageAtSpecifiedLocationInAttributes()
    {
        $config = $this->configApi();
        $config['pagination']['max_per_page_attribute'] = 'size';
        $this->configuration->getApiConfigFor($this->request->reveal())->willReturn($config);
        $this->attributesBag->getBoolean(CrudControllerInterface::ALLOW_USER_DEFINE_MAX_PAGE, false)->willReturn(true);
        $this->attributesBag->getInt(CrudControllerInterface::MAX_PER_PAGE_ALLOWED, 250)->willReturn(555);
        $this->queryBag->getInt('size', 0)->willReturn(12)->shouldBeCalled();

        $paginationRequest = $this->subject->createPaginationRequest($this->request->reveal());
        $this->assertEquals(1, $paginationRequest->getPage());
        $this->assertEquals(12, $paginationRequest->getMaxPerPage());
    }

    private function configApi()
    {
        return [
            'title' => 'Biig API',
            'name' => 'main',
            'description' => 'This is huge.',
            'version' => '1.0.0',
            'base_path' => '/api/v1',
            'paths' => [],
            'enable_doc' => true,
            'doc_factory' => null,
        ];
    }
}
