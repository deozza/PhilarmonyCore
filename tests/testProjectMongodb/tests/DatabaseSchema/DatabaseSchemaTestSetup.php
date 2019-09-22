<?php


namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\DatabaseSchema;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DatabaseSchemaTestSetup extends WebTestCase
{

    protected function setUp()
    {
        $kernel = self::bootKernel();
    }

    public function deleteDirectory($dir)
    {
        if (!file_exists($dir))
        {
            return true;
        }

        if (!is_dir($dir))
        {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item)
        {
            if ($item == '.' || $item == '..')
            {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item))
            {
                return false;
            }

        }

        return rmdir($dir);
    }
}