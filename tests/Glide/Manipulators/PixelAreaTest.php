<?php
declare(strict_types=1);

namespace I4code\GlideService\Test\Glide\Manipulators;

use I4code\GlideService\Glide\Manipulators\PixelArea;
use Intervention\Image\AbstractDriver;
use Intervention\Image\Image;
use PHPUnit\Framework\TestCase;

class PixelAreaTest extends TestCase
{
    private $manipulator;

    public function setUp(): void
    {
        $this->manipulator = new PixelArea();
    }

    public function createImageMock()
    {
        $driverMock = $this->createMock(AbstractDriver::class);
        $driverMock->method('init')->willReturn(
            $this->createMock(Image::class)
        );

        $imageMock = $this->getMockBuilder(Image::class)
            ->onlyMethods(['getDriver', 'height', 'save', 'width'])
            ->addMethods(['insert', 'pixelate'])
            ->getMock();

        $imageMock->method('width')->willReturn(1000);
        $imageMock->method('height')->willReturn(600);
        $imageMock->method('getDriver')->willReturn($driverMock);
        return $imageMock;
    }

    public function testCreateInstance()
    {
        $this->assertInstanceOf(PixelArea::class, $this->manipulator);
    }

    public function testGetCoordinates()
    {
        $imageMock = $this->createImageMock();

        $this->manipulator->setParams([
            'pixelarea' => '200,100,50,50'
        ]);

        $this->assertIsArray($this->manipulator->getCoordinates($imageMock));
    }

    public function testGetOffsetX()
    {
        $imageMock = $this->createImageMock();

        $offsetX = $this->manipulator->getOffsetX($imageMock);
        $this->assertIsInt($offsetX);
        $this->assertEquals(0, $offsetX);

        $this->manipulator->setParams([
            'pixelarea' => '200,100,50,50'
        ]);
        $offsetX = $this->manipulator->getOffsetX($imageMock);
        $this->assertIsInt($offsetX);
        $this->assertEquals(50, $offsetX);

        $this->manipulator->setParams([
            'pixelarea' => '200,100,100,50'
        ]);
        $offsetX = $this->manipulator->getOffsetX($imageMock);
        $this->assertIsInt($offsetX);
        $this->assertEquals(100, $offsetX);
    }

    public function testGetOffsetY()
    {
        $imageMock = $this->createImageMock();

        $offsetY = $this->manipulator->getOffsetY($imageMock);
        $this->assertIsInt($offsetY);
        $this->assertEquals(0, $offsetY);

        $this->manipulator->setParams([
            'pixelarea' => '200,100,50,50'
        ]);
        $offsetY = $this->manipulator->getOffsetY($imageMock);
        $this->assertIsInt($offsetY);
        $this->assertEquals(50, $offsetY);

        $this->manipulator->setParams([
            'pixelarea' => '200,100,100,100'
        ]);
        $offsetY = $this->manipulator->getOffsetY($imageMock);
        $this->assertIsInt($offsetY);
        $this->assertEquals(100, $offsetY);
    }

    public function testGetAndPixelateCropFromImage()
    {
        $imageMock = $this->createImageMock();

        $image = $this->manipulator->getImage($imageMock);

        $this->assertInstanceOf(Image::class, $image);
    }

    public function testRunProcessing()
    {
        $imageMock = $this->createImageMock();

        $imageMock->expects($this->once())
            ->method('insert')
            ->willReturn($this->createMock(Image::class));

        $this->manipulator->setParams([
            'pixelarea' => '200,100,100,100'
        ]);

        $this->assertInstanceOf(Image::class, $this->manipulator->run($imageMock));
    }
}
