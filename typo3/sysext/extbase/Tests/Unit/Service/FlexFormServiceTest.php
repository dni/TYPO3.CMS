<?php
namespace TYPO3\CMS\Extbase\Tests\Unit\Service;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class FlexFormServiceTest extends UnitTestCase
{
    /**
     * @var bool Reset singletons created by subject
     */
    protected $resetSingletonInstances = true;

    /**
     * @test
     */
    public function convertFlexFormContentToArrayResolvesComplexArrayStructure()
    {
        $input = '<?xml version="1.0" encoding="iso-8859-1" standalone="yes"?>
<T3FlexForms>
	<data>
		<sheet index="sDEF">
			<language index="lDEF">
				<field index="settings.foo">
					<value index="vDEF">Foo-Value</value>
				</field>
				<field index="settings.bar">
					<el index="el">
						<section index="1">
							<itemType index="_arrayContainer">
								<el>
									<field index="baz">
										<value index="vDEF">Baz1-Value</value>
									</field>
									<field index="bum">
										<value index="vDEF">Bum1-Value</value>
									</field>
									<field index="dot.one">
										<value index="vDEF">dot.one-Value</value>
									</field>
									<field index="dot.two">
										<value index="vDEF">dot.two-Value</value>
									</field>
								</el>
							</itemType>
							<itemType index="_TOGGLE">0</itemType>
						</section>
						<section index="2">
							<itemType index="_arrayContainer">
								<el>
									<field index="baz">
										<value index="vDEF">Baz2-Value</value>
									</field>
									<field index="bum">
										<value index="vDEF">Bum2-Value</value>
									</field>
								</el>
							</itemType>
							<itemType index="_TOGGLE">0</itemType>
						</section>
					</el>
				</field>
			</language>
		</sheet>
	</data>
</T3FlexForms>';

        $expected = [
            'settings' => [
                'foo' => 'Foo-Value',
                'bar' => [
                    1 => [
                        'baz' => 'Baz1-Value',
                        'bum' => 'Bum1-Value',
                        'dot' => [
                            'one' => 'dot.one-Value',
                            'two' => 'dot.two-Value',
                        ],
                    ],
                    2 => [
                        'baz' => 'Baz2-Value',
                        'bum' => 'Bum2-Value'
                    ]
                ]
            ]
        ];

        // The subject calls xml2array statically, which calls getHash and setHash statically, which uses
        // caches, those need to be mocked.
        $cacheManagerMock = $this->createMock(\TYPO3\CMS\Core\Cache\CacheManager::class);
        $cacheMock = $this->createMock(\TYPO3\CMS\Core\Cache\Frontend\FrontendInterface::class);
        $cacheManagerMock->expects($this->any())->method('getCache')->will($this->returnValue($cacheMock));
        GeneralUtility::setSingletonInstance(\TYPO3\CMS\Core\Cache\CacheManager::class, $cacheManagerMock);

        $flexFormService = $this->getMockBuilder(\TYPO3\CMS\Extbase\Service\FlexFormService::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $convertedFlexFormArray = $flexFormService->convertFlexFormContentToArray($input);
        $this->assertSame($expected, $convertedFlexFormArray);
    }
}
