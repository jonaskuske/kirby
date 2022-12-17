<?php

namespace Kirby\Template;

use Kirby\Exception\LogicException;
use ReflectionProperty;

/**
 * @coversDefaultClass Kirby\Template\Slot
 */
class SlotTest extends TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::isOpen
	 * @covers ::name
	 */
	public function testConstruct()
	{
		$snippet = new Snippet('test.php');
		$slot    = new Slot($snippet, 'test');

		$this->assertSame($snippet, $slot->snippet);
		$this->assertSame('test', $slot->name());
		$this->assertFalse($slot->isOpen());
		$this->assertNull($slot->content);
		$this->assertNull($slot->render());
		$this->assertSame('', $slot->__toString());
	}

	/**
	 * @covers ::begin
	 * @covers ::end
	 */
	public function testHelpers()
	{
		$this->assertNull(Snippet::$current);
		$snippet = Snippet::begin('test.php');
		$this->assertSame($snippet, Snippet::$current);

		$captureProp = new ReflectionProperty($snippet, 'capture');
		$slotsProp   = new ReflectionProperty($snippet, 'slots');
		$captureProp->setAccessible(true);
		$slotsProp->setAccessible(true);

		$this->assertCount(0, $captureProp->getValue($snippet));
		$this->assertCount(0, $slotsProp->getValue($snippet));

		$slot = Slot::begin();
		$this->assertInstanceOf(Slot::class, $slot);
		$this->assertCount(1, $captureProp->getValue($snippet));
		$this->assertCount(0, $slotsProp->getValue($snippet));

		Slot::end();
		$this->assertCount(0, $captureProp->getValue($snippet));
		$this->assertCount(1, $slotsProp->getValue($snippet));

		$snippet->close();
	}

	/**
	 * @covers ::open
	 * @covers ::close
	 */
	public function testOpenClose()
	{
		$slot = new Slot(new Snippet('test.php'), 'test');

		$this->assertNull($slot->content);
		$this->assertFalse($slot->isOpen());
		$slot->open();
		$this->assertTrue($slot->isOpen());

		echo $content = 'Test';
		$slot->close();

		$this->assertFalse($slot->isOpen());
		$this->assertSame($content, $slot->content);
	}

	/**
	 * @covers ::close
	 */
	public function testCloseWhenNotOpen()
	{
		$slot = new Slot(new Snippet('test.php'), 'test');

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The slot has not been opened');

		$slot->close();
	}

	/**
	 * @covers ::render
	 * @covers ::__toString
	 */
	public function testRender()
	{
		$slot    = new Slot(new Snippet('test.php'), 'test');
		$content = 'Test content';

		$slot->open();
		echo $content;
		$slot->close();

		$this->assertSame('Test content', $slot->content);
		$this->assertSame('Test content', $slot->render());
		$this->assertSame('Test content', $slot->__toString());
	}
}
