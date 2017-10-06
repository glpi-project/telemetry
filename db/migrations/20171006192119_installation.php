<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Base installation script
 *
 * PHP version 7
 *
 * @category Migration
 * @package  Telemetry
 * @author   Johan Cwiklinski <johan@x-tnd.be>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://githu.com/glpi-project/telemetry
 */

use Phinx\Migration\AbstractMigration;

/**
 * Installation script
 *
 * @category Migration
 * @package  Telemetry
 * @author   Johan Cwiklinski <johan@x-tnd.be>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://githu.com/glpi-project/telemetry
 */
class Installation extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('telemetry', ['id' => false, 'primary_key' => 'id']);
        $table
            ->addColumn('id', 'biginteger', ['identity' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
            ->addColumn('glpi_uuid', 'string', ['length' => 41, 'null' => true])
            ->addColumn('glpi_version', 'string', ['length' => 25, 'null' => true])
            ->addColumn('glpi_default_language', 'string', ['length' => 10, 'null' => true])
            ->addColumn('glpi_avg_entities', 'string', ['length' => 25, 'null' => true])
            ->addColumn('glpi_avg_computers', 'string', ['length' => 25, 'null' => true])
            ->addColumn('glpi_avg_networkequipments', 'string', ['length' => 25, 'null' => true])
            ->addColumn('glpi_avg_tickets', 'string', ['length' => 25, 'null' => true])
            ->addColumn('glpi_avg_problems', 'string', ['length' => 25, 'null' => true])
            ->addColumn('glpi_avg_changes', 'string', ['length' => 25, 'null' => true])
            ->addColumn('glpi_avg_projects', 'string', ['length' => 25, 'null' => true])
            ->addColumn('glpi_avg_users', 'string', ['length' => 25, 'null' => true])
            ->addColumn('glpi_avg_groups', 'string', ['length' => 25, 'null' => true])
            ->addColumn('glpi_ldap_enabled', 'boolean', ['null' => true])
            ->addColumn('glpi_mailcollector_enabled', 'boolean', ['null' => true])
            ->addColumn('glpi_notifications', 'text', ['null' => true])
            ->addColumn('db_engine', 'string', ['length' => 50, 'null' => true])
            ->addColumn('db_version', 'string', ['length' => 50, 'null' => true])
            ->addColumn('db_size', 'biginteger', ['null' => true])
            ->addColumn('db_log_size', 'biginteger', ['null' => true])
            ->addColumn('db_sql_mode', 'text', ['null' => true])
            ->addColumn('web_engine', 'string', ['length' => 50, 'null' => true])
            ->addColumn('web_version', 'string', ['length' => 50, 'null' => true])
            ->addColumn('php_version', 'string', ['length' => 50, 'null' => true])
            ->addColumn('php_modules', 'text', ['null' => true])
            ->addColumn('php_config_max_execution_time', 'integer', ['null' => true])
            ->addColumn('php_config_memory_limit', 'string', ['length' => 10, 'null' => true])
            ->addColumn('php_config_post_max_size', 'string', ['length' => 10, 'null' => true])
            ->addColumn('php_config_safe_mode', 'boolean', ['null' => true])
            ->addColumn('php_config_session', 'text', ['null' => true])
            ->addColumn('php_config_upload_max_filesize', 'string', ['length' => 10, 'null' => true])
            ->addColumn('os_family', 'string', ['length' => 50, 'null' => true])
            ->addColumn('os_distribution', 'string', ['length' => 50, 'null' => true])
            ->addColumn('os_version', 'string', ['length' => 50, 'null' => true])
            ->create()
        ;

        $table = $this->table('glpi_plugin');
        $table
            ->addColumn('pkey', 'string', ['length' => 50, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
            ->create()
        ;

        $table = $this->table('telemetry_glpi_plugin');
        $table
            ->addColumn('telemetry_entry_id', 'biginteger', ['null' => true])
            ->addForeignKey(
                'telemetry_entry_id',
                'telemetry',
                'id',
                ['constraint' => 'telemetry_glpi_plugin_telemetry_entry_id_fkey']
            )
            ->addColumn('glpi_plugin_id', 'integer', ['null' => true])
            ->addForeignKey(
                'glpi_plugin_id',
                'glpi_plugin',
                'id',
                ['constraint' => 'telemetry_glpi_plugin_glpi_plugin_id_fkey']
            )
            ->addColumn('version', 'string', ['length' => 50, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
            ->create()
        ;

        $table = $this->table('reference');
        $table
            ->addColumn('uuid', 'string', ['length' => 41, 'null' => true])
            ->addColumn('name', 'string', ['length' => 505, 'null' => true])
            ->addColumn('country', 'string', ['length' => 10, 'null' => true])
            ->addColumn('comment', 'text', ['null' => true])
            ->addColumn('num_assets', 'integer', ['null' => true])
            ->addColumn('num_helpdesk', 'integer', ['null' => true])
            ->addColumn('email', 'string', ['length' => 505, 'null' => true])
            ->addColumn('phone', 'string', ['length' => 30, 'null' => true])
            ->addColumn('url', 'string', ['length' => 505, 'null' => true])
            ->addColumn('referent', 'string', ['length' => 505, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => true])
            ->addColumn('is_displayed', 'boolean', ['default' => false, 'null' => true])
            ->create()
        ;
    }
}
