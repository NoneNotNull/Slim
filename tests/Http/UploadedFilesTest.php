<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @link      https://github.com/codeguy/Slim
 * @copyright Copyright (c) 2011-2015 Josh Lockhart
 * @license   https://github.com/codeguy/Slim/blob/master/LICENSE (MIT License)
 */
namespace Slim\Tests\Http;

use Slim\Http\Body;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Stream;
use Slim\Http\UploadedFile;
use Slim\Http\Uri;


class UploadedFilesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @beforeClass
     */
    public static function setUpBeforeClass() {
        $fh = fopen("./phpUxcOty", "w");
        fwrite($fh, "12345678");
        fclose($fh);
    }

    /**
     * @afterClass
     */
    public static function tearDownAfterClass()
    {
        unlink("./phpUxcOty");
    }

    /**
     * @param array $input The input array to parse.
     * @param array $expected The expected normalized output.
     *
     * @dataProvider providerCreateFromEnvironment
     */
    public function testCreateFromEnvironmentFromFilesSuperglobal(array $input, array $expected)
    {
        $_FILES = $input;

        $uploadedFile = UploadedFile::createFromEnvironment(Environment::mock());
        $this->assertEquals($expected, $uploadedFile);
    }

    /**
     * @return UploadedFile
     */
    public function testConstructor() {
        $attr = [
            'tmp_name' => './phpUxcOty',
            'name'     => 'my-avatar.txt',
            'size'     => 8,
            'type'     => 'text/plain',
            'error'    => 0,
        ];

        $uploadedFile = new UploadedFile($attr['tmp_name'], $attr['name'], $attr['type'], $attr['size'], $attr['error'], $sapi = false);


        $this->assertEquals($attr['name'], $uploadedFile->getClientFilename());
        $this->assertEquals($attr['type'], $uploadedFile->getClientMediaType());
        $this->assertEquals($attr['size'], $uploadedFile->getSize());
        $this->assertEquals($attr['error'], $uploadedFile->getError());

        return $uploadedFile;
    }

    /**
     * @depends testConstructor
     * @param UploadedFile $uploadedFile
     * @return UploadedFile
     */
    public function testGetStream(UploadedFile $uploadedFile) {
        $stream = $uploadedFile->getStream();
        $this->assertEquals(true, $uploadedFile->getStream() instanceof Stream);
        $stream->close();

        return $uploadedFile;
    }



    public function providerCreateFromEnvironment()
    {
        return [
                    [
                        [
                            'files' => [
                                'tmp_name' => [
                                    0 => __DIR__ . DIRECTORY_SEPARATOR . 'file0.txt',
                                    1 => __DIR__ . DIRECTORY_SEPARATOR . 'file1.html',
                                ],
                                'name'     => [
                                    0 => 'file0.txt',
                                    1 => 'file1.html',
                                ],
                                'type'     => [
                                    0 => 'text/plain',
                                    1 => 'text/html',
                                ],
                                'error'    => [
                                    0 => 0,
                                    1 => 0
                                ]
                            ],
                        ],
                        [
                            'files' => [
                                0 => new UploadedFile(__DIR__ . DIRECTORY_SEPARATOR . 'file0.txt', 'file0.txt', 'text/plain',
                                    null, UPLOAD_ERR_OK, true),
                                1 => new UploadedFile(__DIR__ . DIRECTORY_SEPARATOR . 'file1.html', 'file1.html', 'text/html',
                                    null, UPLOAD_ERR_OK, true),
                            ],
                        ]
                    ],
                    [
                        [
                            'avatar' => [
                                'tmp_name' => 'phpUxcOty',
                                'name'     => 'my-avatar.png',
                                'size'     => 90996,
                                'type'     => 'image/png',
                                'error'    => 0,
                            ],
                        ],
                        [
                            'avatar' => new UploadedFile('phpUxcOty', 'my-avatar.png', 'image/png', 90996, UPLOAD_ERR_OK, true)
                        ]
                    ]
                ];
    }

    /**
     * @param array $mockEnv An array representing a mock environment.
     *
     * @return Request
     */
    public function requestFactory(array $mockEnv)
    {
        $env = Environment::mock();

        $uri = Uri::createFromString('https://example.com:443/foo/bar?abc=123');
        $headers = Headers::createFromEnvironment($env);
        $cookies = [];
        $serverParams = $env->all();
        $body = new Body(fopen('php://temp', 'r+'));
        $uploadedFiles = UploadedFile::createFromEnvironment($env);
        $request = new Request('GET', $uri, $headers, $cookies, $serverParams, $body, $uploadedFiles);

        return $request;
    }

    
}
