<?php

use somov\requirements\Checker;
use somov\requirements\RequirementBase;

/**
 *
 * User: develop
 * Date: 15.01.2019
 */
class CheckerTest extends \Codeception\TestCase\Test
{

    public function testExtension()
    {
        $r = (new Checker([
            'requirements' => [
                'pdo_test' => [
                    'class' => \somov\requirements\Extension::class,
                    'extension' => 'pdo'
                ],

                'int_test' => [
                    'class' => \somov\requirements\Extension::class,
                    'extension' => 'intl',
                    'mandatory' => false
                ],

                'meme_cache' => [
                    'class' => \somov\requirements\Extension::class,
                    'extension' => ['memcache1', 'memcached2'],
                    'mandatory' => false
                ]


            ]
        ]))->checkGroup();

        $this->assertArrayHasKey('success', $r);

    }


    /**
     * @return array
     */
    public function getSingleExtensionsClasses()
    {
        return [
            [\somov\requirements\GdExtension::class, true],
            [\somov\requirements\ImagickExtension::class, false],
            [
                [
                    'class' => \somov\requirements\Ini::class,
                    'optionName' => 'allow_url_include'
                ],
                true
            ]
        ];
    }

    /**
     * @dataProvider getSingleExtensionsClasses()
     * @param string $class
     */
    public function testSingleExtension($class, $r)
    {
        $this->assertSame($r, (new Checker())->checkSingle($class));
    }

    public function testRequirementsBase()
    {
        $r = (new Checker())->checkWithBase();
        $this->assertArrayHasKey(RequirementBase::TYPE_SUCCESS, $r);
        $this->assertArrayHasKey(RequirementBase::TYPE_WARNING, $r);
    }

    public function testCheckByIndex()
    {

        $r = (new Checker())->checkByIndex(Checker::I_WRITABLE_PATH, $requirement);
        $this->assertTrue($r);
        $this->assertInstanceOf(RequirementBase::class, $requirement);

        $reg = [
            'class' => \somov\requirements\ShellExec::class,
            'executableFile' => 'ls'
        ];
        $r = (new Checker())->checkSingleRef($reg);
        $n = $reg->getMessage();

        $this->assertTrue($r);
        $this->assertInstanceOf(RequirementBase::class, $requirement);


    }


    public function testExcluded()
    {

        /** @var Checker $checker */
        $checker = Yii::createObject([
            'class' => Checker::class,
            'excluded' => [Checker::I_PHP_VERSION]
        ]);

        $res = $checker->checkWithBase();

        $this->assertArrayNotHasKey(Checker::I_PHP_VERSION, $res[RequirementBase::TYPE_SUCCESS]);

    }

}