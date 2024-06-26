<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Crud\Controller;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use SwagIndustries\Melodiia\Crud\Controller\GetAll;
use SwagIndustries\Melodiia\Crud\CrudControllerInterface;
use SwagIndustries\Melodiia\Crud\FilterCollection;
use SwagIndustries\Melodiia\Crud\FilterCollectionFactoryInterface;
use SwagIndustries\Melodiia\Crud\Pagination\PaginationRequest;
use SwagIndustries\Melodiia\Crud\Pagination\PaginationRequestFactoryInterface;
use SwagIndustries\Melodiia\Crud\Persistence\DataStoreInterface;
use SwagIndustries\Melodiia\Response\FormErrorResponse;
use SwagIndustries\Melodiia\Response\OkContent;
use SwagIndustries\Melodiia\Test\TestFixtures\FakeMelodiiaModel;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GetAllTest extends TestCase
{
    use ProphecyTrait;

    /** @var DataStoreInterface|ObjectProphecy */
    private $dataStore;

    /** @var AuthorizationCheckerInterface|ObjectProphecy */
    private $authorizationChecker;

    /** @var Request|ObjectProphecy */
    private $request;

    /** @var ParameterBag|ObjectProphecy */
    private $queries;

    /** @var ParameterBag|ObjectProphecy */
    private $attributes;

    /** @var FilterCollectionFactoryInterface|ObjectProphecy */
    private $filtersFactory;

    /** @var FilterCollection|ObjectProphecy */
    private $filtersCollection;

    /** @var PaginationRequest|ObjectProphecy */
    private $paginationRequest;

    /** @var FormInterface|ObjectProphecy */
    private $form;

    /** @var GetAll */
    private $controller;

    public function setUp(): void
    {
        $this->dataStore = $this->prophesize(DataStoreInterface::class);
        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerInterface::class);
        $this->attributes = $this->prophesize(ParameterBag::class);
        $this->attributes->get(CrudControllerInterface::MODEL_ATTRIBUTE)->willReturn(FakeMelodiiaModel::class);
        $this->attributes->get(CrudControllerInterface::SERIALIZATION_GROUP, [])->willReturn([]);
        $this->attributes->get(CrudControllerInterface::SECURITY_CHECK, null)->willReturn(null);
        $this->attributes->get(CrudControllerInterface::MAX_PER_PAGE_ATTRIBUTE, 30)->willReturn(30);
        $this->attributes->getInt(CrudControllerInterface::MAX_PER_PAGE_ALLOWED, 250)->willReturn(30);
        $this->attributes->getBoolean(CrudControllerInterface::ALLOW_USER_DEFINE_MAX_PAGE, false)->willReturn(false);
        $this->request = new Request();
        $this->request->attributes = $this->attributes->reveal();
        $this->request->query = new InputBag(['page' => 1]);

        $this->form = $this->prophesize(FormInterface::class);
        $this->form->handleRequest(Argument::any())->willReturn($this->form->reveal());
        $this->form->isSubmitted()->willReturn(false);
        $this->filtersCollection = $this->prophesize(FilterCollection::class);
        $this->filtersCollection->getForm()->willReturn($this->form->reveal());
        $this->filtersFactory = $this->prophesize(FilterCollectionFactoryInterface::class);
        $this->filtersFactory->createCollection(Argument::cetera())->willReturn($this->filtersCollection->reveal());

        $this->paginationRequest = $this->prophesize(PaginationRequest::class);
        $this->paginationRequest->getMaxPerPage()->willReturn(250);
        $this->paginationRequest->getPage()->willReturn(1);

        $paginationFactory = $this->prophesize(PaginationRequestFactoryInterface::class);
        $paginationFactory->createPaginationRequest($this->request)->willReturn($this->paginationRequest->reveal());

        $this->controller = new GetAll(
            $this->dataStore->reveal(),
            $this->filtersFactory->reveal(),
            $paginationFactory->reveal(),
            $this->authorizationChecker->reveal()
        );
    }

    public function testItIsIntanceOfMelodiiaController()
    {
        $this->assertInstanceOf(CrudControllerInterface::class, $this->controller);
    }

    public function testItReturnResourceFromDataStoreInsideOkContent()
    {
        $this->dataStore->getPaginated(FakeMelodiiaModel::class, 1, $this->filtersCollection->reveal(), 250)->willReturn(new Pagerfanta(new ArrayAdapter([new \stdClass()])))->shouldBeCalled();

        $res = ($this->controller)($this->request);

        $this->assertInstanceOf(OkContent::class, $res);
        $this->assertTrue($res->isCollection());
        $this->assertIsIterable($res->getContent());
    }

    public function testItStillReturn200OkOnEmptyPaginated()
    {
        $this->dataStore->getPaginated(FakeMelodiiaModel::class, 1, $this->filtersCollection->reveal(), 250)->willReturn(new Pagerfanta(new ArrayAdapter([])))->shouldBeCalled();

        $res = ($this->controller)($this->request);

        $this->assertInstanceOf(OkContent::class, $res);
    }

    public function testItCheckAccessToResourceIfSpecifiedInConfiguration(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->attributes->get(CrudControllerInterface::SECURITY_CHECK, null)->willReturn('view');

        $this->authorizationChecker->isGranted('view', Argument::any())->willReturn(false);

        ($this->controller)($this->request, 'id');
    }

    public function testItReturnsErrorFromFilters(): void
    {
        $this->form->handleRequest($this->request)->shouldBeCalled()->willReturn($this->form->reveal());
        $this->form->isSubmitted()->willReturn(true);
        $this->form->isValid()->willReturn(false);

        $this->dataStore->getPaginated(Argument::cetera())->shouldNotBeCalled();

        $res = ($this->controller)($this->request);

        $this->assertInstanceOf(FormErrorResponse::class, $res);
    }
}
