// tests/Tools/DatabaseSchemaToolTrait.php
<?php

namespace App\Tests\Tools;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

trait DatabaseSchemaToolTrait
{
    protected static bool $schemaCreated = false;

    protected function ensureSchema(EntityManagerInterface $em): void
    {
        if (self::$schemaCreated) {
            return;
        }
        $meta = $em->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($em);
        $tool->dropDatabase();
        if (!empty($meta)) {
            $tool->createSchema($meta);
        }
        self::$schemaCreated = true;
    }
}
