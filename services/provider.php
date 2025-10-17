<?php
/**
 * @package     WT SEO Meta templates
 * @subpackage  WT SEO Meta templates - Content
 * @version     2.0.2
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2022 Sergey Tolkachyov
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
 * @since       1.0.0
 */

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\Wt_seo_meta_templates_content\Extension\Wt_seo_meta_templates_content;

\defined('_JEXEC') || die;

return new class implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param Container $container The DI container.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $subject = $container->get(DispatcherInterface::class);
                $config = (array)PluginHelper::getPlugin('system', 'wt_seo_meta_templates_content');
                $plugin = new Wt_seo_meta_templates_content($subject, $config);
	            $plugin->setApplication(Factory::getApplication());
				return $plugin;
            }
        );
    }
};