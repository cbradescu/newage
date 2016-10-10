<?php

namespace CB\Bundle\NewAgeBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CBNewAgeBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::cbLightingTypeTable($schema);
        self::cbSupportTypeTable($schema);
    }

    /**
     * Generate table cb_lighting_type
     *
     * @param Schema $schema
     */
    public static function cbLightingTypeTable(Schema $schema)
    {
        /** Generate table oro_access_group **/
        $table = $schema->createTable('cb_newage_lighting_type');
        $table->addColumn('id', 'smallint', ['notnull' => true, 'autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
//        $table->addUniqueIndex(['name'], 'UNIQ_FEF9EDB75E237F04');
        /** End of generate table oro_access_group **/
    }

    /**
     * Generate table cb_support_type
     *
     * @param Schema $schema
     */
    public static function cbSupportTypeTable(Schema $schema)
    {
        /** Generate table oro_access_group **/
        $table = $schema->createTable('cb_newage_support_type');
        $table->addColumn('id', 'smallint', ['notnull' => true, 'autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
//        $table->addUniqueIndex(['name'], 'UNIQ_FEF9EDB75E237F03');
        /** End of generate table oro_access_group **/
    }
}