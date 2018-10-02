<?php

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root', array(
            'childrenAttributes' => array (
                'class' => 'nav navbar-nav',
            ),
        ));

        // Homepage
        $menu->addChild('', [
            'route' => 'homepage'
        ])->setLinkAttribute('class', 'nav-link fa fa-home');

        // About Us
        $menu->addChild('aboutus', [
            'route' => 'news_show',
            'routeParameters' => ['slug' => 'gioi-thieu']
        ])->setLinkAttribute('class', 'nav-link');

        // News
        $menu->addChild('services', [
            'route' => 'news_category',
            'routeParameters' => ['level1' => 'dich-vu']
        ])->setLinkAttribute('class', 'nav-link');

        $menu->addChild('projects', [
            'route' => 'news_category',
            'routeParameters' => ['level1' => 'du-an']
        ])->setLinkAttribute('class', 'nav-link');

        $menu->addChild('advisory', [
            'route' => 'news_category',
            'routeParameters' => ['level1' => 'tu-van']
        ])->setLinkAttribute('class', 'nav-link');

        // Contact us
        $menu->addChild('contactus', [
            'route' => 'contact'
        ])->setLinkAttribute('class', 'nav-link');

        return $menu;
    }

    public function footerMenu(FactoryInterface $factory, array $options)
    {
        $footerMenu = $factory->createItem('root');

        return $footerMenu;
    }
}