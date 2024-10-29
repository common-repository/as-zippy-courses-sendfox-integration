<?php

class ZippyCourses_Sendfox_EmailListIntegration extends Zippy_EmailListIntegration
{
    public $id = 'sendfox';
    public $service = 'sendfox';
    public $settings = array();
    
    public $api;

    private $path;

    public function __construct()
    {
        $this->path = plugin_dir_path(__FILE__);
        $this->url  = plugin_dir_url(__FILE__);

        $this->settings = (array) get_option('zippy_sendfox_email_integration_settings');
        $this->enabled  = isset($this->settings['enabled']) ? (bool) $this->settings['enabled'] : false;

        add_filter('zippy_classmap', array($this, 'map'));
        add_filter('zippy_fetch_meta_data', array($this, 'metaboxLists'), 10, 2);

        parent::__construct();
    }

    public function map($classes)
    {
        $classes['ZippyCourses_Sendfox_API'] = $this->path . 'lib/API.php';
        $classes['ZippyCourses_Sendfox_EmailList'] = $this->path . 'lib/EmailList.php';

        return $classes;
    }

    public function register()
    {
        $zippy = Zippy::instance();

        $repository = $zippy->make('email_list_integration_repository');
        $repository->add($this);

        parent::register();
    }

    public function ajax()
    {
        add_action('wp_ajax_get_sendfox_lists', array($this->api, 'getListsJSON'));
    }

    /**
     * Setup the integration with the appropriate settings and hooks,
     * such as landing page integration list and settings
     * @return void
     */
    public function setup()
    {
        $zippy = Zippy::instance();
        
        $this->api = new ZippyCourses_Sendfox_API;

        $this->ajax();

        $zippy->bind($this->api->getBinding('list'), 'ZippyCourses_Sendfox_EmailList');

        add_filter('zippy_metaboxes', array($this, 'metaboxes'));
        add_action('init', array($this, 'settings'));
    }

    /**
     * Set register and set up the settings for the email list
     * @return void
     */
    public function settings()
    {
        $zippy = Zippy::instance();

        $settings_pages = $zippy->make('settings_pages_repository');
        $settings_page = $settings_pages->fetch('zippy_settings_email_lists');

        $section = $settings_page->createSection($this->getSettingsName(), 'Sendfox');
            $section->createField(
                'enabled',
                __('Enabled?','as-sizc'),
                'select',
                array(
                    __('No','as-sizc'),
                    __('Yes','as-sizc')
                )
            );
            $section->createField('api_key', __('Access Token','as-sizc'));
    }

    /**
     * Integrate with the correct metaboxes
     * @return void
     */
    public function metaboxes($metaboxes)
    {
        if (!$this->enabled) {
            return $metaboxes;
        }

        $dir = $this->path . 'assets/views/metaboxes/';
        $files = array_diff(scandir($dir), array('..', '.'));

        foreach ($files as $key => &$file) {
            if (!is_dir($dir . $file)) {
                $file = $dir . $file;
            } else {
                unset($files[$key]);
            }
        }

        return array_merge($metaboxes, $files);
    }

    /**
     * Handle the landing page integration
     * @return void
     */
    public function landingPage()
    {
    }
    
    /**
     * Use API to add a user to the list
     * @param  Zippy_User      $student
     * @param  Zippy_EmailList $list
     * @return bool
     */
    public function subscribeStudent(Zippy_User $student, Zippy_EmailList $list)
    {
        $this->api->subscribe($student, $list);
    }

    /**
     * Use API to remove a user from a list
     * @param  Zippy_User      $student
     * @param  Zippy_EmailList $list
     * @return bool
     */
    public function unsubscribeStudent(Zippy_User $student, Zippy_EmailList $list)
    {
        $this->api->unsubscribe($student, $list);
    }

    /**
     * Make sure that tabs are set up where needed
     * @return void
     */
    protected function addTabs()
    {
        if (!$this->enabled) {
            return;
        }

        add_filter('zippy_enable_course_email_list_tab', '__return_true');
        add_filter('zippy_enable_product_email_list_tab', '__return_true');
    }
}
