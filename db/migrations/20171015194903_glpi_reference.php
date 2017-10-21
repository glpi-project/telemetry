<?php


use Phinx\Migration\AbstractMigration;

class GlpiReference extends AbstractMigration
{
    /**
     * Up method
     *
     * @return void
     */
    public function up()
    {
        $table = $this->table('glpi_reference');
        $table
            ->addColumn('reference_id', 'integer')
            ->addForeignKey(
                'reference_id',
                'reference',
                'id',
                ['constraint' => 'telemetry_glpi_reference_reference_id_fkey']
            )
            ->addColumn('num_assets', 'integer', ['null' => true])
            ->addColumn('num_helpdesk', 'integer', ['null' => true])
            ->addTimestamps()
            ->addIndex(['reference_id'], ['unique' => true])
            ->create()
        ;

        //migrate existing data...
        $rows = $this->fetchAll('SELECT id, num_assets, num_helpdesk FROM reference');
        $news = [];
        foreach ($rows as $row) {
            $news[] = [
                'reference_id'  => $row['id'],
                'num_assets'    => $row['num_assets'],
                'num_helpdesk'    => $row['num_helpdesk']
            ];
        }

        if (count($news)) {
            $this->insert('glpi_reference', $news);
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

        $this->dropTable('glpi_reference');
    }
}
