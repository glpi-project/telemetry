<?php


use Phinx\Migration\AbstractMigration;
use GLPI\Telemetry\Models\Reference as ReferenceModel;

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
     *
     * Your database must be empty.
     * From default an admin account is create. There is no way to remove it from the application.
     * You should delete it from the database.
     */
    public function up()
    {

        $table = $this->table('users');
        $table
            ->addColumn('username', 'string', ['length' => 255, 'null' => true])
            ->addColumn('hash', 'string', ['length' => 255, 'null' => true])
            ->addColumn('email', 'string', ['length' => 505, 'null' => true])
            ->addColumn('is_admin', 'boolean', ['null' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->create()
        ;

        $this
            ->insert('users',
                [
                    'username' => 'admin',
                    'hash' => password_hash('admin', PASSWORD_DEFAULT),
                    'is_admin' => true,
                    'email' => 'admin@admin.fr'
                ]
            )
        ;


        $table = $this->table('reference');
        $table
            ->addColumn('user_id', 'integer', ['null' => true])
            ->addColumn('status', 'integer', ['default' => 1, 'null' => true])
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                ['delete'=> 'CASCADE','constraint' => 'telemetry_users_reference_id_fkey']
            )
            ->addIndex(['user_id'], ['unique' => false])
            ->update()
        ;

        // query()
        $stmt = $this->query("SELECT * FROM reference"); // returns PDOStatement
        $rows = $stmt->fetchAll(); // returns the result as an array

        foreach ($rows as $key => $value) {
            $id = $value['id'];
            if ($value['is_displayed'] == true) {
                $str = "UPDATE reference set status = '2' WHERE id = $id";
            } else {
                $str = "UPDATE reference set status = '1' WHERE id = $id";
            }
            $this->query($str);
        }

        $table->removeColumn('is_displayed')->update();
    }

    public function down()
    {

        $table = $this->table('reference');
        $table
            ->dropForeignKey(
                'telemetry_users_reference_id_fkey'
            )
            ->removeColumn('user_id', 'integer', ['null' => true])
            ->addColumn('is_displayed', 'boolean', ['default' => false, 'null' => true])
            ->update()
        ;

        // query()
        $stmt = $this->query("SELECT * FROM reference"); // returns PDOStatement
        $rows = $stmt->fetchAll(); // returns the result as an array

        foreach ($rows as $key => $value) {
            $id = $value['id'];
            if ($value['status'] == 2) {
                $str = "UPDATE reference set is_displayed = true WHERE id = $id";
            } else {
                $str = "UPDATE reference set is_displayed = false WHERE id = $id";
            }
            $this->query($str);
        }


        $table = $this->table('reference');
        $table
            ->removeColumn('status', 'integer', ['null' => true])
            ->update()
        ;

        $this->table('users')
        ->dropForeignKey(
            'telemetry_sessions_reference_id_fkey'
        )->update();
        
        $this->dropTable('users');
    }
}
