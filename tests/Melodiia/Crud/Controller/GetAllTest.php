<?php

namespace SwagIndustries\Melodiia\Test\Crud\Controller;

use SwagIndustries\Melodiia\Bridge\Symfony\Response\FormErrorResponse;
use SwagIndustries\Melodiia\Crud\Controller\GetAll;
use SwagIndustries\Melodiia\Crud\CrudControllerInterface;
use SwagIndustries\Melodiia\Crud\FilterCollection;
use SwagIndustries\Melodiia\Crud\FilterCollectionFactoryInterface;
use SwagIndustries\Melodiia\Crud\Pagination\PaginationRequest;
use SwagIndustries\Melodiia\Crud\Pagination\PaginationRequestFactoryInterface;
use SwagIndustries\Melodiia\Crud\Persistence\DataStoreInterface;
use SwagIndustries\Melodiia\Response\OkContent;
use SwagIndustries\Melodiia\Test\TestFixtures\FakeMelodiiaModel;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GetAllTest extends TestCase
{
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

    /** @var GetAll */
    private $controller;

    public function setUp()
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
        $this->queries = $this->prophesize(ParameterBag::class);
        $this->queries->getInt('page', Argument::cetera())->willReturn(1);
        $this->request = $this->prophesize(Request::class);
        $this->request->attributes = $this->attributes->reveal();
        $this->request->query = $this->queries->reveal();

        $this->filtersCollection = $this->prophesize(FilterCollection::class);
        $this->filtersCollection->getForm()->willReturn($this->prophesize(FormInterface::class)->reveal());
        $this->filtersFactory = $this->prophesize(FilterCollectionFactoryInterface::class);
        $this->filtersFactory->createCollection(Argument::cetera())->willReturn($this->filtersCollection->reveal());

        $this->paginationRequest = $this->prophesize(PaginationRequest::class);
        $this->paginationRequest->getMaxPerPage()->willReturn(250);
        $this->paginationRequest->getPage()->willReturn(1);

        $paginationFactory = $this->prophesize(PaginationRequestFactoryInterface::class);
        $paginationFactory->createPaginationRequest($this->request->reveal())->willReturn($this->paginationRequest->reveal());

        $this->controller = new GetAll(
            $this->dataStore->reveal(),
            $this->authorizationChecker->reveal(),
            $this->filtersFactory->reveal(),
            $paginationFactory->reveal()
        );
    }

    public function testItIsIntanceOfMelodiiaController()
    {
        $this->assertInstanceOf(CrudControllerInterface::class, $this->controller);
    }

    public function testItReturnResourceFromDataStoreInsideOkContent()
    {
        $this->dataStore->getPaginated(FakeMelodiiaModel::class, 1, $this->filtersCollection->reveal(), 250)->willReturn(new Pagerfanta(new ArrayAdapter([new \stdClass()])))->shouldBeCalled();

        $res = ($this->controller)($this->request->reveal());

        $this->assertInstanceOf(OkContent::class, $res);
        $this->assertTrue($res->isCollection());
        $this->assertIsIterable($res->getContent());
    }

    public function testItStillReturn200OkOnEmptyPaginated()
    {
        $this->dataStore->getPaginated(FakeMelodiiaModel::class, 1, $this->filtersCollection->reveal(), 250)->willReturn(new Pagerfanta(new ArrayAdapter([])))->shouldBeCalled();

        $res = ($this->controller)($this->request->reveal());

        $this->assertInstanceOf(OkContent::class, $res);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testItCheckAccessToResourceIfSpecifiedInConfiguration()
    {
        $this->attributes->get(CrudControllerInterface::SECURITY_CHECK, null)->willReturn('view');

        $this->authorizationChecker->isGranted('view', Argument::any())->willReturn(false);

        ($this->controller)($this->request->reveal(), 'id');
    }

    public function testItReturnsErrorFromFilters()
    {
        $request = $this->request->reveal();
        /** @var FormInterface|ObjectProphecy $form */
        $form = $this->prophesize(FormInterface::class);
        $form->handleRequest($request)->shouldBeCalled()->willReturn();
        $form->isSubmitted()->willReturn(true);
        $form->isValid()->willReturn(false);

        $this->filtersCollection->getForm()->willReturn($form->reveal());
        $this->dataStore->getPaginated(Argument::cetera())->shouldNotBeCalled();

        $res = ($this->controller)($request);

        $this->assertInstanceOf(FormErrorResponse::class, $res);
    }
}
