<?php

namespace SoliDryTest\Unit\Extensions;

use Faker\Factory;
use Illuminate\Routing\Route;
use Illuminate\Http\Request;
use Modules\V2\Http\Controllers\ArticleController;
use SoliDry\Extension\BaseController;
use SoliDry\Extension\JSONApiInterface;
use SoliDryTest\_data\ArticleFixture;
use SoliDryTest\_data\TopicFixture;
use SoliDryTest\Unit\TestCase;

/**
 * Class ApiController
 * @package rjapitest\Unit\Extensions
 *
 * @property BaseController baseController
 */
class ApiControllerTest extends TestCase
{
    private const RELATION = 'topic';

    private $baseController;

    /**
     * @throws \SoliDry\Exceptions\HeadersException
     */
    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $_SERVER['HTTP_HOST'] = 'localhost';

        $router               = new Route(['POST', 'GET'], '', function () {
        });
        $this->baseController = new ArticleController($router);

    }

    /**
     * @test
     * @throws \SoliDry\Exceptions\HeadersException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function it_runs_index()
    {
        $router = new Route(['GET'], '/' . self::API_VERSION . '/article?include=tag&data=["title", "description"]', function () {
        });
        $router->setAction(['controller' => 'ArticleController@index']);
        $this->baseController = new ArticleController($router);

        $req = new Request();
        $req->initialize([
            'include' => 'tag',
        ]);
        $resp = $this->baseController->index($req);

        // @todo: Change simple 200 OK check to more complex tests
        $this->assertEquals($resp->getStatusCode(), JSONApiInterface::HTTP_RESPONSE_CODE_OK);
    }

    /**
     * @test
     * @throws \SoliDry\Exceptions\HeadersException
     * @throws AttributesException
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function it_runs_view()
    {
        $item = ArticleFixture::createAndGet();

        $router = new Route(['GET'], '/v2/article/' . $item->id . '?include=tag&data=["title", "description"]', function () {
        });
        $router->setAction(['controller' => 'ArticleController@view']);
        $this->assertTrue(is_string($item->id));

        $this->baseController = new ArticleController($router);

        $resp = $this->baseController->view($this->request(), $item->id);

        // @todo: Change simple 200 OK check to more complex tests
        $this->assertEquals($resp->getStatusCode(), JSONApiInterface::HTTP_RESPONSE_CODE_OK);
    }

    /**
     * @test
     * @throws \SoliDry\Exceptions\HeadersException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws AttributesException
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function it_runs_create()
    {
        $topic = TopicFixture::createAndGet();

        $router = new Route(['POST'], '/v2/article/', function () {
        });
        $router->setAction(['controller' => 'ArticleController@view']);

        $this->baseController = new ArticleController($router);

        $faker   = Factory::create();
        $reqData = [
            'data' => [
                'type'          => 'article',
                'attributes'    => [
                    'id'           => uniqid(),
                    'title'        => $faker->title,
                    'description'  => $faker->name,
                    'fake_attr'    => 'attr',
                    'url'          => 'http://example.com/articles_feed' . uniqid(),
                    'show_in_top'  => '0',
                    'topic_id'     => 1,
                    'rate'         => 5,
                    'date_posted'  => '2017-12-12',
                    'time_to_live' => '10:11:12',
                ],
                'relationships' => [
                    'topic' => [
                        'data' => ['type' => 'topic', 'id' => $topic->id],
                    ],
                ],
            ],
        ];


        $req  = $this->request($reqData);
        $resp = $this->baseController->create($req);

        $this->assertEquals($resp->getStatusCode(), JSONApiInterface::HTTP_RESPONSE_CODE_CREATED);

        $respData = json_decode($resp->getContent(), true);
        $this->assertEquals($reqData['data']['attributes']['id'], $respData['data']['id']);

        return $reqData;
    }

    /**
     * @test
     * @depends it_runs_create
     * @param array $reqData
     * @return array
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function it_runs_update(array $reqData)
    {
        $faker                                  = Factory::create();
        $id                                     = $reqData['data']['attributes']['id'];
        $reqData['data']['attributes']['title'] = $faker->title;

        $req  = $this->request($reqData);
        $resp = $this->baseController->update($req, $id);

        $this->assertEquals($resp->getStatusCode(), JSONApiInterface::HTTP_RESPONSE_CODE_OK);

        $respData = json_decode($resp->getContent(), true);
        $this->assertEquals($id, $respData['data']['id']);

        return $reqData;
    }

    /**
     * @test
     * @param array $reqData * @depends it_runs_update
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @depends it_runs_update
     */
    public function it_runs_delete(array $reqData)
    {
        $id = $reqData['data']['attributes']['id'];

        $req  = $this->request($reqData);
        $resp = $this->baseController->delete($req, $id);

        $this->assertEquals($resp->getStatusCode(), JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT);
    }

    /**
     * @test
     */
    public function it_creates_relations()
    {
        $topic   = TopicFixture::createAndGet();
        $article = ArticleFixture::createAndGet();

        $relation = 'topic';
        $reqData  = [
            'data' => [
                'type'          => 'article',
                'id'            => $article->id,
                'relationships' => [
                    $relation => [
                        'data' => ['type' => $relation, 'id' => $topic->id],
                    ]
                ]
            ]
        ];

        $req  = $this->request($reqData);
        $resp = $this->baseController->createRelations($req, $article->id, $relation);

        $respData = json_decode($resp->getContent(), true);
        $this->assertEquals($respData['data']['relationships'][$relation]['data']['id'], $reqData['data']['relationships'][$relation]['data']['id']);

        TopicFixture::truncate();

        return $reqData;
    }

    /**
     * @test
     * @depends it_creates_relations
     * @param array $reqData
     * @return array
     */
    public function it_updates_relations(array $reqData) : array
    {
        $topic = TopicFixture::createAndGet();

        $relation = 'topic';

        $reqData['data']['relationships'][$relation]['data']['id'] = $topic->id;
        $req                                                       = $this->request($reqData);
        $resp                                                      = $this->baseController->updateRelations($req, $reqData['data']['id'], $relation);

        $respData = json_decode($resp->getContent(), true);

        $this->assertEquals($respData['data']['relationships'][$relation]['data']['id'], $reqData['data']['relationships'][$relation]['data']['id']);

        return $reqData;
    }

    /**
     * @test
     * @depends it_updates_relations
     * @param array $reqData
     * @return array
     */
    public function it_gets_relations(array $reqData) : array
    {
        $req  = $this->request();
        $resp = $this->baseController->relations($req, $reqData['data']['id'], self::RELATION);

        $respData = json_decode($resp->getContent(), true);
        $this->assertEquals($reqData['data']['relationships'][self::RELATION]['data']['id'], $respData['data']['id']);
        $this->assertEquals($reqData['data']['relationships'][self::RELATION]['data']['type'], $respData['data']['type']);

        return $reqData;
    }

    // @todo: deleteRelations
    /**
     * @test
     * @depends it_gets_relations
     * @param array $reqData
     */
    public function it_deletes_relation(array $reqData)
    {
        $req = $this->request([
            'data' => [
                [
                    'type' => self::RELATION,
                    'id'   => $reqData['data']['relationships'][self::RELATION]['data']['id'],
                ],
            ],
        ]);

        $resp = $this->baseController->deleteRelations($req, $reqData['data']['id'], self::RELATION);

        $this->assertEquals($resp->getStatusCode(), JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT);
    }
}