<?php


use Phinx\Migration\AbstractMigration;

class DynamicReference extends AbstractMigration
{
    /**
     * Up method
     *
     * @return void
     */
    public function up()
    {
        $table = $this->table('dynamic_reference');
        $table
            ->addColumn('reference_id', integer)
            ->addForeignKey(
                'reference_id',
                'reference',
                'id',
                ['constraint' => 'telemetry_dynamic_reference_reference_id_fkey']
            )
            ->addColumn('name', 'string', ['length' => 50])
            ->addColumn('value', 'string', ['length' => 50])
            ->addTimestamps()
            ->addIndex(['reference_id', 'name'], ['unique' => true])
            ->create()
        ;

        //migrate existing data...
        $rows = $this->fetchAll('SELECT id, num_assets, num_helpdesk FROM reference');
        $news = [];
        foreach ($rows as $row) {
            if ((int)$row['num_assets'] > 0) {
                $news[] = [
                    'reference_id'  => $row['id'],
                    'name'          => 'num_assets',
                    'value'         => $row['num_assets']
                ];
            }
            if ((int)$row['num_helpdesk'] > 0) {
                $news[] = [
                    'reference_id'  => $row['id'],
                    'name'          => 'num_helpdesk',
                    'value'         => $row['num_helpdesk']
                ];
            }
        }

        if (count($news)) {
            $this->insert('dynamic_reference', $news);
        }

        $table = $this->table('reference');
        $table
            ->removeColumn('num_assets')
            ->removeColumn('num_helpdesk')
            ->save()
        ;
    }

    /**
     * Down method
     *
     * @return void
     */
    public function down()
    {
        $table = $this->table('reference');
        $table
            ->addColumn('num_assets', 'integer', ['null' => true])
            ->addColumn('num_helpdesk', 'integer', ['null' => true])
            ->save()
        ;

        //no down for data; sorry!

        $this->dropTable('dynamic_reference');
    }
}
