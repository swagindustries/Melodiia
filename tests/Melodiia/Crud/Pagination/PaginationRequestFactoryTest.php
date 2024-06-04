<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Crud\Pagination;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use SwagIndustries\Melodiia\Crud\CrudControllerInterface;
use SwagIndustries\Melodiia\MelodiiaConfigurationInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class PaginationRequestFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var MelodiiaConfigurationInterface|ObjectProphecy */
    private $configuration;

    /** @var Request|ObjectProphecy */
    private $request;

    /** @var ParameterBag */
    private $attributesBag;

    /** @var PaginationRequestFactoryInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->configuration = $this->prophesize(MelodiiaConfigurationInterface::class);
        $this->request = $this->prophesize(Request::class);
        $this->attributesBag = $this->prophesize(ParameterBag::class);

        $this->attributesBag->getInt(CrudControllerInterface::MAX_PER_PAGE_ATTRIBUTE, PaginationRequestFactory::DEFAULT_ITEMS_PER_PAGE)->willReturn(PaginationRequestFactory::DEFAULT_ITEMS_PER_PAGE);
        $this->attributesBag->get(PaginationRequestFactory::DEFAULT_MAX_PER_PAGE_ATTRIBUTE, PaginationRequestFactory::DEFAULT_MAX_PER_PAGE_ATTRIBUTE)->willReturn(PaginationRequestFactory::DEFAULT_MAX_PER_PAGE_ATTRIBUTE);
        $this->attributesBag->getInt(CrudControllerInterface::MAX_PER_PAGE_ALLOWED, 250)->willReturn(30);
        $this->attributesBag->getBoolean(CrudControllerInterface::ALLOW_USER_DEFINE_MAX_PAGE, false)->willReturn(false);

        $this->request->attributes = $this->attributesBag->reveal();
        $this->request->query = new InputBag(['page' => 1]);
        $this->subject = new PaginationRequestFactory($this->configuration->reveal());
    }

    public function testItDoesNotAllowUserToAskForALimitIfNotConfigured()
    {
        $this->attributesBag->getBoolean(CrudControllerInterface::ALLOW_USER_DEFINE_MAX_PAGE, false)->willReturn(false);
        $this->request->query = new InputBag(['page' => 1]);

        $this->configuration->getApiConfigFor($this->request->reveal())->willReturn($this->configApi())->shouldBeCalled();

        $this->assertEquals(new PaginationRequest(1, 30), $this->subject->createPaginationRequest($this->request->reveal()));
    }

    public function testItDoAllowUserToAskForALimitIfConfigured()
    {
        $this->attributesBag->getBoolean(CrudControllerInterface::ALLOW_USER_DEFINE_MAX_PAGE, false)->willReturn(true);
        $this->request->query = new InputBag([PaginationRequestFactory::DEFAULT_MAX_PER_PAGE_ATTRIBUTE => 15]);

        $paginationRequest = $this->subject->createPaginationRequest($this->request->reveal());
        $this->assertEquals(1, $paginationRequest->getPage());
        $this->assertEquals(15, $paginationRequest->getMaxPerPage());
    }

    public function testThatNoUserRequestCanSurpassTheConfiguredLimitPerPage()
    {
        $this->attributesBag->getBoolean(CrudControllerInterface::ALLOW_USER_DEFINE_MAX_PAGE, false)->willReturn(true);
        $this->attributesBag->getInt(CrudControllerInterface::MAX_PER_PAGE_ALLOWED, 250)->willReturn(555);
        $this->request->query = new InputBag([PaginationRequestFactory::DEFAULT_MAX_PER_PAGE_ATTRIBUTE => 666 * 666 * 666]);

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
        $this->request->query = new InputBag(['size' => 12]);

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
