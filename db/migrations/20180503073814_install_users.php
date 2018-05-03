<?php


use Phinx\Migration\AbstractMigration;

class InstallUsers extends AbstractMigration
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
     */
    public function up()
    {

        $table = $this->table('users');
        $table
            ->addColumn('user', 'string', ['length' => 32, 'null' => true])
            ->addColumn('hash', 'string', ['length' => 255, 'null' => true])
            ->addColumn('email', 'string', ['length' => 505, 'null' => true])
            ->addColumn('is_admin', 'boolean', ['null' => true])
            ->create()
        ;

        $table = $this->table('reference');
        $table
            ->addColumn('user_id', 'integer', ['null' => true])
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                ['delete'=> 'CASCADE','constraint' => 'telemetry_users_reference_id_fkey']
            )
            ->addIndex(['user_id'], ['unique' => true])
            ->update()
        ;
    }

    public function down()
    {
        

        $table = $this->table('reference');
        $table
            ->dropForeignKey(
                'telemetry_users_reference_id_fkey'
            )
            ->removeColumn('user_id', 'integer', ['null' => true])
            ->update()
        ;

        $this->dropTable('users');
    }
}
