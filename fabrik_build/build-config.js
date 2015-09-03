module.exports = {
    'pluginFolders': ['element', 'cron', 'form', 'list', 'validationrule', 'visualization'],
    'modules'      : [
        {
            'name'    : 'Fabrik List Module',
            'path'    : 'modules/mod_fabrik_list',
            'fileName': 'mod_fabrik_list_{version}.zip',
            'element' : 'mod_fabrik_list',
            'xmlFile' : 'mod_fabrik_list.xml'
        },
        {
            'name'    : 'Fabrik Form Module',
            'path'    : 'modules/mod_fabrik_form',
            'fileName': 'mod_fabrik_form_{version}.zip',
            'element' : 'mod_fabrik_form',
            'xmlFile' : 'mod_fabrik_form.xml'
        },
        {
            'name'    : 'Fabrik Admin Form Module',
            'path'    : 'administrator/modules/mod_fabrik_form',
            'fileName': 'mod_fabrik_admin_form_{version}.zip',
            'element' : 'mod_fabrik_form',
            'xmlFile' : 'mod_fabrik_admin_form.xml'
        },
        {
            'name'    : 'Fabrik Admin List Module',
            'path'    : 'administrator/modules/mod_fabrik_list',
            'fileName': 'mod_fabrik_admin_list_{version}.zip',
            'element' : 'mod_fabrik_list',
            'xmlFile' : 'mod_fabrik_admin_list.xml'
        },
        {
            'name'    : 'Fabrik Admin QuickIcon Module',
            'path'    : 'administrator/modules/mod_fabrik_quickicon',
            'fileName': 'mod_fabrik_admin_quickicon_{version}.zip',
            'element' : 'mod_fabrik_quickicon',
            'xmlFile' : 'mod_fabrik_admin_quickicon.xml'
        },
        {
            'name'    : 'Fabrik Admin Visualization Module',
            'path'    : 'administrator/modules/mod_fabrik_visualization',
            'fileName': 'mod_fabrik_admin_visualization_{version}.zip',
            'element' : 'mod_fabrik_visualization',
            'xmlFile' : 'mod_fabrik_admin_visualization.xml'
        }],
    'plugins'      : {
        'system'   : [{
            'name'    : 'Fabrik Content Plugin',
            'path'    : 'plugins/system/fabrik',
            'fileName': 'plg_fabrik_{version}.zip',
            'element' : 'fabrik',
            'xmlFile' : 'plg_system_fabrik.xml'
        },
            {
                'name'    : 'Fabrik Cron Plugin',
                'path'    : 'plugins/system/fabrikcron',
                'fileName': 'plg_fabrik_schedule_{version}.zip',
                'element' : 'fabrikcron',
                'xmlFile' : 'plg_system_fabrikcron.xml'
            }
        ],
        'community': [
            {
                'name'    : 'Community Builder: Fabrik User Plugin',
                'path'    : 'plugins/community/fabrik',
                'fileName': 'plg_community_builder_fabrik_user_{version}.zip',
                'element' : 'fabrik',
                'xmlFile' : 'plg_community_builder_fabrik_user.xml'
            }],
        'content'  : [
            {
                'name'    : 'Fabrik Content Plugin',
                'path'    : 'plugins/content/fabrik',
                'fileName': 'plg_fabrik_content_{version}.zip',
                'element' : 'fabrik',
                'xmlFile' : 'plg_fabrik_content.xml'
            }],
        'search'   : [{
            'name'    : 'Fabrik Search Plugin',
            'path'    : 'plugins/search/fabrik',
            'fileName': 'plg_fabrik_search_{version}.zip',
            'element' : 'fabrik',
            'xmlFile' : 'plg_fabrik_search.xml'
        }]
    }
}
