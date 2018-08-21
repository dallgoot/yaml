<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Dallgoot\Yaml\Yaml as Y;

final class APITest extends TestCase
{
    public function testaddReference($value='')
    {
     // code...
    }

    /**
     * @depends testAddReference
     */
    public function testGetReference($value='')
    {
     // code...
    }

    /**
     * @depends testAddReference
     */
    public function testGetAllReferences($value='')
    {
     // code...
    }

    public function testAddComment($value='')
    {
     // code...
    }

    /**
     * @depends testAddComment
     */
    public function testGetComment($value='')
    {
     // code...
    }

    public function testSetText($value='')
    {
     // code...
    }

    public function testAddTag($value='')
    {
     // code...
    }
}