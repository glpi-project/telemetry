<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Add known plugins
 *
 * PHP version 7
 *
 * @category Migration
 * @package  Telemetry
 * @author   Johan Cwiklinski <johan@x-tnd.be>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://githu.com/glpi-project/telemetry
 */

use Phinx\Seed\AbstractSeed;

/**
 * Add known plugins
 *
 * @category Migration
 * @package  Telemetry
 * @author   Johan Cwiklinski <johan@x-tnd.be>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://githu.com/glpi-project/telemetry
 */
class GlpiPlugins extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $data = [
            ['pkey' => 'room'],
            ['pkey' => 'additionalalerts'],
            ['pkey' => 'addressing'],
            ['pkey' => 'racks'],
            ['pkey' => 'manageentities'],
            ['pkey' => 'manufacturersimports'],
            ['pkey' => 'accounts'],
            ['pkey' => 'fusioninventory'],
            ['pkey' => 'appliances'],
            ['pkey' => 'archires'],
            ['pkey' => 'backups'],
            ['pkey' => 'badges'],
            ['pkey' => 'certificates'],
            ['pkey' => 'databases'],
            ['pkey' => 'domains'],
            ['pkey' => 'ideabox'],
            ['pkey' => 'financialreports'],
            ['pkey' => 'eventlog'],
            ['pkey' => 'environment'],
            ['pkey' => 'immobilizationsheets'],
            ['pkey' => 'installations'],
            ['pkey' => 'network'],
            ['pkey' => 'reports'],
            ['pkey' => 'outlookical'],
            ['pkey' => 'resources'],
            ['pkey' => 'rights'],
            ['pkey' => 'routetables'],
            ['pkey' => 'shellcommands'],
            ['pkey' => 'validation'],
            ['pkey' => 'mailkb'],
            ['pkey' => 'webapplications'],
            ['pkey' => 'shutdowns'],
            ['pkey' => 'syslogng'],
            ['pkey' => 'treeview'],
            ['pkey' => 'centreon'],
            ['pkey' => 'dumpentity'],
            ['pkey' => 'loadentity'],
            ['pkey' => 'pdf'],
            ['pkey' => 'datainjection'],
            ['pkey' => 'genericobject'],
            ['pkey' => 'order'],
            ['pkey' => 'uninstall'],
            ['pkey' => 'geninventorynumber'],
            ['pkey' => 'removemfromocs'],
            ['pkey' => 'massocsimport'],
            ['pkey' => 'webservices'],
            ['pkey' => 'cacti'],
            ['pkey' => 'connections'],
            ['pkey' => 'alerttimeline'],
            ['pkey' => 'snort'],
            ['pkey' => 'alias2010'],
            ['pkey' => 'importbl'],
            ['pkey' => 'bestmanagement'],
            ['pkey' => '22032'],
            ['pkey' => 'projet'],
            ['pkey' => 'morecron'],
            ['pkey' => 'AdsmTape2010'],
            ['pkey' => 'renamer'],
            ['pkey' => 'relations'],
            ['pkey' => 'catalogueservices'],
            ['pkey' => 'ticketmail'],
            ['pkey' => 'ticketlink'],
            ['pkey' => 'behaviors'],
            ['pkey' => 'mobile'],
            ['pkey' => 'forward'],
            ['pkey' => 'barscode'],
            ['pkey' => 'monitoring'],
            ['pkey' => 'formcreator'],
            ['pkey' => 'themes'],
            ['pkey' => 'positions'],
            ['pkey' => 'helpdeskrating'],
            ['pkey' => 'typology'],
            ['pkey' => 'mask'],
            ['pkey' => 'ocsinventoryng'],
            ['pkey' => 'surveyticket'],
            ['pkey' => 'utilitaires'],
            ['pkey' => 'Reforme'],
            ['pkey' => 'ticketcleaner'],
            ['pkey' => 'escalation'],
            ['pkey' => 'vip'],
            ['pkey' => 'dashboard'],
            ['pkey' => 'mantis'],
            ['pkey' => 'reservation'],
            ['pkey' => 'timezones'],
            ['pkey' => 'exemple'],
            ['pkey' => 'sccm'],
            ['pkey' => 'talk'],
            ['pkey' => 'tag'],
            ['pkey' => 'news'],
            ['pkey' => 'purgelogs'],
            ['pkey' => 'mreporting'],
            ['pkey' => 'custom'],
            ['pkey' => 'customfields'],
            ['pkey' => 'escalade'],
            ['pkey' => 'moreticket'],
            ['pkey' => 'itilcategorygroups'],
            ['pkey' => 'consumables'],
            ['pkey' => 'printercounters'],
            ['pkey' => 'field'],
            ['pkey' => 'fpsoftware'],
            ['pkey' => 'fptheme'],
            ['pkey' => 'fpsaml'],
            ['pkey' => 'fpconsumables'],
            ['pkey' => 'mhooks'],
            ['pkey' => 'lock'],
            ['pkey' => 'bootstraptheme'],
            ['pkey' => 'webnotifications'],
            ['pkey' => 'simcard'],
            ['pkey' => 'processmaker'],
            ['pkey' => 'seasonality'],
            ['pkey' => 'moreldap'],
            ['pkey' => 'tasklists'],
            ['pkey' => 'mailanalyzer'],
            ['pkey' => 'arsurveys'],
            ['pkey' => 'glpi_ansible'],
            ['pkey' => 'hidefields'],
            ['pkey' => 'formvalidation'],
            ['pkey' => 'mydashboard'],
            ['pkey' => 'IFRAME'],
            ['pkey' => 'timelineticket'],
            ['pkey' => 'airwatch'],
            ['pkey' => 'useditemsexport'],
            ['pkey' => 'loginbyemail'],
            ['pkey' => 'nebackup'],
            ['pkey' => 'physicalinv'],
            ['pkey' => 'openvas'],
            ['pkey' => 'autologin'],
            ['pkey' => 'father'],
            ['pkey' => 'browsernotification'],
            ['pkey' => 'armadito-glpi'],
            ['pkey' => 'showloading'],
            ['pkey' => 'service'],
            ['pkey' => 'modifications'],
            ['pkey' => 'credit'],
            ['pkey' => 'myassets'],
            ['pkey' => 'xivo']
        ];

        $plugins = $this->table('glpi_plugin');
        $plugins->insert($data)->save();
    }
}
