<?php
namespace SoliDryTest\Unit\Helpers;

use SoliDry\Helpers\ConfigOptions;
use SoliDryTest\Unit\TestCase;

/**
 * Class ConfigOptionsTest
 * @package rjapitest\Unit\Blocks
 * @property ConfigOptions configOptions
 */
class ConfigOptionsTest extends TestCase
{
    private $configOptions;

    public function setUp(): void
    {
        parent::setUp();
        $this->configOptions = new ConfigOptions();
    }

    /**
     * @test
     */
    public function it_sets_configuration_options()
    {
        $accessToken = sha1(time());
        $this->configOptions->setQueryAccessToken($accessToken);
        $this->assertEquals($this->configOptions->getQueryAccessToken(), $accessToken);

        $limit = mt_rand(5, 10);
        $this->configOptions->setQueryLimit($limit);
        $this->assertEquals($this->configOptions->getQueryLimit(), $limit);

        $sort = 'desc';
        $this->configOptions->setQuerySort($sort);
        $this->assertEquals($this->configOptions->getQuerySort(), $sort);

        $page = 1;
        $this->configOptions->setQueryPage($page);
        $this->assertEquals($this->configOptions->getQueryPage(), $page);

        $this->configOptions->setJwtIsEnabled(true);
        $this->assertTrue($this->configOptions->getJwtIsEnabled());

        $jwtTable = 'users';
        $this->configOptions->setJwtTable($jwtTable);
        $this->assertEquals($this->configOptions->getJwtTable(), $jwtTable);

        $this->configOptions->setIsJwtAction(true);
        $this->assertTrue($this->configOptions->getIsJwtAction());

        $this->configOptions->setStateMachine(true);
        $this->assertTrue($this->configOptions->isStateMachine());

        $this->configOptions->setSpellCheck(true);
        $this->assertTrue($this->configOptions->isSpellCheck());

        $this->configOptions->setBitMask(true);
        $this->assertTrue($this->configOptions->isBitMask());

        $this->configOptions->setIsCached(true);
        $this->assertTrue($this->configOptions->isCached());

        $this->configOptions->setIsXFetch(true);
        $this->assertTrue($this->configOptions->isXFetch());

        $this->configOptions->setCacheTtl(3600);
        $this->assertEquals(3600, $this->configOptions->getCacheTtl());

        $this->configOptions->setCacheBeta(1.1);
        $this->assertEquals(1.1, $this->configOptions->getCacheBeta());

        $this->configOptions->setCalledMethod('index');
        $this->assertEquals('index', $this->configOptions->getCalledMethod());
    }
}