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
                'class' => 'navbar-nav mr-auto mt-2 mt-lg-0',
            ),
        ));

        // Homepage
        $menu->addChild('', [
            'attributes' => ['class' => 'nav-item'],
            'route' => 'homepage'
        ])->setLinkAttribute('class', 'nav-link fa fa-home');

        // About Us
        $menu->addChild('aboutus', [
            'attributes' => ['class' => 'nav-item'],
            'route' => 'news_show',
            'routeParameters' => ['slug' => 'gioi-thieu']
        ])->setLinkAttribute('class', 'nav-link');

        // News
        $menu->addChild('services', [
            'attributes' => ['class' => 'nav-item'],
            'route' => 'news_category',
            'routeParameters' => ['level1' => 'dich-vu']
        ])->setLinkAttribute('class', 'nav-link');

        $menu->addChild('projects', [
            'attributes' => ['class' => 'nav-item'],
            'route' => 'news_category',
            'routeParameters' => ['level1' => 'du-an']
        ])->setLinkAttribute('class', 'nav-link');

        // Contact us
        $menu->addChild('contactus', [
            'attributes' => ['class' => 'nav-item'],
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