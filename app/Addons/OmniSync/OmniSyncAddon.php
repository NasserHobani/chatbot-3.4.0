<?php 

namespace App\Addons\OmniSync;

use App\AddonManager\Addon;

class OmniSyncAddon extends Addon
{
    public $name                 = 'OmniSync';

    public $description          = 'SaleBot OmniSync Chat Addon: Facebook, Instagram & Beyond';

    public $version              = '1.0.0';

    public $author               = 'SpaGreen Creative';

    public $author_url           = 'https://codecanyon.net/user/spagreen/portfolio';

    public $tag                  = 'Facebook messenger, Addon, messenger bot, Instagram';

    public $addon_identifier     = 'omnisync';

    public $required_cms_version = '3.3.0';

    public $required_app_version = '3.3.0';

    public $license              = 'General Public License';

    public $license_url          = 'https://mit-license.org/GPL';


    public function boot()
    {
        $this->enableViews();
        $this->enableRoutes();
    }

    public function addonActivated()
    {
        dd('I am activated');
    }

    public function addonDeactivated()
    {
        dd('I am deActivated');
    }
}

?>